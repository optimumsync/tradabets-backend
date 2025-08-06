<?php
namespace App\Http\Controllers;
use App\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use MongoDB\Driver\Session;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use App\Helpers\PaymentHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Redirect;
use Paystack;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Interswitch\Interswitch\Facades\Interswitch;
class PaymentController extends Controller
{
    //
    public function __construct()
    {
        /** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }
    public function payment(Request $request){
        $deposit_amt=$request->get('deposit_amount');
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $item_1 = new Item();
        $item_1->setName('Item 1') /** item name **/
        ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice( $deposit_amt); /** unit price **/
        $item_list = new ItemList();
        $item_list->setItems(array($item_1));
        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal( $deposit_amt);
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('deposit-request')) /** Specify return URL **/
        ->setCancelUrl(URL::route('deposit-request'));
        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
                $request->session()->put('error', 'Connection timeout');
                return Redirect::route('payment-request');
            } else {
                $request->session()->put('error', 'Some error occur, sorry for inconvenient');
                return Redirect::route('payment-request');
            }
        }
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        /** add payment ID to session **/
        $request->session()->put('amount',$deposit_amt);
        if (isset($redirect_url)) {
            /** redirect to paypal **/
            return redirect($redirect_url);
        }
        $request->session()->put('error', 'Unknown error occurred');
        return redirect('payment-request');
    }
    public function depositAmount(Request $request)
    {
        $payment_id = $_GET['paymentId'];
        $amount = $request->session()->get('amount');
        
        \Log::info('PayPal callback received', [
            'payment_id' => $payment_id,
            'amount' => $amount,
            'payer_id' => $_GET['PayerID'] ?? null,
            'token' => $_GET['token'] ?? null
        ]);
        
        if (empty($_GET['PayerID']) || empty($_GET['token'])) {
            \Log::warning('PayPal payment failed - missing PayerID or token');
            $request->session()->put('error', 'Payment Failed');
            return redirect('deposit-form');
        }
        
        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        $result = $payment->execute($execution, $this->_api_context);
        
        \Log::info('PayPal payment execution result', [
            'state' => $result->getState()
        ]);
        
        if ($result->getState() == 'approved') {
            $user = auth()->user();
            \Log::info('Processing PayPal payment', [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            
            PaymentHelper::create_transaction($amount, $user->id, 'deposit');
            $transaction_check = \App\Models\Transaction::where('user_id', $user->id)->get()->all();
            $transaction_count = count($transaction_check);
            
            if ($transaction_count == 1) {
                \Log::info('First time deposit - creating bonus transaction');
                PaymentHelper::create_transaction($amount, $user->id, 'bonus');
            }
            
            $request->session()->put('success', 'Payment success');
            return redirect('deposits');
        }
        
        \Log::warning('PayPal payment not approved', [
            'state' => $result->getState()
        ]);
        
        $request->session()->put('error', 'Payment Failed');
        return redirect('deposits');
    }
    /***************      PAYSTACK      **********************
     *
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
   public function redirectToGateway(Request $request)
    {
        $paystack = new Paystack();
        $user = auth()->user();
        if($user->email != null){
             $request->email = $user->email;
        }
        else{
             $request->email = ((string)$user->phone) .'@tradabets.com';
        }
        // $request->amount = $amount;
        $reference = 'TRX-' . uniqid() . '-' . time();
        $request->reference = PaymentHelper::initiate_transaction($user->id, 'request', $request->amount, null, $reference);
        $request->callback_url = route('paystack.callback'); 
        try {
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            return Redirect::back()->withMessage(['msg' => 'The paystack token has expired. Please refresh the page and try again.', 'type' => 'error']);
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->input('reference');
        if (!$reference) {
            \Log::error('No reference found in Paystack callback request.');
            return redirect('deposits')->with('error', 'Payment reference missing.');
        }
        $paymentDetails = Paystack::getPaymentData($reference);
        \Log::info('Paystack callback received', [
            'payment_details' => $paymentDetails
        ]);
        
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
        if (is_array($paymentDetails) && isset($paymentDetails['data'])) {
            $data = $paymentDetails['data'];
        } elseif (is_object($paymentDetails) && isset($paymentDetails->data)) {
            $data = $paymentDetails->data;
        } else {
            \Log::error('Invalid Paystack payment details', ['paymentDetails' => $paymentDetails]);
            return redirect('deposits')->with('error', 'Payment verification failed.');
        }
        \Log::info('Raw Paystack payment details', ['paymentDetails' => $paymentDetails]);
        $amount = ($data['amount'] ?? $data->amount) / 100;
        $status = $data['status'] ?? $data->status;
        $reference = $data['reference'] ?? $data->reference;
        
        \Log::info('Processing Paystack payment', [
            'amount' => $amount,
            'status' => $status,
            'reference' => $reference
        ]);
        
        if ($status == 'success') {
            $user_id = PaymentHelper::get_user_id_by_reference($reference);
            \Log::info('Found user for Paystack payment', [
                'user_id' => $user_id,
                'reference' => $reference
            ]);
            
            PaymentHelper::update_transaction($amount, $user_id, $reference, 'deposit','Paystack Payment');
            
            //###########################################################
            //Check first time deposit then amount will be double as bonus
            $firstDeposit  = PaymentHelper::checkUserFirstDeposit($user_id);
            if($firstDeposit == 1 && $amount >= 500) {
                PaymentHelper::createDepositBonus($amount, $user_id, 'bonus');
            }
            //############################################################
            return redirect('deposits');
        }
        
        \Log::warning('Paystack payment failed', [
            'status' => $status,
            'reference' => $reference
        ]);
        
        $status->session()->put('error', 'Payment Failed');
        return redirect('deposits');
    }


    /********************      FLUTTERWAVE     *********************
    /**
     * Initialize Rave payment process
     * @return void
     */
    public function initialize(Request $request)
    {
        //This generates a payment reference
        // $reference = Flutterwave::generateReference();
        $reference = 'FLW-' . uniqid();
        $user = auth()->user();
        if($user->email != null){
             $request->email = $user->email;
        }
        else{
             $request->email = ((string)$user->phone) .'@tradabets.com';
        }
         PaymentHelper::initiate_transaction($user->id, 'request', $request->amount, 'Flutterwave Payment Gateway',$reference);
        // Enter the details of the payment
        $data = [
            'amount' => $request->amount,
            'email' => $request->email,
            'tx_ref' => $reference,
            'currency' => "NGN",
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => $request->email,
                "phone_number" => $user->phone,
                "name" => $user->first_name.' '.$user->last_name
            ],
        ];
        $payment = Flutterwave::initializePayment($data);
        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return Redirect::back()->withMessage(['msg' => 'Something went wrong. Please refresh the page and try again.', 'type' => 'error']);
        }
        return redirect($payment['data']['link']);
    }
    /**
     * Obtain Rave callback information
     * @return void
     */
    public function flutterwaveCallback(Request $request)
    {
        $status = request()->status;
        \Log::info('Flutterwave callback received', [
            'status' => $status,
            'request_data' => $request->all()
        ]);
        if ($status == 'successful' || $status == 'completed') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);
            
            \Log::info('Flutterwave transaction verified', [
                'transactionID' => $transactionID,
                'verification_data' => $data
            ]);
            $ref_id = explode('-', $data['data']['tx_ref']);
            $tx_ref = $data['data']['tx_ref'];
            $amount = $data['data']['amount'];

            $transaction = \App\Models\Transaction::where('transaction_id', $tx_ref)->first();
            if (!$transaction) {
                \Log::error('Transaction not found for tx_ref', ['tx_ref' => $tx_ref]);
                return redirect('deposits')->withErrors(['error' => 'Transaction not found for this reference.']);
            }
            $user_id = $transaction->user_id;

            $transaction_id = Flutterwave::getTransactionIDFromCallback();
            
            \Log::info('Processing Flutterwave payment', [
                'tx_ref' => $tx_ref,
                'amount' => $amount,
                'user_id' => $user_id
            ]);
            
            PaymentHelper::update_transaction($amount, $user_id, $transaction_id, 'deposit', 'Flutterwave Payment Gateway');
            
            //###########################################################
            //Check first time deposit then amount will be double as bonus
            $firstDeposit  = PaymentHelper::checkUserFirstDeposit($user_id);
            if($firstDeposit == 1 && $amount >= 500) {
                PaymentHelper::createDepositBonus($amount, $user_id, 'bonus');
            }
            //############################################################
            session()->flash('message-success', 'Deposit successful!');
            return redirect('deposits');
        }
        elseif ($status ==  'cancelled'){
            \Log::info('Flutterwave payment cancelled');
            return redirect('deposits')->withErrors(['error' => 'Payment cancelled']);
        }
        else{
            \Log::warning('Flutterwave payment failed', [
                'status' => $status
            ]);
            return redirect('deposits')->withErrors(['error' => 'Payment Failed']);
        }
    }
    public function pay(Request $request)
    {
        $rebuiltResponse = [
            'transactionReference' => $_POST['txnref'],
            'responseCode' => $_POST['resp'],
            'responseDescription' => $_POST['desc'],
            'paymentReference' => $_POST['payRef'],
            'returnedReference' => $_POST['retRef'],
            'cardNumber' => $_POST['cardNum'],
            'approvedAmount' => $_POST['apprAmt'],
            'amount' => $_POST['amount'],
            'mac' => $_POST['mac']
        ];
        \Log::info('Payment callback received', [
            'response' => $rebuiltResponse
        ]);
        if (in_array($rebuiltResponse['responseCode'], ['00','10','11'])) {
            $ref_id = explode('-', $rebuiltResponse['transactionReference']);
            $amount = ($rebuiltResponse['amount']) / 100;
            $user_id = PaymentHelper::get_user_id_by_reference($ref_id[0]);
            
            \Log::info('Processing payment', [
                'ref_id' => $ref_id[0],
                'amount' => $amount,
                'user_id' => $user_id
            ]);
            
            PaymentHelper::update_transaction($amount, $user_id, $ref_id[0], 'deposit', json_encode($rebuiltResponse));
            
            //###########################################################
            //Check first time deposit then amount will be double as bonus
            $firstDeposit  = PaymentHelper::checkUserFirstDeposit($user_id);
            if($firstDeposit == 1 && $amount >= 500) {
                PaymentHelper::createDepositBonus($amount, $user_id, 'bonus');
            }
            //############################################################
            session()->flash('message-success', 'Deposit successful!');
            return redirect('deposits');
        }
        else{
            \Log::warning('Payment failed', [
                'response_code' => $rebuiltResponse['responseCode']
            ]);
            return redirect('deposits')->withErrors(['error' => 'Payment Failed']);
        }
    }
    public function interswitch_callback()
    {
        $rebuiltResponse = [
            'transactionReference' => $_POST['txnref'],
            'responseCode' => $_POST['resp'],
            'responseDescription' => $_POST['desc'],
            'paymentReference' => $_POST['payRef'],
            'returnedReference' => $_POST['retRef'],
            'cardNumber' => $_POST['cardNum'],
            'approvedAmount' => $_POST['apprAmt'],
            'amount' => $_POST['amount'],
            'mac' => $_POST['mac']
        ];
        if (in_array($rebuiltResponse['responseCode'], ['00','10','11'])) {
            $ref_id = explode('-', $rebuiltResponse['transactionReference']);
            $amount = ($rebuiltResponse['amount']) / 100 ;
            $user_id = PaymentHelper::get_user_id_by_reference($ref_id[0]);
            PaymentHelper::update_transaction($amount, $user_id, $ref_id[0], 'deposit', json_encode($rebuiltResponse));
            //###########################################################
            //Check first time deposit then amount will be double as bonus
            $firstDeposit  = PaymentHelper::checkUserFirstDeposit($user_id);
            if($firstDeposit == 1 && $amount >= 500) {
                PaymentHelper::createDepositBonus($amount, $user_id, 'bonus');
            }
            //############################################################
            // $transaction_check = \App\Models\Transaction::where('user_id', $user_id)->get()->all();
            // $transaction_count = count($transaction_check);
            // if ($transaction_count == 1) {
            //     PaymentHelper::create_transaction($amount, $user_id, 'bonus');
            // }
            session()->flash('message-success', 'Deposit successful!');
            return redirect('deposits');
        }
        else{
            //Put desired action/code after transaction has failed here
            return redirect('deposits')->withErrors(['error' => 'Payment Failed']);;
        }
    }
    
    public function opayPay(Request $request)
    {
        $user = auth()->user();
        if($user->email != null){
            $request->email = $user->email;
        }
        else{
            $request->email = ((string)$user->phone) .'@tradabets.com';
        }
        $request->reference = PaymentHelper::initiate_transaction($user->id, 'request', ($request->amount * 100), 'Opay Pyament Gateway');
        // Enter the details of the payment
//        $data = [
//            'amount' => $request->amount,
//            'customerEmail' => $request->email,
//            'transactionReference' => $request->reference.'-'.$request->transactionReference,
//            'customerName' => $user->first_name.' '.$user->last_name,
//            'customerID' => $user->id
//        ];
        $data = [
            'country' => 'NG',
            'reference' => $request->reference,
            'amount' => [
                "total"=> $request->amount,
                "currency"=> 'NGN',
            ],
            'returnUrl' => config('app.url') . '/opay-return/'.base64_encode($request->reference),
            'callbackUrl'=> config('app.url') . '/opay-return',
            'cancelUrl' => 'https://your-cacel-url',
            'expireAt' => 30,
            'sn' => 'PE462xxxxxxxx',
            'userInfo' => [
                "userEmail"=> $request->email,
                "userId"=> $user->id,
                "userMobile"=> $user->phone,
                "userName"=> $user->first_name.' '.$user->last_name
            ],
            'product' => [
                "name"=> 'name',
                "description"=> 'description'
            ],
            'payMethod'=>'',
        ];
        //$data2 = (string) json_encode($data,JSON_UNESCAPED_SLASHES);
        $header = ['Content-Type:application/json', 'Authorization:Bearer '. config('opay.publicKey'), 'MerchantId:'.config('opay.merchantId')];
        $response = $this->http_post(config('opay.opayPaymentUrl'), $header, json_encode($data));
        $result = $response?$response:null;
        $result = json_decode($result);
        if ($result->message !== 'SUCCESSFUL') {
            // notify something went wrong
            return Redirect::back()->withMessage(['msg' => 'Something went wrong. Please refresh the page and try again.', 'type' => 'error']);
        }
        return redirect($result->data->cashierUrl);
    }
    private function http_post ($url, $header, $data) {
        if (!function_exists('curl_init')) {
            throw new Exception('php not found curl', 500);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error=curl_error($ch);
        curl_close($ch);
        if (200 != $httpStatusCode) {
            print_r("invalid httpstatus:{$httpStatusCode} ,response:$response,detail_error:" . $error, $httpStatusCode);
        }
        return $response;
    }
    public function opayReturn($reference)
    {
        $reference = base64_decode($reference);
        $data = [
            'country' => 'NG',
            'reference' => $reference
        ]
        ;
        $data2 = (string) json_encode($data,JSON_UNESCAPED_SLASHES);
        $auth = $this->auth($data2);
        $header = ['Content-Type:application/json', 'Authorization:Bearer '. $auth, 'MerchantId:'.config('opay.merchantId')];
        $response = $this->http_post(config('opay.opayPaymentStatusUrl'), $header, json_encode($data));
        $result = $response?$response:null;
        $result = json_decode($result);
        if($result->message == 'SUCCESSFUL') {
            $amount = ($result->data->amount->total) / 100 ;
            $user_id = PaymentHelper::get_user_id_by_reference($reference);
            PaymentHelper::update_transaction($amount, $user_id, $reference, 'deposit', json_encode($result));
            //###########################################################
            //Check first time deposit then amount will be double as bonus
            $firstDeposit  = PaymentHelper::checkUserFirstDeposit($user_id);
            if($firstDeposit == 1 && $amount >= 500) {
                PaymentHelper::createDepositBonus($amount, $user_id, 'bonus');
            }
            //############################################################
            // $transaction_check = \App\Models\Transaction::where('user_id', $user_id)->get()->all();
            // $transaction_count = count($transaction_check);
            // if ($transaction_count == 1) {
            //     PaymentHelper::create_transaction($amount, $user_id, 'bonus');
            // }
            session()->flash('message-success', 'Deposit successful!');
            return redirect('deposits');
        }
        else{
            return redirect('deposits')->withErrors(['error' => 'Payment Failed']);
        }
    }
    public function auth ($data) {
        $secretKey = config('opay.secretKey');
        $auth = hash_hmac('sha512', $data, $secretKey);
        return $auth;
    }
}

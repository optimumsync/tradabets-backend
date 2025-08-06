<?php
namespace App\Http\Controllers\API\V1;
use Unicodeveloper\Paystack\Test\HelpersTest;
use Unicodeveloper\Paystack\Exceptions\IsNullException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\RedirectUrls;
use App\Models\Transaction;
use PayPal\Rest\ApiContext;
use App\Helpers\PaymentHelper;
use Paystack;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Interswitch\Interswitch\Facades\Interswitch;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use Unicodeveloper\Paystack\Facades\Paystack as PaystackFacade;
use App\Balance;
use App\User;

class PaymentControllerApi extends Controller
{
    protected $user;
    protected $_api_context;
    public function __construct()
    {
        $this->middleware('jwt.auth')->except(['paystackCallback', 'flutterwaveCallback', 'paypalExecutePayment']);
        $this->user = auth()->user();
        // PayPal API Context
        $paypal_conf = config('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
            $paypal_conf['client_id'],
            $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);
    }
    /**
     * Initiate PayPal Payment
     */
    public function paypalPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:USD,EUR,GBP'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $item = new Item();
            $item->setName('Account Deposit')
                ->setCurrency($request->currency)
                ->setQuantity(1)
                ->setPrice($request->amount);
            $itemList = new ItemList();
            $itemList->setItems([$item]);
            $amount = new Amount();
            $amount->setCurrency($request->currency)
                ->setTotal($request->amount);
            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription('Account funding');
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(route('api.payment.paypal.callback'))
                ->setCancelUrl(route('api.payment.paypal.callback'));
            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);
            $payment->create($this->_api_context);
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() === 'approval_url') {
                    return $this->successResponse([
                        'payment_id' => $payment->getId(),
                        'approval_url' => $link->getHref(),
                        'execute_url' => route('api.payment.paypal.execute')
                    ], 'Payment initiated');
                }
            }
            return $this->errorResponse('Unable to create payment', 500);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    /**
     * Execute PayPal Payment
     */
    public function paypalExecutePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required',
            'payer_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $payment = Payment::get($request->payment_id, $this->_api_context);
            $execution = new PaymentExecution();
            $execution->setPayerId($request->payer_id);
            $result = $payment->execute($execution, $this->_api_context);
            if ($result->getState() === 'approved') {
                $amount = $payment->getTransactions()[0]->getAmount()->getTotal();
                
                // Process payment
                $this->processPayment($amount, 'paypal', $payment->getId());
                return $this->successResponse([
                    'amount' => $amount,
                    'currency' => $payment->getTransactions()[0]->getAmount()->getCurrency(),
                    'transaction_id' => $payment->getId()
                ], 'Payment successful');
            }
            return $this->errorResponse('Payment not approved', 400);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    /**
     * Initiate Paystack Payment
     */
    public function paystackPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100' // Minimum ₦100
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $user = auth()->user();
            
            // 1. Get or Generate Valid Email
            $email = $this->getUserEmail($user);
            
            // 2. Generate Transaction Reference
            $reference = time() . $user->id; // Example: TRA1634567890123
            
            // 3. Prepare Payment Data
            $paymentData = [
                'amount' => $request->amount * 100, // Convert to kobo
                'email' => $email,
                'reference' => $reference,
                'callback_url' => route('api.payment.paystack.callback'),
                'metadata' => [
                    'user_id' => $user->id,
                    'custom_fields' => [
                        [
                            'display_name' => 'User ID',
                            'variable_name' => 'user_id',
                            'value' => $user->id
                        ]
                    ]
                ]
            ];
            // 4. Initialize Payment
            $authorizationUrl = Paystack::getAuthorizationUrl($paymentData)->url;
            return $this->successResponse([
                'authorization_url' => $authorizationUrl,
                'reference' => $reference
            ], 'Payment initiated');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    /**
     * Get or Generate Valid Email for User
     */
    protected function getUserEmail($user)
    {
        // Case 1: User has valid email
        if (!empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return $user->email;
        }
        
        // Case 2: Generate from phone number
        if (!empty($user->phone)) {
            return preg_replace('/[^0-9]/', '', $user->phone) . '@tradabets.com';
        }
        
        // Case 3: Fallback to system-generated email
        return 'user' . $user->id . '@tradabets.com';
    }
    /**
     * Handle Paystack Callback
     */
   public function paystackCallback(Request $request)
    {
        try {
            $reference = $request->query('reference');
            \Log::info('Paystack callback reference:', ['reference' => $reference]);
            if (empty($reference)) {
                \Log::error('Invalid Paystack reference received', ['reference' => $reference]);
                return $this->errorResponse('Reference is missing from callback.', 400);
            }
            $paymentDetails = Paystack::getPaymentData($reference);
            $amount = $paymentDetails['data']['amount'] / 100;
            $status = $paymentDetails['status'];
            $user_id = $paymentDetails['data']['metadata']['user_id'] ?? null;
            $paystackPaymentStatus = $paymentDetails['data']['status'] ?? null; // 'success', 'failed', etc.
            // Always update the transaction status
            if ($paystackPaymentStatus !== 'success') {
                // Store failed status in DB
                PaymentHelper::update_transaction($amount, $user_id, $reference, 'failed', 'Paystack Payment Failed', 'deposit');
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment failed',
                        'data' => [
                            'transaction_id' => $reference,
                            'status' => 'failed'
                        ]
                    ], 400);
                }
                return redirect('deposits')->with('error', 'Payment failed');
            }
            if ($status === true && $paystackPaymentStatus === 'success') {
                PaymentHelper::update_transaction($amount, $user_id, $reference, 'deposit', 'Paystack Payment', 'deposit');
                $transaction = Transaction::where('transaction_id', $reference)->first();
                if ($request->expectsJson()) {
                    return $this->successResponse($transaction, 'Payment successful');
                }
                return $this->successResponse([
                    'redirect_url' => url('api/transactions')
                ], 'Payment successful');
            }
            // Fallback for any other case
            PaymentHelper::update_transaction($amount, $user_id, $reference, 'failed', 'Paystack Payment Failed', 'deposit');
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment failed',
                    'data' => [
                        'transaction_id' => $reference,
                        'status' => 'failed'
                    ]
                ], 400);
            }
            return redirect('deposits')->with('error', 'Payment failed');
        } catch (\Exception $e) {
            // Store failed status in DB if possible
            if (!empty($reference) && !empty($user_id)) {
                PaymentHelper::update_transaction($amount ?? 0, $user_id, $reference, 'failed', 'Paystack Payment Exception', 'deposit');
            }
            if ($request->expectsJson()) {
                return $this->errorResponse('Payment error: ' . $e->getMessage(), 500);
            }
            return redirect('deposits')->with('error', 'Payment error: ' . $e->getMessage());
        }
        \Log::info('Full Paystack callback query params:', $request->query());
    }
    /**
     * Initiate Flutterwave Payment
     */
    public function flutterwavePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'   => 'required|numeric|min:100',
            'currency' => 'required|in:NGN',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        try {
            $user = auth()->user();
            $email = $user->email ?: $user->phone . '@yourdomain.com';
            $fullName = $user->full_name ?? $user->name ?? 'User '.$user->id;
            $txRef = Flutterwave::generateReference();
            $data = [
                'payment_options' => 'card,banktransfer',
                'amount'          => $request->amount,
                'currency'        => $request->currency,
                'email'           => $email,
                'tx_ref'          => $txRef,
                'redirect_url'    => route('api.payment.flutterwave.callback'),
                'customer'        => [
                    'email'        => $email,
                    'phone_number' => $user->phone,
                    'name'         => $fullName,
                ],
            ];
            $payment = Flutterwave::initializePayment($data);
            \Log::info('Flutterwave init response', $payment);
            if (($payment['status'] ?? '') !== 'success' || empty($payment['data'])) {
                return $this->errorResponse('Payment initialization failed', 500);
            }
            // Extract true transaction ID:
            $transactionId = $payment['data']['id'] ?? $payment['data']['flw_ref'] ?? null;
            return $this->successResponse([
                'payment_url'     => $payment['data']['link'],
                'tx_ref'          => $txRef,
                'transaction_id'  => $transactionId,
            ], 'Payment initiated');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function flutterwaveCallback(Request $request)
    {
        try {
            $status = $request->query('status') ?? $request->input('status');
            $txRef = $request->query('tx_ref') ?? $request->input('tx_ref');
            $transactionID = $request->query('transaction_id') ?? $request->input('transaction_id');
    
            // Get user from middleware/auth
            $user = auth()->user() ?? $this->user;
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            $user_id = $user->id;
    
            // Check by actual transaction ID now (was txRef)
            $transaction = \App\Models\Transaction::where('transaction_id', $transactionID)->first();
    
            if (empty($status) || $status === 'cancelled') {
                $balance = \App\Balance::where('user_id', $user_id)->first();
                $current_balance = $balance ? $balance->balance : 0;
    
                if ($transaction) {
                    $transaction->status = 'cancelled';
                    $transaction->remarks = 'Flutterwave Payment Cancelled by User';
                    $transaction->save();
                } else {
                    \App\Models\Transaction::create([
                        'user_id'         => $user_id,
                        'opening_balance' => $current_balance,
                        'closing_balance' => $current_balance,
                        'amount'          => 0,
                        'status'          => 'failed',
                        'remarks'         => 'Flutterwave Payment failed by User',
                        'transaction_id'  => $transactionID,
                        'request'         => null,
                        'transaction_type' => 'deposit'
                    ]);
                }
    
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment failed by user.'
                ], 200);
            }
    
            // Verify from Flutterwave
            $response = null;
            if ($transactionID) {
                $response = \KingFlamez\Rave\Facades\Rave::verifyTransaction($transactionID);
            }
    
            if (
                !$response ||
                !isset($response['status']) || $response['status'] !== 'success' ||
                !isset($response['data']) ||
                !isset($response['data']['amount']) ||
                !isset($response['data']['tx_ref']) ||
                (isset($response['data']['status']) && $response['data']['status'] !== 'successful')
            ) {
                $balance = \App\Balance::where('user_id', $user_id)->first();
                $current_balance = $balance ? $balance->balance : 0;
    
                if ($transaction) {
                    $transaction->status = 'failed';
                    $transaction->remarks = 'Flutterwave Payment Failed';
                    $transaction->save();
                } else {
                    \App\Models\Transaction::create([
                        'user_id'         => $user_id,
                        'opening_balance' => $current_balance,
                        'closing_balance' => $current_balance,
                        'amount'          => 0,
                        'status'          => 'failed',
                        'remarks'         => 'Flutterwave Payment Failed',
                        'transaction_id'  => $transactionID,
                        'request'         => json_encode($response),
                        'transaction_type' => 'deposit'
                    ]);
                }
    
                return response()->json(['error' => 'Payment failed or invalid transaction response','transaction_type' => 'deposit'], 400);
            }
    
            // Payment successful
            $data = $response['data'];
            $amount = $data['amount'];
    
            if ($transaction) {
                $transaction->status = 'deposit';
                $transaction->remarks = 'Flutterwave Payment Successful';
                $transaction->amount = $amount;
                $transaction->save();
            } else {
                $balance = \App\Balance::where('user_id', $user_id)->first();
                $current_balance = $balance ? $balance->balance : 0;
                $new_balance = $current_balance + $amount;
    
                if ($balance) {
                    $balance->balance = $new_balance;
                    $balance->save();
                }
    
                \App\Models\Transaction::create([
                    'user_id'         => $user_id,
                    'opening_balance' => $current_balance,
                    'closing_balance' => $new_balance,
                    'amount'          => $amount,
                    'status'          => 'deposit',
                    'remarks'         => 'Flutterwave Payment Successful',
                    'transaction_id'  => $transactionID,
                    'request'         => json_encode($response),
                    'transaction_type' => 'deposit'
                    
                ]);
            }
    
            return response()->json([
                'message' => 'Payment verified and processed successfully.',
                'transaction_type' => 'deposit',
                'amount' => $amount,
                'reference' => $txRef,
                'flutterwave_id' => $transactionID,
                'gateway_response' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Callback processing failed','transaction_type' => 'deposit', 'details' => $e->getMessage()], 500);
        }
    }
    //git pushed 2
    private function saveTransaction($user, $amount, $gateway, $reference)
    {
        // Fetch user's current balance record
        $balanceRecord = Balance::where('user_id', $user->id)->first();
        // If no balance record exists, optionally create one with 0 balance
        if (!$balanceRecord) {
            $balanceRecord = Balance::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);
        }
        $openingBalance = $balanceRecord->balance;
        $closingBalance = $openingBalance + $amount;
        // Update balance table
        $balanceRecord->balance = $closingBalance;
        $balanceRecord->save();
        // Save transaction
        \App\Models\Transaction::create([
            'user_id'         => $user->id,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'amount'          => $amount,
            'status'          => 'deposit',
            'remarks'         => ucfirst($gateway) . ' Deposit',
            'transaction_id'  => $reference,
            'request'         => null, // Optional: store request data
            'transaction_type' => 'deposit'
        ]);
    }
    /**
     * Handle Flutterwave Callback
     */
    // public function flutterwaveCallback(Request $request)
    // {
    //     try {
    //         $transactionID = Flutterwave::getTransactionIDFromCallback();
    //         $data = Flutterwave::verifyTransaction($transactionID);
    //         if (
    //             $data['status'] !== 'success' ||
    //             !isset($data['data']) ||
    //             !isset($data['data']['amount']) ||
    //             !isset($data['data']['tx_ref'])
    //         ) {
    //             return $this->errorResponse('Payment verification failed or invalid response', 400);
    //         }
    //         $amount = $data['data']['amount'];
    //         $reference = $data['data']['tx_ref']; // Use the full tx_ref
    //         // Store transaction
    //         $this->processPayment($amount, 'flutterwave', $reference);
    //         return $this->successResponse([
    //             'gateway_response' => $data,
    //             'amount' => $amount,
    //             'reference' => $reference
    //         ], 'Payment successful');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse($e->getMessage(), 500);
    //     }
    // }
    /**
     * Process payment and handle bonus
     */
    protected function processPayment($amount, $gateway, $reference, $user_id = null)
    {
        $uid = $user_id ?? ($this->user ? $this->user->id : null);
        if (!$uid) {
            // handle error
            return;
        }
        // Only process as deposit if payment is successful
        // You may want to check the payment status from your transaction table
        $transaction = \App\Models\Transaction::where('transaction_id', $reference)->first();
        if ($transaction && $transaction->status !== 'deposit') {
            // Do not process bonus or credit for failed transactions
            return;
        }
        PaymentHelper::update_transaction($amount, $uid, $reference, 'deposit', json_encode([
            'gateway' => $gateway,
            'amount' => $amount
        ]));
        // Check for first deposit bonus
        $firstDeposit = PaymentHelper::checkUserFirstDeposit($uid);
        if ($firstDeposit == 1 && $amount >= 500) {
            PaymentHelper::createDepositBonus($amount, $uid, 'bonus');
        }
    }
    /**
     * Standard success response
     */
    protected function successResponse($data = [], $message = 'Success')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], 200);
    }
    /**
     * Standard error response
     */
    protected function errorResponse($error, $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => is_array($error) ? 'Validation failed' : $error,
            'errors' => is_array($error) ? $error : null
        ], $statusCode);
    }
    public function flutterwaveStatusByTxRef(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tx_ref' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }
        $tx_ref = $request->tx_ref;
        $secretKey = env('FLW_SECRET_KEY');
        $url = "https://api.flutterwave.com/v3/transactions?tx_ref={$tx_ref}";
        try {
            $response = Http::withToken($secretKey)->get($url);
            $data = $response->json();
            if (isset($data['data'][0])) {
                $transaction = $data['data'][0];
                return $this->successResponse([
                    'status' => $transaction['status'],
                    'amount' => $transaction['amount'],
                    'currency' => $transaction['currency'],
                    'tx_ref' => $transaction['tx_ref'],
                    'id' => $transaction['id']
                ], 'Transaction status fetched');
            } else {
                return $this->errorResponse('Transaction not found for this tx_ref.', 404);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching transaction: ' . $e->getMessage(), 500);
        }
    }
}

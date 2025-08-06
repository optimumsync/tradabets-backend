<?php
namespace Unicodeveloper\Paystack;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\UserBankDetails;
use App\PaymentReport;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class PaystackController extends Controller
{
     /**
     * Issue Secret Key from your Paystack Dashboard
     * @var string
     */
    protected $secretKey;

    /**
     * Paystack API base Url
     * @var string
     */
    protected $baseUrl;

    /**
     * Authorization Url - Paystack payment page
     * @var string
     */
    protected $authBearer;
    //
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);

        $this->setKey();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }

    /**
     * Get Base Url from Paystack config file
     */
    public function setBaseUrl()
    {
        $this->baseUrl = Config::get('paystack.paymentUrl');
    }

    /**
     * Get secret key from Paystack config file
     */
    public function setKey()
    {
        $this->secretKey = Config::get('paystack.secretKey');
    }

    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        $this->authBearer = array(
              "Authorization: Bearer " . $this->secretKey,
              "Cache-Control: no-cache",
              );
    }

    public function initiate(Request $request, $id)
    {
        $user = DB::table('withdraw_requests')
            ->select('user_id','amount')
            ->where('id', $id)
            ->first();
        $user_data = DB::table('user')
            ->select('first_name','last_name','email','phone')
            ->where('id', $user->user_id)
            ->first();
        
        if ( $user_data->email != null && $user_data->phone != null) {
            $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->email .")" ."(" .$user_data->phone .")" ;
        }
        else if ($user_data->email != null) {
            $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->email .")";
        }
        else if ($user_data->phone != null) {
            $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->phone .")";
        }
        else {
            $username = $user_data->first_name ." " .$user_data->last_name;
        }

        $recipient_code = DB::table('user_bank_accounts')
            ->where('user_id', $user->user_id)
            ->where('Active_status',"Active")
            ->value('recipient_code');

        $amount = round($user->amount * 100);

        $reason = "Withdrawal transfers";

              $url = $this->baseUrl . "/transfer";
              $fields = [
                'source' => "balance",
                'amount' => $amount,
                'recipient' => $recipient_code,
                'reason' => $reason
              ];
              // return $recipient_code;
              $fields_string = http_build_query($fields);

              //open connection
              $ch = curl_init();
              
              //set the url, number of POST vars, POST data
              curl_setopt($ch,CURLOPT_URL, $url);
              curl_setopt($ch,CURLOPT_POST, true);
              curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
              curl_setopt($ch, CURLOPT_HTTPHEADER, $this->authBearer);
              
              //So that curl_exec returns the contents of the cURL; rather than echoing it
              curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
              
              //execute post
              $result = curl_exec($ch);
              // echo $recipient_code;

              $initiate = json_decode($result);
              // echo $result;
               $status = $initiate->status;
               $message = $initiate->message;
               // $transfer_status1 = $initiate->data->status;

              if ($status == true) {

                  $message = $initiate->data->status;
                  $reference = $initiate->data->reference;
                  $amount = ($initiate->data->amount / 100) ;
                    // $request->has('spid_id') ? $request->input('spid_id') : NULL,
                  $reason = $initiate->data->reason;
                  $transfer_code = $initiate->data->transfer_code;
                  $createdAt = $initiate->data->createdAt;
                  $transaction_status = $initiate->data->status;


                    $query = PaymentReport::create(['transaction_reference'=>$reference,
                            'amount'=> $amount,
                            'status'=> $transaction_status,
                            'transaction_code'=> $transfer_code,
                            'payment_at'=> $createdAt,
                            'user_id'=>$user->user_id,
                            'recipient_code'=>$recipient_code,
                            'username'=> $username,
                            'user_email'=> $user_data->email,
                            'user_phone'=> $user_data->phone,

                        ]);

                    $update = DB::table('withdraw_requests')
                        ->where('id', $id)
                        ->update(['status' => "approved"]);

                    if (!$query) {
                        return redirect('/withdraw-requests')->with('error1', 'Transfer initiated details could not be stored in the database');
                    }
                    else {
                        return redirect('/withdraw-requests')->with('transfer-success', 'Transfer Success!');

                    }
              }

             else {
                if ($message == "otp") {
                    if (isset($transfer_code) && isset($recipient_code)) {
                        session()->put('transfer_code', $transfer_code);
                        session()->put('recipient_code', $recipient_code);
                    } else {
                        return redirect('/withdraw-requests')->with('error', 'Transfer code or recipient code is missing.');
                    }
             }
        }
    }

    public function bulkTransfer(Request $request)
    {

        $selected_requests = request('data');

        $collection = new Collection();

        foreach($selected_requests as $item) {
            $individualItem = DB::table('withdraw_requests')
                                ->select('amount','recipient_code')
                                ->where('id', $item)
                                ->first();

                            $collection->push((object)[
                                'amount' => $individualItem->amount,
                                'reason' => "Transfer for Withdrawal request",
                                'recipient' => $individualItem->recipient_code
                            ]);
        }

        $fields = [
            "currency" => "NGN",
            "source" => "balance",
            "transfers" => $collection
        ];

        $newFields = (json_encode($fields));

        // $fields_string = http_build_query($newFields);

  $url = "https://api.paystack.co/transfer/bulk";

  // $fields_string = http_build_query($fields);
  //open connection
  $ch = curl_init();
  
  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, true);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $newFields);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer " . $this->secretKey,
    // "Cache-Control: no-cache",
    "Content-Type: application/json"
  ));
  
  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
  
  //execute post
  $result = curl_exec($ch);

        // $curl = curl_init();
          
        // curl_setopt_array($curl, array(
        //   CURLOPT_URL => 'https://api.paystack.co//transfer/bulk/',
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_ENCODING => '',
        //   // CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 0,
        //   CURLOPT_FOLLOWLOCATION => true,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => 'POST',
        //   CURLOPT_POSTFIELDS => $newFields,
        //   CURLOPT_HTTPHEADER => array(
        //     'Authorization: Bearer ' . $this->secretKey,
        //     'Content-Type: application/json'
        //   ),
        // ));

        // $result = curl_exec($curl);

          $finalize = json_decode($result);
          $status = $finalize->status;
          $request_array = $finalize->data;
// echo $result;
          if ($status) {

            foreach ($request_array as $elemt) {

                $indl_recipient = $elemt->recipient;
                $indl_amount = $elemt->amount;
                $indl_transfer_code = $elemt->transfer_code;
                $indl_status = $elemt->status;
                $indl_payment_at = now();

                $user = DB::table('withdraw_requests')
                    ->select('user_id')
                    ->where('recipient_code', $indl_recipient)
                    ->first();

                $user_data = DB::table('user')
                    ->select('first_name','last_name','email','phone')
                    ->where('id', $user->user_id)
                    ->first();
                
                if ( $user_data->email != null && $user_data->phone != null) {
                    $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->email .")" ."(" .$user_data->phone .")" ;
                }
                else if ($user_data->email != null) {
                    $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->email .")";
                }
                else if ($user_data->phone != null) {
                    $username = $user_data->first_name ." " .$user_data->last_name ."(" .$user_data->phone .")";
                }
                else {
                    $username = $user_data->first_name ." " .$user_data->last_name;
                }

                    $query = PaymentReport::create(['transaction_reference' => "Bulk Transfered",
                            'amount'=> $indl_amount,
                            'status'=> $indl_status,
                            'transaction_code'=> $indl_transfer_code,
                            'payment_at'=> $indl_payment_at,
                            'user_id'=>$user->user_id,
                            'recipient_code'=>$indl_recipient,
                            'username'=> $username,
                            'user_email'=> $user_data->email,
                            'user_phone'=> $user_data->phone,

                    ]);  
            }

            foreach($selected_requests as $item) {
                    $update = DB::table('withdraw_requests')
                        ->where('id', $item)
                        ->update(['status' => "approved"]);
            }

            echo "{\"status\" : \"success\",
            \"message\" : \"Transfer Success\",
            \"data\" : ".$result ."
            }";
          }
          else {
                echo "{\"status\" : \"error\",
                \"message\" : \"Transfer could not be finalized\",
               \"data\" : ".$result ."
            }";

          }

    }
}

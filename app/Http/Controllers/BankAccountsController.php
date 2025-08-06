<?php
namespace Unicodeveloper\Paystack;

namespace App\Http\Controllers;
use App\UserBankDetails;
use App\BanksList;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class BankAccountsController extends Controller
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
        $this->middleware(['auth']);

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

    public function index(Request $request)
    {
        $user=auth()->user();

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];
        if($request->form){
            $bank_list=userBankDetails::where('user_id',$user->id)
                ->whereBetween('created_at',array($request->form['date_from'],$request->form['date_to']))->get()->all();
        }
        else
        {
            $bank_list=userBankDetails::where('user_id',$user->id)
                ->whereBetween('created_at',array($filter_arr['date_from'],$filter_arr['date_to']))->get()->all();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;

        $bank_list=userBankDetails::where('user_id',$user->id)->get()->all();
        $view_data=['bank_list'=>$bank_list, 'filter_arr'=>$filter_arr,];
        return view('Bank-accounts.bank-accounts', $view_data);
    }

    public function addAccount(Request $request)
    {
        $user=auth()->user();

        // $bank_list = BanksList::all(['bank_name'])->toArray();
        $bank_list = BanksList::pluck('bank_name', 'id')->toArray();
        // $bank_list = BanksList::all();

        // $result_list = compact('bank_list');
        $view_data = ['bank_list' => $bank_list];
        return view('Bank-accounts.add-bank-account', $view_data);
        return $bank_list;
// dd($view_data);

    }

    public function add(Request $request)
    {
        $user=auth()->user();
        $validator = Validator();
        $AccountNumber = $request->form['account_number'];
        $name = $request->form['account_name'];
        $bank = $request->bank;

        $code = DB::table('banks_list')
                     ->select('bank_code')
                     ->where('id', $bank)
                     ->first();
        $BankCode = $code->bank_code;

        $account_check = DB::table('user_bank_accounts')
            ->where(['user_id' => $user->id])
            ->where(['account_number' => $AccountNumber])
            ->count();

          if ($account_check != null) {
            return redirect('/bank-accounts')->with('errors', 'Account already exists!');
          }
          else {

            $curl = curl_init();
              curl_setopt_array($curl, array(
              CURLOPT_URL => $this->baseUrl ."/bank/resolve?account_number=".rawurlencode($AccountNumber)."&bank_code=".rawurlencode($BankCode),
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => $this->authBearer,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" .$err;
            } else {
                // echo $response;
                $result = json_decode($response);
                $verify = $result->status;
            }
                if ($verify) {
                    # code...
                    $name = $result->data->account_name;


                    if (!empty($name) && !empty($AccountNumber) && !empty($BankCode)) {

                       $url = $this->baseUrl . "/transferrecipient";
                       $fields = [
                         'type' => "nuban",
                         'name' => $name,
                         'account_number' => $AccountNumber,
                         'bank_code' => $BankCode,
                         'currency' => 'NGN'
                       ];
                       $fields_string = http_build_query($fields);
                       //open connection
                       $ch = curl_init();

                       //set the url, number of POST vars, POST data
                       curl_setopt($ch,CURLOPT_URL,$url);
                       curl_setopt($ch,CURLOPT_POST, true);
                       curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
                       curl_setopt($ch,CURLOPT_HTTPHEADER,$this->authBearer);

                       //So that curl_exec returns the contents of the cURL, rather than echoing it
                       curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

                       //execute post
                       $result = curl_exec($ch);
                       // echo $result;
                       //var_dump($result);

                       $info = json_decode($result);
                       $recipient_name = $info->data->name;
                       $recipient_code = $info->data->recipient_code;
                       $num_type = $info->data->type;
                       $Acct_Numb = $info->data->details->account_number;
                       $Bank_Code = $info->data->details->bank_code;
                       $Bank_Name = $info->data->details->bank_name;
                       $currency = $info->data->currency;
                       $createdAt = $info->data->createdAt;

                       if ($info->status) {

                            // $values = array('name' => $recipient_name, 'recipient_code' => $recipient_code, 'type' => $num_type, 'account_number' => $Acct_Numb, 'bank_code' => $Bank_Code, 'bank_name' => $Bank_Name, 'currency' => $currency, 'createdAt' => $createdAt);
                            // $query =  DB::table('paystack_transfer_recipient')->insert($values);

                            $change_state = "Inactive";
                            $update = DB::table('user_bank_accounts')
                                ->where('user_id', $user->id)
                                ->update(['Active_status' => $change_state]);

                            $query = userBankDetails::create(['user_id'=>$user->id,
                                    'account_name'=> $recipient_name,
                                    'account_number'=> $Acct_Numb,
                                    'bank_name'=> $Bank_Name,
                                    'bank_code'=> $Bank_Code,
                                    // 'BVN_Number'=>$request->form['bvn_number'],
                                    'Active_status'=>'Active',
                                    'recipient_code'=> $recipient_code,
                                    'num_type'=> $num_type,

                                ]);
                                session([
                                    'account_status' => 1
                                ]);


                             if (!$query) {
                               // code...
                                echo 'There was an error';
                             }
                             else {
                                // session::flash('flash_message','successfully saved.');
                                // return redirect('/bank-accounts');
                                // return redirect()->route("bank_account");
                                return redirect('/bank-accounts')->with('status', 'Account added & is verified with Bank Code!');
                              exit();
                             }

                       }

                    }
                    else {
                          echo 'There was an error';
                     exit();
                    }
                }
                else {
                    // $view_data={'error'=>'Invalid Account Number or Bank Code, it is NOT resolved/verified'};
                    // return view('Bank-accounts.add-bank-account', $view_data);

                return redirect('/bank-accounts')->with('error', 'Invalid Account Number or Bank Code, it is NOT resolved/verified!');

                }
          }
    }

    public function activateAccount(Request $request, $id)
    {
        $user=auth()->user();
        $change_state_active = "Active";

        // update table
        $change_state = "Inactive";
        $update = DB::table('user_bank_accounts')
            ->where('user_id', $user->id)
            ->update(['Active_status' => $change_state]);

        $update_active = DB::table('user_bank_accounts')
            ->where('id', $id)
            ->update(['Active_status' => $change_state_active]);

        return redirect('/bank-accounts');
    }

    public function updateBanksList(Request $request)
    {

          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/bank",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: Bearer " . $this->secretKey,
              "Cache-Control: no-cache",
            ),
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);

          $finalize = json_decode($response);
          // dd($finalize);

          $status = $finalize->status;
          $banks_array = $finalize->data;
          if ($status) {

            BanksList::truncate();

            foreach ($banks_array as $elemt) {

                $bank_name = $elemt->name;
                $bank_code = $elemt->code;
                $country = $elemt->country;
                $currency = $elemt->currency;
                $type = $elemt->type;
                $list_id = $elemt->id;

                    $query = BanksList::create([
                            'bank_name' => $bank_name,
                            'bank_code' => $bank_code,
                            'country' => $country,
                            'currency' => $currency,
                            'type' => $type,
                            'bank_list_id' => $list_id,
                    ]);
            }
            return redirect('/withdraw-requests')->with('list_updated', 'Banks list successfully updated!');

          }
          else {

            return redirect('/withdraw-requests')->with('list_update_failed', 'Error updating Banks list!');

          }
    }

}

<?php

namespace App\Http\Controllers;

use App\UserBankDetails;
use App\BanksList;
use App\User; // <-- ADDED: This tells the controller where to find the User model.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- ADDED: Required for auth()->user()
use Illuminate\Support\Facades\Validator; // <-- ADDED: Required for validation

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
     * Authorization Header
     * @var array
     */
    protected $authBearer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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
        $this->authBearer = [
            "Authorization: Bearer " . $this->secretKey,
            "Cache-Control: no-cache",
        ];
    }

    /**
     * Display a list of the user's bank accounts.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];
        if ($request->form) {
            $bank_list = UserBankDetails::where('user_id', $user->id)
                ->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']])->get();
        } else {
            $bank_list = UserBankDetails::where('user_id', $user->id)
                ->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']])->get();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;

        $view_data = ['bank_list' => $bank_list, 'filter_arr' => $filter_arr];
        return view('Bank-accounts.bank-accounts', $view_data);
    }

    /**
     * Show the form for adding a new bank account (Paystack version).
     */
    public function addAccount(Request $request)
    {
        $bank_list = BanksList::pluck('bank_name', 'id')->toArray();
        $view_data = ['bank_list' => $bank_list];
        return view('Bank-accounts.add-bank-account', $view_data);
    }

    /**
     * Process the addition of a new bank account using Paystack API.
     */
    public function add(Request $request)
    {
        $user = auth()->user();
        $AccountNumber = $request->form['account_number'];
        $bankId = $request->bank;

        $bankDetails = DB::table('banks_list')
            ->select('bank_code')
            ->where('id', $bankId)
            ->first();

        if (!$bankDetails) {
            return redirect('/bank-accounts')->with('error', 'Invalid Bank selected!');
        }
        $BankCode = $bankDetails->bank_code;

        $account_check = DB::table('user_bank_accounts')
            ->where(['user_id' => $user->id])
            ->where(['account_number' => $AccountNumber])
            ->count();

        if ($account_check != null) {
            return redirect('/bank-accounts')->with('errors', 'Account already exists!');
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . "/bank/resolve?account_number=" . rawurlencode($AccountNumber) . "&bank_code=" . rawurlencode($BankCode),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $this->authBearer,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return redirect('/bank-accounts')->with('error', "cURL Error #:" . $err);
        }

        $result = json_decode($response);
        if (!($result->status ?? false)) {
            return redirect('/bank-accounts')->with('error', $result->message ?? 'Invalid Account Number or Bank Code, it is NOT resolved/verified!');
        }

        $name = $result->data->account_name;

        $url = $this->baseUrl . "/transferrecipient";
        $fields = [
            'type' => "nuban",
            'name' => $name,
            'account_number' => $AccountNumber,
            'bank_code' => $BankCode,
            'currency' => 'NGN'
        ];
        $fields_string = http_build_query($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->authBearer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $recipient_response = curl_exec($ch);
        curl_close($ch);

        $info = json_decode($recipient_response);

        if ($info->status ?? false) {
            DB::table('user_bank_accounts')
                ->where('user_id', $user->id)
                ->update(['Active_status' => 'Inactive']);

            UserBankDetails::create([
                'user_id' => $user->id,
                'account_name' => $info->data->name,
                'account_number' => $info->data->details->account_number,
                'bank_name' => $info->data->details->bank_name,
                'bank_code' => $info->data->details->bank_code,
                'Active_status' => 'Active',
                'recipient_code' => $info->data->recipient_code,
                'num_type' => $info->data->type,
            ]);

            return redirect('/bank-accounts')->with('status', 'Account added & is verified with Bank Code!');
        }

        return redirect('/bank-accounts')->with('error', 'Could not create transfer recipient.');
    }

    /**
     * Activate a specific bank account for the user.
     */
    public function activateAccount($id)
    {
        $user = auth()->user();
        DB::table('user_bank_accounts')
            ->where('user_id', $user->id)
            ->update(['Active_status' => 'Inactive']);

        DB::table('user_bank_accounts')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update(['Active_status' => 'Active']);

        return redirect('/bank-accounts')->with('status', 'Account has been set to active.');
    }

    /**
     * Update the list of banks from Paystack.
     */
    public function updateBanksList(Request $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/bank",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->authBearer,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $finalize = json_decode($response);

        if ($finalize->status ?? false) {
            BanksList::truncate();
            foreach ($finalize->data as $bank) {
                BanksList::create([
                    'bank_name' => $bank->name,
                    'bank_code' => $bank->code,
                    'country' => $bank->country,
                    'currency' => $bank->currency,
                    'type' => $bank->type,
                    'bank_list_id' => $bank->id,
                ]);
            }
            return redirect('/withdraw-requests')->with('list_updated', 'Banks list successfully updated!');
        }

        return redirect('/withdraw-requests')->with('list_update_failed', 'Error updating Banks list!');
    }

    /**
     * (ADMIN) Display the form for an admin to add a bank account for any user.
     * This method is protected by the 'admin' middleware defined in web.php.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showAdminAddForm()
    {
        // Fetch all users to populate the user selection dropdown in the form.
        $users = User::orderBy('first_name')->get();

        // Fetch all available banks for the bank selection dropdown.
        $banks = BanksList::orderBy('bank_name')->get();

        // Return the admin-specific view, passing the users and banks lists to it.
        // The path corresponds to 'resources/views/admin-views/transaction/add-user-bank-account.blade.php'.
        return view('admin-views.transaction.add-user-bank-account', [
            'users' => $users,
            'banks' => $banks,
        ]);
    }

    /**
     * (ADMIN) Store a new bank account submitted by an admin.
     * This method is protected by the 'admin' middleware defined in web.php.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAdminBankAccount(Request $request)
    {
        // 1. Validate all the data submitted from the admin form.
        $validator = Validator::make($request->all(), [
            // CORRECTED: The validation now checks the 'user' table, not 'users'.
            'user_id'        => 'required|integer|exists:user,id',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|numeric|digits:10',
            'bank_id'        => 'required|integer|exists:banks_list,id',
            'Active_status'  => 'required|in:Active,Inactive',
            'recipient_code' => 'nullable|string|max:100',
            'num_type'       => 'nullable|string|max:50',
        ]);

        // If validation fails, redirect back with errors and original input.
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userId = $request->input('user_id');
        $bank = BanksList::find($request->input('bank_id'));

        // 2. Use a database transaction to ensure data integrity.
        DB::beginTransaction();
        try {
            // If the new account is being set as 'Active', first deactivate
            // all other bank accounts for that specific user.
            if ($request->input('Active_status') === 'Active') {
                UserBankDetails::where('user_id', $userId)->update(['Active_status' => 'Inactive']);
            }

            // 3. Create the new bank account record in the database.
            UserBankDetails::create([
                'user_id'        => $userId,
                'account_name'   => $request->input('account_name'),
                'account_number' => $request->input('account_number'),
                'bank_name'      => $bank->bank_name,
                'bank_code'      => $bank->bank_code,
                'bank_id'        => $bank->id,
                'Active_status'  => $request->input('Active_status'),
                'recipient_code' => $request->input('recipient_code'),
                'num_type'       => $request->input('num_type', 'nuban'), // Default to 'nuban'
            ]);

            // If everything is successful, commit the changes to the database.
            DB::commit();
             return redirect()->back()->with('success', 'Bank account successfully added and verified!');
        } catch (\Exception $e) {
            // If any error occurs, roll back the transaction and redirect with an error message.
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while saving the account. Please try again.')->withInput();
        }
    }
}
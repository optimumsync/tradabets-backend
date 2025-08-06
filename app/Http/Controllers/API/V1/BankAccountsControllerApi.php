<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth; // <-- Import Auth facade
use Illuminate\Support\Facades\Log; // <-- Import Log facade for better error tracking
use App\Http\Controllers\Controller;
use App\UserBankDetails;
use App\BanksList;
use App\User; // <-- Import User model, used in index method

class BankAccountsControllerApi extends Controller
{
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->setConfig();
    }

    protected function setConfig()
    {
        $this->secretKey = Config::get('paystack.secretKey');
        $this->baseUrl = Config::get('paystack.paymentUrl');

        Log::info('Paystack Config:', [
            'secret_key_loaded' => !empty($this->secretKey) ? 'YES' : 'NO',
            'paymentUrl' => $this->baseUrl,
        ]);

        if (empty($this->secretKey)) {
            Log::error('Paystack secret key is not configured or empty.');
            throw new \RuntimeException('Paystack secret key not configured');
        }

        if (empty($this->baseUrl)) {
            Log::error('Paystack base URL is not configured or empty.');
            throw new \RuntimeException('Paystack base URL not configured');
        }
    }

    /**
     * Get user's bank accounts. Can filter by user_id if admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        $targetUserId = $request->input('user_id');
        $user = null;

        if ($targetUserId && ($authUser->role === 'admin')) {
            $user = User::find($targetUserId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }
        } elseif ($targetUserId && $targetUserId != $authUser->id && !($authUser->role === 'admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. You can only view your own bank accounts.'
            ], 403);
        } else {
            $user = $authUser;
            $targetUserId = $user->id;
        }

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Authentication failed.'], 401);
        }

        // Remove date filter
        $query = UserBankDetails::where('user_id', $user->id);

        $bankAccounts = $query->get();

        return response()->json([
            'status' => true,
            'bank_list' => $bankAccounts,
            'target_user_id' => $targetUserId,
            'message' => 'Bank accounts retrieved successfully.'
        ]);
    }

    // ... (rest of your controller methods remain unchanged)
    public function getBankList()
    {
        return response()->json([
            'status' => true,
            'bank_list' => BanksList::pluck('bank_name', 'id')->toArray(),
            'message' => 'Banks list retrieved successfully.'
        ]);
    }

    public function addAccount(Request $request)
    {
        $user = Auth::user();

        if (!Schema::hasTable('banks_list')) {
            Log::error('Database error: banks_list table not found.');
            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error: Banks configuration missing.'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'form.account_number' => 'required|numeric|digits:10',
            'bank' => 'required|integer|exists:banks_list,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $accountNumber = $request->input('form.account_number');
        $bankId = (int)$request->input('bank');

        try {
            $bank = BanksList::find($bankId);
            if (!$bank) {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected bank does not exist. Please update your banks list.'
                ], 400);
            }

            $bankCode = $bank->bank_code;

            if (UserBankDetails::where('user_id', $user->id)
                ->where('account_number', $accountNumber)
                ->where('bank_code', $bankCode)
                ->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'This account number is already registered with this bank in your profile.'
                ], 400);
            }

            $verificationResult = $this->verifyAccountWithPaystack($accountNumber, $bankCode);
            if (!($verificationResult['status'] ?? false)) {
                Log::error('Paystack account verification failed', [
                    'user_id' => $user->id,
                    'account' => $accountNumber,
                    'bank_code' => $bankCode,
                    'response' => $verificationResult
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Account verification failed: ' . ($verificationResult['message'] ?? 'Invalid account details or Paystack error')
                ], 400);
            }

            $recipientResult = $this->createPaystackTransferRecipient(
                $verificationResult['data']['account_name'],
                $accountNumber,
                $bankCode
            );

            if (!($recipientResult['status'] ?? false)) {
                Log::error('Paystack recipient creation failed', [
                    'user_id' => $user->id,
                    'response' => $recipientResult
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Could not create transfer recipient: ' . ($recipientResult['message'] ?? 'Unknown Paystack error')
                ], 400);
            }

            DB::beginTransaction();
            try {
                $bankAccount = $this->saveBankAccount($user->id, $recipientResult['data']);
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Account added and verified successfully',
                    'data' => [
                        'id' => $bankAccount->id,
                        'account_name' => $bankAccount->account_name,
                        'account_number' => $bankAccount->account_number,
                        'bank_name' => $bankAccount->bank_name,
                        'recipient_code' => $bankAccount->recipient_code
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database save failed for new bank account (user ' . $user->id . '): ' . $e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save account details due to an internal error.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Unexpected error in addAccount (user ' . $user->id . '): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred while adding the account. Please try again.'
            ], 500);
        }
    }

    private function verifyAccountWithPaystack($accountNumber, $bankCode)
    {
        $url = $this->baseUrl . "/bank/resolve?account_number=" . rawurlencode($accountNumber) . "&bank_code=" . rawurlencode($bankCode);

        try {   
            $response = Http::withToken($this->secretKey)
                ->timeout(10)
                ->get($url);
            
            $result = $response->json();

            if (!$response->successful()) {
                Log::warning('Paystack verification non-successful HTTP response', ['status' => $response->status(), 'body' => $result]);
                return ['status' => false, 'message' => $result['message'] ?? 'Paystack service unavailable or invalid request.'];
            }

            if (!isset($result['status']) || !$result['status']) {
                Log::warning('Paystack verification failed response (status false)', ['body' => $result]);
                return ['status' => false, 'message' => $result['message'] ?? 'Invalid response from Paystack API.'];
            }

            return $result;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paystack Connection Error (verifyAccount): ' . $e->getMessage());
            return ['status' => false, 'message' => 'Could not connect to Paystack. Please try again later.'];
        } catch (\Exception $e) {
            Log::error('Paystack API error (verifyAccount): ' . $e->getMessage());
            return ['status' => false, 'message' => 'Paystack API error during verification: ' . $e->getMessage()];
        }
    }

    private function createPaystackTransferRecipient($name, $accountNumber, $bankCode)
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(10)
                ->post($this->baseUrl . '/transferrecipient', [
                    'type' => 'nuban',
                    'name' => $name,
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                    'currency' => 'NGN',
                ]);

            $data = $response->json();

            if (!$response->successful()) {
                Log::warning('Paystack recipient creation non-successful HTTP response', ['status' => $response->status(), 'body' => $data]);
                return ['status' => false, 'message' => $data['message'] ?? 'Paystack service unavailable or invalid request.'];
            }

            if (!isset($data['status']) || !$data['status']) {
                Log::warning('Paystack recipient creation failed response (status false)', ['body' => $data]);
                return ['status' => false, 'message' => $data['message'] ?? 'Unknown error from Paystack during recipient creation.'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Paystack Connection Error (createRecipient): ' . $e->getMessage());
            return ['status' => false, 'message' => 'Could not connect to Paystack for recipient creation. Please try again later.'];
        } catch (\Exception $e) {
            Log::error('Paystack API error (createRecipient): ' . $e->getMessage());
            return ['status' => false, 'message' => 'Paystack API error during recipient creation: ' . $e->getMessage()];
        }
    }

    private function saveBankAccount($userId, $recipientData)
    {
        UserBankDetails::where('user_id', $userId)
            ->update(['Active_status' => 'Inactive']);

        return UserBankDetails::create([
            'user_id' => $userId,
            'account_name' => $recipientData['name'] ?? $recipientData['details']['account_name'] ?? 'N/A',
            'account_number' => $recipientData['details']['account_number'],
            'bank_name' => $recipientData['details']['bank_name'],
            'bank_code' => $recipientData['details']['bank_code'],
            'Active_status' => 'Active',
            'recipient_code' => $recipientData['recipient_code'],
            'num_type' => $recipientData['type'],
        ]);
    }

    public function activateAccount($id)
    {
        $user = Auth::user();

        $accountToActivate = UserBankDetails::where('id', $id)
                                            ->first();

        if (!$accountToActivate) {
            return response()->json(['status' => false, 'message' => 'Bank account not found or does not belong to you.'], 404);
        }

        UserBankDetails::where('id', $id)
        ->update(['Active_status' => 'Active']);

        $accountToActivate->update(['Active_status' => 'Active']);

        return response()->json(['status' => true, 'message' => 'Account activated successfully']);
    }

    public function createTransferRecipient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string',
            'bank_code' => 'required|string',
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $result = $this->createPaystackTransferRecipient(
                $request->input('name'),
                $request->input('account_number'),
                $request->input('bank_code')
            );

            if (!($result['status'] ?? false)) {
                Log::error('Manual recipient creation failed: ' . ($result['message'] ?? 'Unknown error'), ['request' => $request->all()]);
                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Could not create transfer recipient.'
                ], 400);
            }

            return response()->json([
                'status' => true,
                'recipient_code' => $result['data']['recipient_code'],
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Exception during manual recipient creation: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred during recipient creation.'
            ], 500);
        }
    }

    public function updateBanksList()
    {
        $user = Auth::user();

        if (!($user->role === 'admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action. Only administrators can update the banks list.'
            ], 403);
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->timeout(30)
                ->get('https://api.paystack.co/bank');

            $result = $response->json();

            if (!$response->successful()) {
                Log::warning('Paystack banks list API returned non-successful HTTP status.', ['status' => $response->status(), 'body' => $result]);
                return response()->json(['status' => false, 'message' => 'Error fetching banks list from Paystack.'], $response->status());
            }

            if (!isset($result['status']) || !$result['status'] || !isset($result['data']) || !is_array($result['data'])) {
                Log::warning('Paystack banks list API response invalid.', ['body' => $result]);
                return response()->json(['status' => false, 'message' => 'Invalid data received from Paystack banks API.'], 400);
            }

            DB::beginTransaction();

            BanksList::truncate();

            foreach ($result['data'] as $bank) {
                BanksList::create([
                    'bank_name' => $bank['name'],
                    'bank_code' => $bank['code'],
                    'country' => $bank['country'],
                    'currency' => $bank['currency'],
                    'type' => $bank['type'],
                    'bank_list_id' => $bank['id'],
                ]);
            }

            DB::commit();
            Log::info('Banks list successfully updated from Paystack by admin ' . $user->id);
            return response()->json(['status' => true, 'message' => 'Banks list successfully updated!']);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            DB::rollBack();
            Log::error('Connection error updating banks list from Paystack: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Could not connect to Paystack to update banks list.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update banks list from Paystack: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => 'Failed to update banks list due to an internal error.'], 500);
        }
    }
}
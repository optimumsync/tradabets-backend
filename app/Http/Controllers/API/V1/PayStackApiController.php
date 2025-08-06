<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\PaymentReport;
use Illuminate\Support\Collection;
use App\Balance; 

class PaystackApiController extends Controller
{
    protected $secretKey;
    protected $baseUrl;
    protected $authBearer;

    public function __construct()
    {
        $this->secretKey = env('PAYSTACK_SECRET_KEY');
        $this->baseUrl = env('PAYSTACK_PAYMENT_URL');
        $this->authBearer = [
            "Authorization: Bearer " . $this->secretKey,
            "Cache-Control: no-cache",
        ];
    }

   
    public function initiate(Request $request, $id)
    {
        $user = DB::table('withdraw_requests')->select('user_id', 'amount')->where('id', $id)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal request not found.'
            ], 404);
        }

        $user_data = DB::table('user')->where('id', $user->user_id)->first();
        if (!$user_data) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $username = trim("{$user_data->first_name} {$user_data->last_name}");
        if ($user_data->email) $username .= " ({$user_data->email})";
        if ($user_data->phone) $username .= " ({$user_data->phone})";

        $recipient_code = DB::table('user_bank_accounts')
            ->where('user_id', $user->user_id)
            ->where('Active_status', 'Active')
            ->value('recipient_code');

        if (!$recipient_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipient code not found for the user. Please add a valid bank account.'
            ], 422);
        }

    // Check balance using the Balance model
    $balance = Balance::where('user_id', $user->user_id)->first();
    if (!$balance) {
        return response()->json([
            'status' => 'error',
            'message' => 'Balance record not found for the user.'
        ], 422);
    }

        // Convert balance to kobo if stored in NGN
        $current_balance = $balance->balance * 100; // Convert to kobo
        $amount = round($user->amount * 100); // Convert withdrawal amount to kobo

        \Log::info('Balance and Amount Check:', [
            'current_balance' => $current_balance,
            'amount' => $amount,
            'user_id' => $user->user_id
        ]);

        if ($current_balance < $amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance in the system. Please top up and try again.'
            ], 422);
        }

        // Deduct the amount from the balance
        $balance->decrement('balance', $amount / 100); // Deduct in NGN if balance is stored in NGN

        $fields = [
            'source' => "balance",
            'amount' => $amount,
            'recipient' => $recipient_code,
            'reason' => "Withdrawal transfers"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "{$this->baseUrl}/transfer",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => $this->authBearer,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $result = curl_exec($ch);
        $initiate = json_decode($result);

        if ($initiate->status === true) {
            $data = $initiate->data;
            PaymentReport::create([
                
                'amount' => $data->amount / 100,
                'status' => $data->status,
                'transaction_code' => $data->transfer_code,
                'payment_at' => $data->createdAt,
                'user_id' => $user->user_id,
                'recipient_code' => $recipient_code,
                'username' => $username,
                'user_email' => $user_data->email,
                'user_phone' => $user_data->phone,
            ]);
            
            DB::table('withdraw_requests')->where('id', $id)->update(['status' => 'approved']);

            return response()->json([
                'status' => 'success',
                'message' => 'Transfer successful',
                'data' => $data
            ]);
        }

        // Revert the balance deduction if the transfer fails
        $balance->increment('balance', $amount);

        return response()->json([
            'status' => 'error',
            'message' => $initiate->message ?? 'Transfer failed',
            'data' => $initiate
        ], 422);
    }

    public function bulkTransfer(Request $request)
    {
        $selected_requests = $request->input('data', []);
        $collection = new Collection();

        foreach ($selected_requests as $item) {
            $info = DB::table('withdraw_requests')
                ->select('amount', 'recipient_code')
                ->where('id', $item)
                ->first();

            if ($info) {
                $collection->push([
                    'amount' => round($info->amount * 100),
                    'reason' => "Transfer for Withdrawal request",
                    'recipient' => $info->recipient_code
                ]);
            }
        }

        $payload = [
            "currency" => "NGN",
            "source" => "balance",
            "transfers" => $collection
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.paystack.co/transfer/bulk",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->secretKey,
                "Content-Type: application/json"
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $result = curl_exec($ch);
        $response = json_decode($result);

        if ($response->status) {
            foreach ($response->data as $transfer) {
                $user = DB::table('withdraw_requests')->select('user_id')
                    ->where('recipient_code', $transfer->recipient)->first();
                $user_data = DB::table('user')->where('id', $user->user_id)->first();

                $username = trim("{$user_data->first_name} {$user_data->last_name}");
                if ($user_data->email) $username .= " ({$user_data->email})";
                if ($user_data->phone) $username .= " ({$user_data->phone})";

                PaymentReport::create([
                    
                    'amount' => $transfer->amount / 100,
                    'status' => $transfer->status,
                    'transaction_code' => $transfer->transfer_code,
                    'payment_at' => now(),
                    'user_id' => $user->user_id,
                    'recipient_code' => $transfer->recipient,
                    'username' => $username,
                    'user_email' => $user_data->email,
                    'user_phone' => $user_data->phone,
                ]);

                DB::table('withdraw_requests')
                    ->where('recipient_code', $transfer->recipient)
                    ->update(['status' => 'approved']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk transfer completed',
                'data' => $response->data
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Bulk transfer failed',
            'data' => $response
        ], 422);
    }
}

<?php
namespace App\Helpers;
use App\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
class PaymentHelper
{
    public static function update_transaction($amount, $user_id, $reference, $status, $remarks = null, $transaction_type = null)
    {
        $avail_balance = Balance::where('user_id', $user_id)->first()->balance;
        // Only update balance for successful transactions
        if ($status === 'deposit' || $status === 'bonus') {
            $balance_amt = $avail_balance + $amount;
            DB::table('balance')
                ->where('user_id', $user_id)
                ->update(['balance' => $balance_amt]);
            session([
                'avail_balance' => $balance_amt
            ]);
        } else {
            // For failed or other statuses, do not change balance
            $balance_amt = $avail_balance;
        }
        // Create a new transaction record
        \App\Models\Transaction::create([
            'user_id' => $user_id,
            'status' => $status,
            'amount' => $amount,
            'opening_balance' => $avail_balance,
            'closing_balance' => $balance_amt,
            'transaction_id' => $reference,
            'remarks' => $remarks,
            'transaction_type' => $transaction_type ?? $status, 
        ]);
    }
    public static function initiate_transaction($user_id, $status, $amount, $remarks = null, $reference = null, $transaction_type = null)
    {
        $avail_balance = Balance::where('user_id', $user_id)->first()->balance;
        $data = \App\Models\Transaction::create([
            'user_id' => $user_id,
            'status' => $status,
            'amount' => ($amount/100),
            'opening_balance' => $avail_balance,
            'transaction_id' => $reference, // Store the reference
            'remarks' => $remarks,
        ]);
        return $reference;
    }
    public static function get_user_id_by_reference($reference)
    {
        return Transaction::where('transaction_id', $reference)->first()->user_id;
    }
    public static function create_transaction($amount, $user_id,$status)
    {
        $avail_balance = Balance::where('user_id', $user_id)->first()->balance;
        $balance_amt = $avail_balance + $amount;
        $final_balance = DB::table('balance')
            ->where('user_id', $user_id)
            ->update(['balance' => $balance_amt]);
        session([
            'avail_balance' => $balance_amt
        ]);
        \App\Models\Transaction::create(['user_id' => $user_id,
            'status' => $status,
            'amount' => $amount,
            'opening_balance' => $avail_balance,
            'closing_balance' => $balance_amt,
            'transaction_type' => $transaction_type ]);
            
        //bonus amount can be set here for Play credit
    }
    public static function checkUserFirstDeposit($user_id)
    {
        return $transaction = Transaction::where('user_id', $user_id)->where('status', 'deposit')->count();
    }
    public static function createDepositBonus($amount, $user_id, $status)
    {
        $avail_balance = Balance::where('user_id', $user_id)->first()->balance;
        $balance_amt = $avail_balance + $amount;
        $final_balance = DB::table('balance')
            ->where('user_id', $user_id)
            ->update(['balance' => $balance_amt]);
        session([
            'avail_balance' => $balance_amt
        ]);
        \App\Models\Transaction::create(['user_id' => $user_id,
            'status' => $status,
            'amount' => $amount,
            'opening_balance' => $avail_balance,
            'closing_balance' => $balance_amt,
            'remarks'=>'First time Deposit Bonus'
        ]);
    }
}

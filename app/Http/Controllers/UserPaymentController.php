<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Transaction;
use App\WithdrawRequest;

class UserPaymentController extends Controller
{
    /**
     * Display the specified user's details on a dedicated page with
     * enhanced payment history.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showUserDetails($id, Request $request)
    {
        // Eager load relationships for the user
        $user = User::with(['userBankDetails', 'withdrawals', 'transaction'])->findOrFail($id);

        // --- All Transactions Tab ---
        $allTransactions = $user->transaction()->latest()->get();

        // --- Deposit History Tab ---
        $depositStatus = $request->get('deposit_status', 'All');
        $depositQuery = $user->transaction()->where('status', 'deposit')->latest();

        if ($depositStatus !== 'All') {
            $depositQuery->where('status', $depositStatus);
        }
        $deposits = $depositQuery->get();

        // --- Withdrawal History Tab with Filter ---
        $withdrawalStatus = $request->get('withdrawal_status', 'All');
        $withdrawalQuery = $user->withdrawals()->latest();

        if ($withdrawalStatus !== 'All') {
            $withdrawalQuery->where('status', $withdrawalStatus);
        }

        $withdrawals = $withdrawalQuery->get();

        $view_data = [
            'user' => $user,
            'allTransactions' => $allTransactions,
            'deposits' => $deposits,
            'depositStatus' => $depositStatus,
            'withdrawals' => $withdrawals,
            'withdrawalStatus' => $withdrawalStatus,
        ];

        return view('user-list.user-details', $view_data);
    }

    /**
     * Display a separate payment history page.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showPaymentHistory($id, Request $request)
    {
        $user = User::with(['userBankDetails', 'withdrawals', 'transaction'])->findOrFail($id);

        $allTransactions = $user->transaction()->latest()->get();

        $depositStatus = $request->get('deposit_status', 'All');
        $depositQuery = $user->transaction()->whereIn('status', ['deposit', 'failed'])->latest();

        if ($depositStatus !== 'All') {
             // If a specific status is selected, override the general filter
            $depositQuery->where('status', $depositStatus);
        }
        $deposits = $depositQuery->get();

        $withdrawalStatus = $request->get('withdrawal_status', 'All');
        $withdrawalQuery = $user->withdrawals()->latest();
        if ($withdrawalStatus !== 'All') {
            $withdrawalQuery->where('status', $withdrawalStatus);
        }
        $withdrawals = $withdrawalQuery->get();

        $view_data = [
            'user' => $user,
            'allTransactions' => $allTransactions,
            'deposits' => $deposits,
            'depositStatus' => $depositStatus,
            'withdrawals' => $withdrawals,
            'withdrawalStatus' => $withdrawalStatus,
        ];

        return view('user-list.user-payment-history', $view_data);
    }
}
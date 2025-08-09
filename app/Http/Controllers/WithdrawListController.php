<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WithdrawRequest;

class WithdrawListController extends Controller
{
    /**
     * Display a list of all withdrawal requests.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $withdrawals = WithdrawRequest::with('user')->orderBy('created_at', 'desc')->get();
        // Change the view path to match your desired folder structure
        return view('admin-views.transaction.withdraw-list', compact('withdrawals'));
    }
}
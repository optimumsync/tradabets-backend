<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Mail\SendEmailNotification;
use App\WithdrawRequest;
use App\User;
use Illuminate\Support\Facades\DB;

class SendEmailController extends Controller
{
    function index()
    {
     // return view('send_email');
    }

    function send(Request $request)
    {
       
        $user=auth()->user();

        $id = $request->session()->get('withdraw_request_id');

        $displayData = WithdrawRequest::find($id);

        $user_id = $user->id;
        $name = $user->first_name .' '.$user->last_name;
        $email = $user->email;
        $amount = $displayData->amount;
        $status = $displayData->status;
        $request_id = $id;
        $requested_On = $displayData->created_at;
        $recipient = 'tradabets360@gmail.com';

        $data = array(
            'name' => $name,
            'email' => $email,
            'amount' => $amount,
            'user_id' => $user_id,
            'status' => $status,
            'request_id' => $request_id,
            'requested_On' => $requested_On,
            'url' => env('APP_URL') .'/withdraw-request/view/'.$id
        );

     Mail::to($recipient)->send(new SendMail($data));
     return back()->with('success', 'Your request is successful & queued for approval');  

    }
}

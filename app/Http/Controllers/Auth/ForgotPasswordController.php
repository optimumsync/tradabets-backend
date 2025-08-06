<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordOTP;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

use Illuminate\Support\Facades\Log;
use App\Helpers\BaseHelper;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $userData = User::where('email', '=', $request->email)->first();
        if($userData->email_otp != $request->email_otp) {
            return 2;
        }

       // return $request;
            //return $request->only('email');
         //$this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        if($response == Password::RESET_LINK_SENT)
        {
            return 1;
        }
        else{
            return 0;
        }

       // return $response;

       /* return
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);*/

    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    // public function sendEmailOtp(Request $request)
    // {
    //     try {
    //         Log::info('sendEmailOtp: Start for email: ' . $request->email);

    //         $userData = User::where('email', '=', $request->email)->first();
    //         if (!$userData) {
    //             Log::error('sendEmailOtp: User not found for email: ' . $request->email);
    //             // dd('User not found for email:', $request->email); // Temporary dd for quick check
    //             return response()->json(['error' => true, 'message' => 'User not found']);
    //         }
    //         Log::info('sendEmailOtp: User found: ' . $userData->email);

    //         // Generate OTP
    //         $emailOtp = rand(100000, 999999);
    //         Log::info('sendEmailOtp: Generated OTP: ' . $emailOtp);

    //         // Save OTP to user record
    //         $userData->email_otp = $emailOtp;
    //         $userData->save();
    //         Log::info('sendEmailOtp: OTP saved for user: ' . $userData->email);

    //         // Create better formatted email content
    //         $content = "Your Tradabets password reset OTP is: <b>{$emailOtp}</b>.  Please use this code to reset your password.";
    //         $from_name = $userData->first_name . " " . $userData->last_name;
    //         $subject = "Tradabets - Password Reset OTP";

    //         Log::info('sendEmailOtp: Preparing to call BaseHelper::sendGridMail to ' . $userData->email . ' with subject: ' . $subject);

    //         // Send email using Brevo
    //         $emailSent = BaseHelper::sendGridMail($userData->email, $content, $from_name, $subject);
    //         if (!$emailSent) {
    //             Log::error('sendEmailOtp: Failed to send OTP email via BaseHelper to: ' . $userData->email);
    //             // dd('Failed to send email via BaseHelper'); // Temporary dd
    //             return response()->json(['error' => true, 'message' => 'Failed to send OTP']);
    //         }

    //         Log::info('sendEmailOtp: OTP email sent successfully to: ' . $userData->email);
    //         return response()->json(['error' => false, 'message' => 'OTP sent successfully']);

    //     } catch (\Exception $e) {
    //         Log::error('sendEmailOtp: Exception: ' . $e->getMessage());
    //         Log::error('sendEmailOtp: Stack trace: ' . $e->getTraceAsString());
    //         // dd('Exception in sendEmailOtp:', $e->getMessage()); // Temporary dd
    //         return response()->json(['error' => true, 'message' => 'An error occurred']);
    //     }
    // }

    public function sendEmailOtp(Request $request)
    {
        $userData = User::where('email', '=', $request->email)->first();

        if (!$userData) {
            return response()->json(['error' => true, 'message' => 'User not found'], 404);
        }

        $emailOtp = rand(100000, 999999);
        $userData->email_otp = $emailOtp;
        $userData->save();

        // Your SendGrid-like input
        $content = '<h1>Verification Code:</h1><p>' . $emailOtp . '</p>';
        $subject = "Verification Code:";
        $from_email = "noreply@tradabets.com"; // Must be verified in Brevo
        $from_name = "Tradabets";

        Mail::to($userData->email, $userData->first_name . ' ' . $userData->last_name)
            ->send(new ForgotPasswordOTP($content, $subject, $from_email, $from_name));

        return response()->json(['error' => false, 'message' => 'OTP sent successfully']);
    }

    public function verifyEmailOtp(Request $request)
    {
        $userData = User::where('email', '=', $request->email)->first();
        if($userData->email_otp != $request->email_otp) {
            return 2;
        }
        return 1;
    }
}

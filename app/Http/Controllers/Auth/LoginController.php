<?php

namespace App\Http\Controllers\Auth;

use App\Balance;
use App\Helpers\BaseHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Socialite;
use Auth; // Make sure this is Illuminate\Support\Facades\Auth
use App\User; // Or App\Models\User if you are using Laravel 8+ and moved it
use Illuminate\Http\Request;
use App\Token;
use Twilio\Rest\Client;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME; // This is typically '/home'

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Add 'devAutoLogin' to the exceptions for the guest middleware
        $this->middleware('guest')->except(['logout', 'devAutoLogin']);
    }

    // ... your existing userVerify method ...
    public function userVerify(Request $request){
        $user_email = User::where('email', '=', $request->username)->first();
        $user_phone = User::where('phone', '=', $request->username)->first();

        if($user_email && Hash::check($request->password, $user_email->password)) {
            echo 1;
        } else if($user_phone && Hash::check($request->password, $user_phone->password)) {
            echo 2;
        } else {
            echo 0;
        }
    }


    // ... your existing login method ...
    public function login(Request $request)
    {
        // Determine if the input is an email or phone for the 'username' field
        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $fieldType => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, $request->remember)) { // login attempt
            if (empty(Auth::user()->token)) {
                $user = User::find(Auth::user()->id);
                $user->token = BaseHelper::generateToken();
                $user->save();
            }
            // Successful login, AuthenticatesUsers trait will handle redirection
            // So we don't strictly need to return redirect from here if using the trait's login method
            // However, your current structure overrides the trait's login method.
            // The trait would normally handle redirecting to $this->redirectTo.
            return redirect()->intended($this->redirectTo); // Or redirect('/'); as you had
        }

        // If attempt fails, redirect back with error
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']); // Or a generic error
    }


    // ... your existing sendMobileEmailOtp method ...
    public function sendMobileEmailOtp(Request $request)
    {
        $userData = User::where('email', '=', $request->email)->orWhere('phone', '=', $request->email)->first();
        if (!$userData) {
            return response()->json(['error' => 'User not found'], 404);
        }

        //mobile otp
        $phoneOtp = rand(100000, 999999);
        $account_sid = getenv('TWILIO_ACCOUNT_SID');
        $auth_token = getenv('TWILIO_AUTH_TOKEN');
        // Ensure TWILIO_NUMBER is set in your .env
        $twilio_number = getenv('TWILIO_NUMBER') ?: "+13133563321"; // Fallback if not set
        $client = new Client($account_sid, $auth_token);

        // Ensure country_code and phone are present
        if ($userData->country_code && $userData->phone) {
            $phone = '+' . $userData->country_code . $userData->phone;
            try {
                $client->messages->create(
                    $phone,
                    [
                        'from' => $twilio_number,
                        'body' => 'Mobile verification OTP is: ' . $phoneOtp
                    ]
                );
            } catch (\Exception $e) {
                // Log error or handle appropriately
                // return response()->json(['error' => 'Failed to send SMS OTP: ' . $e->getMessage()], 500);
            }
        }


        //email otp
        $emailOtp = rand(100000, 999999);
        $details = [
            'title' => 'Tradabets Login OTP',
            'email_otp' => $emailOtp
        ];

        \Mail::to($userData->email)->send(new \App\Mail\OtpEmail($details));

        $response = [
            'phone_otp' => $phoneOtp, // This will be null if SMS failed or wasn't sent
            'email_otp' => $emailOtp
        ];

        return response()->json($response);
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // START: DEVELOPMENT ONLY - AUTO LOGIN METHOD
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    /**
     * WARNING: FOR DEVELOPMENT/TESTING ONLY. REMOVE BEFORE PRODUCTION.
     * Automatically logs in a predefined user and redirects to the account page.
     */
    public function devAutoLogin()
    {
        // Check if the app is in a local environment.
        // You might want to add more checks (e.g., APP_DEBUG === true)
        if (!app()->isLocal()) {
            // If not local, redirect to home or show an error. Do NOT allow auto-login.
            return redirect('/')->with('error', 'Auto-login is disabled in this environment.');
        }

        $defaultUserId = 1; // <<< IMPORTANT: Set this to the ID of the user you want to auto-login
        // OR, find by email if more convenient and IDs change:
        // $defaultUserEmail = 'developer@example.com';
        // $user = User::where('email', $defaultUserEmail)->first();

        $user = User::find($defaultUserId);

        if ($user) {
            Auth::login($user); // Log the user in

            // Generate token if it's part of your app's logic after login
            if (empty(Auth::user()->token)) {
                $loggedInUser = User::find(Auth::user()->id); // Re-fetch or use $user
                $loggedInUser->token = BaseHelper::generateToken();
                $loggedInUser->save();
            }

            // Redirect to the intended page (your account page)
            // $this->redirectTo is defined as RouteServiceProvider::HOME, which is usually '/home'
            return redirect()->intended($this->redirectTo);
            // Or explicitly: return redirect('/your-account-page-route');
        } else {
            // Handle case where the test user doesn't exist
            // You might want to create this user in your seeder
            return response("Error: Test user with ID {$defaultUserId} not found. Please create this user or update the ID in LoginController@devAutoLogin.", 404);
        }
    }
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // END: DEVELOPMENT ONLY - AUTO LOGIN METHOD
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


    /*public function redirectToGoogle()
    // ... your Google login methods if you use them ...
    */
}
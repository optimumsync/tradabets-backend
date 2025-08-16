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
     public function userVerify(Request $request)
    {
        $user_email = User::where('email', '=', $request->username)->first();
        $user_phone = User::where('phone', '=', $request->username)->first();

        // Check if user exists via email
        if ($user_email && Hash::check($request->password, $user_email->password)) {
            // Check if the user is active
            if (!$user_email->is_active) {
                return response()->json(3); // Status code for deactivated account
            }
            return response()->json(1); // Email and password are correct, and user is active
        } 
        // Check if user exists via phone
        else if ($user_phone && Hash::check($request->password, $user_phone->password)) {
            // Check if the user is active
            if (!$user_phone->is_active) {
                return response()->json(3); // Status code for deactivated account
            }
            return response()->json(2); // Phone and password are correct, and user is active
        } 
        // If credentials do not match
        else {
            return response()->json(0); // Incorrect credentials
        }
    }

   public function login(Request $request)
    {
        // Determine if the input is an email or phone for the 'username' field
        $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $fieldType => $request->email,
            'password' => $request->password
        ];

        // 1. Attempt to authenticate the user with their credentials
        if (Auth::attempt($credentials, $request->remember)) {
            
            // 2. Get the authenticated user
            $user = Auth::user();

            // 3. Check if the user's account is active
            if (!$user->is_active) {
                // If the account is not active, log the user out immediately
                Auth::logout();
                
                // Redirect back to the login page with an error message
                return redirect()->back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
            }

            // 4. If active, proceed with the normal login process
            if (empty($user->token)) {
                $user->token = BaseHelper::generateToken();
                $user->save();
            }
            
            return redirect()->intended($this->redirectTo);
        }

        // If authentication fails, redirect back with a generic error
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }


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
<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use App\Helpers\BaseHelper;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class RegistrationApiController extends Controller
{
    // OTP expiration time in minutes
    const OTP_EXPIRY_MINUTES = 5;

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:150',
            'last_name' => 'required|string|max:150',
            'email' => 'nullable|email|unique:user,email',
            'phone' => 'required|string|unique:user,phone',
            'country_code' => 'required|string',
            'country' => 'required|string|max:150',
            'state' => 'required|string|max:150',
            'city' => 'required|string|max:150',
            'date_of_birth' => 'nullable|date',
            'password' => 'required|string|min:6|confirmed',
        ], [], [
            'first_name' => 'Name',
            'last_name' => 'Surname',
            'password' => 'Password',
            'phone' => 'Phone',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store registration data in cache (do not create user yet)
        $registrationData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'country_code', 'country',
            'state', 'city', 'date_of_birth', 'password'
        ]);
        // Hash the password before storing
        $registrationData['password'] = Hash::make($registrationData['password']);

        // Use email if present, otherwise phone for cache key
        $cacheKeyId = !empty($registrationData['email']) ? $registrationData['email'] : $registrationData['phone'];
        $cacheKey = 'registration_' . $cacheKeyId;
        Cache::put($cacheKey, $registrationData, now()->addMinutes(self::OTP_EXPIRY_MINUTES));

        // Send OTP (simulate user object for OTP sending)
        $fakeUser = (object) $registrationData;
        $fakeUser->id = $cacheKeyId; // Use email or phone as temp ID for OTP cache
        $fakeUser->email = $registrationData['email'] ?? null;
        $fakeUser->first_name = $registrationData['first_name'];
        $fakeUser->last_name = $registrationData['last_name'];
        $fakeUser->country_code = $registrationData['country_code'];
        $fakeUser->phone = $registrationData['phone'];

        $otpResponse = $this->sendOTP($fakeUser);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration started. OTP sent to your phone and email.',
            'phone' => $registrationData['phone'],
            'email' => $registrationData['email'] ?? null,
            'otp_expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES)->toDateTimeString(),
            'debug_otp' => $otpResponse // for testing
        ]);
    }

    /**
     * Send OTP to both phone and email
     */
    public function sendOTP($user)
    {
        $otp = rand(100000, 999999);
        // Store in cache for 5 minutes
        Cache::put("otp_{$user->id}", $otp, now()->addMinutes(self::OTP_EXPIRY_MINUTES));
        $this->sendMobileOtp($user, $otp);
        $this->sendEmailOtp($user, $otp);
        return [
            'phone_otp' => $otp,
            'email_otp' => $otp
        ];
    }

    /**
     * Send mobile OTP via Twilio
     */
    private function sendMobileOtp($user, $otp)
    {
        try {
            if (!empty($user->phone)) {
                $account_sid = env('TWILIO_ACCOUNT_SID');
                $auth_token = env('TWILIO_AUTH_TOKEN');
                $twilio_number = "+13133563321";
                $client = new Client($account_sid, $auth_token);
                $phone = '+' . $user->country_code . $user->phone;
                $client->messages->create($phone, [
                    'from' => $twilio_number,
                    'body' => 'Your verification OTP is: ' . $otp . ' (valid for ' . self::OTP_EXPIRY_MINUTES . ' minutes)'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Twilio error: ' . $e->getMessage());
        }
    }

    /**
     * Send email OTP
     */
    public function sendEmailOtp($user, $otp)
    {
        try {
            if (!empty($user->email)) {
                $details = [
                    'otp' => $otp,
                    'user' => $user,
                    'minutes' => self::OTP_EXPIRY_MINUTES,
                    'title' => 'Tradabets Registration OTP',
                    'email_otp' => $otp
                ];
                Mail::to($user->email)->send(new OtpEmail($details));
            }
        } catch (\Exception $e) {
            \Log::error('Email sending error: ' . $e->getMessage());
        }
    }

    /**
     * Verify OTP and create user if valid
     */
    public function verifyOtp(Request $request)
    {
        try {
            \Log::debug('OTP Verification Request:', $request->all());

            $validator = Validator::make($request->all(), [
                'otp' => 'required|numeric|digits:6',
                'phone' => 'nullable|string',
                'email' => 'nullable|email'
            ]);

            if ($validator->fails() || (empty($request->phone) && empty($request->email))) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed. Phone or email is required.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $key = !empty($request->email) ? $request->email : $request->phone;
            $cacheKey = 'registration_' . $key;
            $registrationData = Cache::get($cacheKey);
            if (!$registrationData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration data expired or not found. Please register again.'
                ], 401);
            }

            $cachedOtp = Cache::get("otp_{$key}");
            \Log::debug('Stored OTP for key '.$key.': '.$cachedOtp);

            if (!$cachedOtp) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP expired. Please request a new one.'
                ], 401);
            }

            if ($cachedOtp != $request->otp) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP code'
                ], 401);
            }

            // OTP is valid, create user if not already created
            $existingUser = User::where('phone', $registrationData['phone'])
                ->orWhere(function($q) use ($registrationData) {
                    if (!empty($registrationData['email'])) {
                        $q->where('email', $registrationData['email']);
                    }
                })->first();
            if ($existingUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User already exists or already verified.'
                ], 409);
            }

            $user = User::create([
                'first_name' => $registrationData['first_name'],
                'last_name' => $registrationData['last_name'],
                'email' => $registrationData['email'],
                'phone' => $registrationData['phone'],
                'country_code' => $registrationData['country_code'],
                'country' => $registrationData['country'],
                'state' => $registrationData['state'],
                'city' => $registrationData['city'],
                'date_of_birth' => $registrationData['date_of_birth'] ?? null,
                'password' => $registrationData['password'],
                'token' => BaseHelper::generateToken(),
                'is_verified' => true,
            ]);

            // Create initial balance and transaction
            Balance::create([
                'user_id' => $user->id,
                'balance' => Config::get('constants.default_bonus', 0),
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'status' => 'bonus',
                'amount' => Config::get('constants.default_bonus', 0),
                'opening_balance' => 0,
                'closing_balance' => Config::get('constants.default_bonus', 0),
                'remarks' => 'Registration Bonus',
            ]);

            // Clean up cache
            Cache::forget($cacheKey);
            Cache::forget("otp_{$key}");

            return response()->json([
                'status' => 'success',
                'message' => 'Account verified and created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('OTP Verification Exception: '.$e->getMessage()."\n".$e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => config('app.debug') ? $e->getMessage() : 'Verification failed',
                'exception' => config('app.debug') ? get_class($e) : null
            ], 500);
        }
    }

    /**
     * Check if email exists
     */
    public function emailCheck($email)
    {
        $exists = User::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Check if phone exists
     */
    public function phoneCheck($phone)
    {
        $exists = User::where('phone', $phone)->exists();
        return response()->json(['exists' => $exists]);
    }
}
<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\User; // Assuming App\Models\User if you are using Laravel 8+ with default structure
use App\Helpers\BaseHelper; // Only if used elsewhere, not directly for JWT token generation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Important: Use Laravel's Auth facade
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth; // Import the JWTAuth facade

class LoginApiController extends Controller
{
    // JWT token expiration is managed in config/jwt.php, not here.
    // protected $tokenExpiryMinutes = 120; // REMOVED as it's not used with JWT

    /**
     * Authenticate user and return JWT token (XML/JSON response)
     */
    public function login(Request $request)
    {
        try {
            $contentType = $request->header('Content-Type');
            $credentials = [];
            $responseFormat = 'json'; // Default to JSON for error responses unless XML is clearly preferred

            if (strpos($contentType, 'application/xml') !== false) {
                $content = str_replace('&', '&amp;', $request->getContent());
                Log::info('Raw XML Content for Login: ' . $content);
                $xml = new \SimpleXMLElement($content, LIBXML_PARSEHUGE);

                $credentials = [
                    'email' => (string)$xml->Credentials->email,
                    'password' => (string)$xml->Credentials->password
                ];
                $responseFormat = 'xml';
            } elseif (strpos($contentType, 'application/json') !== false) {
                $credentials = $request->only(['email', 'password']);
                $responseFormat = 'json';
            } else {
                return $this->formatResponse([
                    'status' => 'FAIL',
                    'message' => 'Unsupported Content-Type'
                ], 400, $responseFormat);
            }
        } catch (\Exception $e) {
            Log::error('Login Parsing Error: ' . $e->getMessage(), ['request_content' => $request->getContent()]);
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid request format'
            ], 400, $responseFormat);
        }

        $validator = Validator::make($credentials, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Validation error',
                'errors' => $validator->errors()->toArray()
            ], 422, $responseFormat);
        }

        // Attempt to authenticate and generate JWT token
        // Auth::attempt() uses the 'api' guard configured in config/auth.php
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            Log::warning('Login failed for email: ' . $credentials['email'] . '. Invalid credentials.');
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid credentials'
            ], 401, $responseFormat);
        }

        // Retrieve the authenticated user
        $user = Auth::guard('api')->user();

        // With JWT, the token is not stored in the user model or cache for authentication.
        // It's self-contained and validated cryptographically.
        // The old token refreshing logic for the database/cache is removed.

        Log::info('Login successful for user ID: ' . $user->id);

        return $this->formatResponse([
            'status' => 'SUCCESS',
            'message' => 'Login successful',
            'Auth' => [
                'token' => $token, // Return the generated JWT
                'user_id' => $user->id // Retaining user_id/playerGuid as per original response structure
            ]
        ], 200, $responseFormat);
    }

    /**
     * Verify OTP and login with JWT (XML/JSON response)
     */
    public function verifyOtp(Request $request)
    {
        $contentType = $request->header('Content-Type');
        $emailOrPhone = null;
        $otp = null;
        $otpType = null;
        $responseFormat = 'xml'; // Default to XML as per original

        Log::info('----- OTP Verification Attempt -----');
        Log::info('Request Content-Type: ' . $contentType);

        try {
            if (str_contains($contentType, 'application/json')) {
                $data = json_decode($request->getContent(), true);
                Log::info('Raw JSON Content for Verify OTP: ' . $request->getContent());
                $emailOrPhone = $data['Credentials']['emailOrPhone'] ?? null;
                $otp = $data['Credentials']['otp'] ?? null;
                $otpType = $data['Credentials']['otpType'] ?? null;
                $responseFormat = 'json';
            } else { // Assuming XML if not JSON
                $content = str_replace('&', '&amp;', $request->getContent());
                Log::info('Raw XML Content for Verify OTP: ' . $content);
                $xml = new \SimpleXMLElement($content, LIBXML_PARSEHUGE);
                $emailOrPhone = (string)$xml->Credentials->emailOrPhone;
                $otp = (string)$xml->Credentials->otp;
                $otpType = isset($xml->Credentials->otpType) ? (string)$xml->Credentials->otpType : null;
                $responseFormat = 'xml';
            }
            Log::info('Parsed from request: ', ['emailOrPhone' => $emailOrPhone, 'otp' => $otp, 'otpType' => $otpType, 'responseFormat' => $responseFormat]);

        } catch (\Exception $e) {
            Log::error('Verify OTP Parsing Error: ' . $e->getMessage(), ['request_content' => $request->getContent()]);
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid request format'
            ], 400, $responseFormat);
        }

        if (!$emailOrPhone || !$otp) {
            Log::warning('Validation Fail: emailOrPhone or OTP missing.', ['emailOrPhone' => $emailOrPhone, 'otp' => $otp]);
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'emailOrPhone and OTP are required'
            ], 422, $responseFormat);
        }

        $user = User::where('email', $emailOrPhone)
            ->orWhere('phone', $emailOrPhone)
            ->first();

        if (!$user) {
            Log::warning('User not found for: ' . $emailOrPhone);
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'User not found'
            ], 404, $responseFormat);
        } else {
            Log::info('User found: ID ' . $user->id . ' for identifier: ' . $emailOrPhone);
            // Log::info('DB phone_otp: ' . $user->phone_otp); // Not safe to log actual OTP in production
            // Log::info('DB email_otp: ' . $user->email_otp); // Not safe to log actual OTP in production
            Log::info('Cache phone_otp exists: ' . (Cache::has('phone_otp_' . $user->id) ? 'YES' : 'NO'));
            Log::info('Cache email_otp exists: ' . (Cache::has('email_otp_' . $user->id) ? 'YES' : 'NO'));
        }

        $validOtp = false;
        $inputOtpString = (string)$otp;

        // Perform OTP validation from DB or Cache
        if ($otpType === 'phone' || !$otpType) { // If otpType is phone or not specified, check phone OTP
            $dbPhoneOtp = (string)$user->phone_otp;
            $cachePhoneOtp = (string)Cache::get('phone_otp_' . $user->id);
            if (($dbPhoneOtp !== '' && $dbPhoneOtp === $inputOtpString) || ($cachePhoneOtp !== '' && $cachePhoneOtp === $inputOtpString)) {
                $validOtp = true;
                Log::info('Phone OTP matched for user ID: ' . $user->id);
            }
        }
        if (!$validOtp && ($otpType === 'email' || !$otpType)) { // If otpType is email or not specified, check email OTP
            $dbEmailOtp = (string)$user->email_otp;
            $cacheEmailOtp = (string)Cache::get('email_otp_' . $user->id);
            if (($dbEmailOtp !== '' && $dbEmailOtp === $inputOtpString) || ($cacheEmailOtp !== '' && $cacheEmailOtp === $inputOtpString)) {
                $validOtp = true;
                Log::info('Email OTP matched for user ID: ' . $user->id);
            }
        }

        if (!$validOtp) {
            Log::warning('OTP validation failed for user ID: ' . $user->id . '. Input OTP: "' . $inputOtpString . '", Type: "' . ($otpType ?? 'any') . '"');
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid OTP'
            ], 401, $responseFormat);
        }

        // Clear OTPs after successful verification for security
        Log::info('OTP verified. Clearing OTPs for user ID: ' . $user->id);
        $user->phone_otp = null;
        $user->email_otp = null;
        $user->save();
        Cache::forget('phone_otp_' . $user->id); // Explicitly clear from cache
        Cache::forget('email_otp_' . $user->id); // Explicitly clear from cache

        // Generate JWT after successful OTP verification
        // This will log the user in and return a new JWT
        $token = Auth::guard('api')->login($user);

        Log::info('----- OTP Verification Successful -----');
        return $this->formatResponse([
            'status' => 'SUCCESS',
            'message' => 'OTP verified successfully',
            'Auth' => [
                'token' => $token, // Return the new JWT
                'playerGuid' => $user->id // Retaining playerGuid as per original response structure
            ]
        ], 200, $responseFormat);
    }

    /**
     * Logout user (invalidate JWT token)
     */
    public function logout(Request $request)
    {
        $responseFormat = 'json'; // Assuming JSON for logout requests in this context, or derive from Accept header

        try {
            // Get the token from the request, Auth::guard('api')->logout() typically
            // infers it from the Authorization header.
            // If the token is already invalid/expired, this will throw an exception.
            Auth::guard('api')->logout(); // This invalidates the current JWT

            Log::info('User logged out (JWT invalidated successfully).');

            return $this->formatResponse([
                'status' => 'SUCCESS',
                'message' => 'Logged out successfully'
            ], 200, $responseFormat);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            Log::warning('Logout attempt with an expired token: ' . $e->getMessage());
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Token has already expired' // Client should handle refreshing before logout, or just acknowledge
            ], 401, $responseFormat);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            Log::warning('Logout attempt with an invalid token: ' . $e->getMessage());
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid token provided'
            ], 401, $responseFormat);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('JWT Logout Error (general JWT exception): ' . $e->getMessage());
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Could not process logout, token error'
            ], 500, $responseFormat);
        } catch (\Exception $e) {
            Log::error('General Logout Error: ' . $e->getMessage());
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'An unexpected error occurred during logout'
            ], 500, $responseFormat);
        }
    }

    /**
     * Send OTP to mobile and email
     */
    public function sendOtp(Request $request)
    {
        $contentType = $request->header('Content-Type');
        $emailOrPhone = null;
        $responseFormat = 'xml'; // default

        Log::info('----- Send OTP Attempt -----');
        Log::info('Request Content-Type for Send OTP: ' . $contentType);

        try {
            if (str_contains($contentType, 'application/json')) {
                $data = json_decode($request->getContent(), true);
                Log::info('Raw JSON Content for Send OTP: ' . $request->getContent());
                $emailOrPhone = $data['Credentials']['emailOrPhone'] ?? null;
                $responseFormat = 'json';
            } else {
                $content = str_replace('&', '&amp;', $request->getContent());
                Log::info('Raw XML Content for Send OTP: ' . $content);
                $xml = new \SimpleXMLElement($content, LIBXML_PARSEHUGE);
                $emailOrPhone = (string)$xml->Credentials->emailOrPhone;
                $responseFormat = 'xml';
            }
            Log::info('Parsed for Send OTP: ', ['emailOrPhone' => $emailOrPhone, 'responseFormat' => $responseFormat]);

        } catch (\Exception $e) {
            Log::error('Send OTP Parsing Error: ' . $e->getMessage());
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'Invalid request format'
            ], 400, $responseFormat);
        }

        if (!$emailOrPhone) {
            Log::warning('Send OTP: emailOrPhone is required.');
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'emailOrPhone is required'
            ], 422, $responseFormat);
        }

        $user = User::where('email', $emailOrPhone)
            ->orWhere('phone', $emailOrPhone)
            ->first();

        if (!$user) {
            Log::warning('Send OTP: User not found for ' . $emailOrPhone);
            return $this->formatResponse([
                'status' => 'FAIL',
                'message' => 'User not found'
            ], 404, $responseFormat);
        }
        Log::info('Send OTP: User found ID ' . $user->id . ' (Email: ' . ($user->email ?? 'N/A') . ', Phone: ' . ($user->phone ?? 'N/A') . ')');

        // Generate OTPs
        $phoneOtp = rand(100000, 999999);
        $emailOtp = rand(100000, 999999);

        // Store OTPs in DB columns (temporarily)
        $user->phone_otp = (string) $phoneOtp;
        $user->email_otp = (string) $emailOtp;
        $user->save();
        Log::info('Send OTP: Saved to DB for User ID ' . $user->id); // Avoid logging actual OTP values

        // Store in cache with expiry (e.g., 5 minutes)
        Cache::put('phone_otp_' . $user->id, $phoneOtp, now()->addMinutes(5));
        Cache::put('email_otp_' . $user->id, $emailOtp, now()->addMinutes(5));
        Log::info('Send OTP: Stored OTPs in Cache for User ID ' . $user->id);


        // Send SMS OTP
        if ($user->phone && $user->country_code) {
            try {
                $account_sid = env('TWILIO_ACCOUNT_SID');
                $auth_token = env('TWILIO_AUTH_TOKEN');
                $twilio_number = env('TWILIO_PHONE_NUMBER', "+13133563321");

                if($account_sid && $auth_token && $twilio_number) {
                    $client = new Client($account_sid, $auth_token);
                    $phoneTo = $user->phone;
                    if (strpos($phoneTo, '+') !== 0 && $user->country_code) { // Ensure country code if not already E.164
                           $phoneTo = '+' . $user->country_code . $user->phone;
                    }

                    $client->messages->create($phoneTo, [
                        'from' => $twilio_number,
                        'body' => 'Your OTP is: ' . $phoneOtp // Send actual OTP
                    ]);
                    Log::info('Send OTP: SMS sent to ' . $phoneTo . ' for User ID ' . $user->id);
                } else {
                    Log::warning('Send OTP: Twilio service not fully configured (missing env vars). SMS not sent.');
                }
            } catch (\Exception $e) {
                Log::error('Send OTP: Twilio error for User ID ' . $user->id . ': ' . $e->getMessage());
            }
        } else {
            Log::warning('Send OTP: User ID ' . $user->id . ' missing phone or country_code. SMS not sent.');
        }

        // Send Email OTP
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new OtpEmail([
                    'title' => 'Login OTP',
                    'otp' => $emailOtp // Send actual OTP
                ]));
                Log::info('Send OTP: Email sent to ' . $user->email . ' for User ID ' . $user->id);
            } catch (\Exception $e) {
                Log::error('Send OTP: Email error for User ID ' . $user->id . ': ' . $e->getMessage());
            }
        } else {
             Log::warning('Send OTP: User ID ' . $user->id . ' missing email. Email OTP not sent.');
        }

        // Conditionally include OTPs in response for debug environments only.
        $otpDetailsForResponse = [];
        if (config('app.debug')) {
            $otpDetailsForResponse = [
                'phoneOtp_sent' => $phoneOtp,
                'emailOtp_sent' => $emailOtp
            ];
        }

        Log::info('----- Send OTP Successful -----');
        return $this->formatResponse([
            'status' => 'SUCCESS',
            'message' => 'OTP sent successfully',
            'otpDetails' => $otpDetailsForResponse
        ], 200, $responseFormat);
    }

    /**
     * Generate XML response (PHP 7.4 compatible)
     */
    private function xmlResponse(array $data, $status = 200)
    {
        $xml = new \SimpleXMLElement('<Response/>');
        $this->arrayToXml($data, $xml);

        return response($xml->asXML(), $status)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Convert array to XML (PHP 7.4 compatible)
     */
    private function arrayToXml(array $data, \SimpleXMLElement $xml)
    {
        foreach ($data as $key => $value) {
            // Ensure key is a valid XML tag name
            $key = preg_replace('/[^a-zA-Z0-9_\-]/', '_', is_numeric($key) ? 'item' . $key : $key);

            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                // htmlspecialchars to prevent XML injection issues
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }

    /**
     * Format response as XML or JSON based on requested format.
     */
    private function formatResponse(array $data, int $status = 200, string $format = 'xml')
    {
        if ($format === 'json') {
            return response()->json($data, $status);
        } else {
            return $this->xmlResponse($data, $status);
        }
    }
}
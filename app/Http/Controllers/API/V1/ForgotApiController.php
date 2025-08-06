<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordOTP;
use Carbon\Carbon;

class ForgotApiController extends Controller
{
    const OTP_EXPIRY_MINUTES = 5;

    /**
     * Send email OTP for forgot password
     */
    public function sendEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        $otp = rand(100000, 999999);

        // Store OTP in cache
        Cache::put("forgot_otp_email_{$user->id}", $otp, now()->addMinutes(self::OTP_EXPIRY_MINUTES));

        // Prepare email content
        $content = "<h1>Tradabets Forgot Password OTP:</h1><p>{$otp}</p>";
        $subject = "Tradabets Forgot Password OTP (valid for " . self::OTP_EXPIRY_MINUTES . " minutes)";
        $from_email = "noreply@tradabets.com"; // Ensure this email is verified in Brevo or your SMTP provider
        $from_name = "Tradabets";

        // Send email using Laravel Mailable
        Mail::to($user->email, $user->first_name . ' ' . $user->last_name)
            ->send(new ForgotPasswordOTP($content, $subject, $from_email, $from_name));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent to your email.',
            'otp_expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES)->toDateTimeString(),
            'debug_otp' => $otp // Remove in production
        ]);
    }

    /**
     * Verify OTP and send password reset link
     */
    public function verifyEmailOtpAndSendLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user,email',
            'otp' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $cachedOtp = Cache::get("forgot_otp_email_{$user->id}");

        if (!$cachedOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP expired or not found.'
            ], 401);
        }

        if ($cachedOtp != $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP.'
            ], 401);
        }

        // Clear OTP from cache
        Cache::forget("forgot_otp_email_{$user->id}");

        // Generate password reset link
        $token = Password::broker()->createToken($user);
        $resetLink = url("/password/reset/{$token}?email=" . urlencode($user->email));

        // Prepare email content
        $content = "<h1>Password Reset Request</h1>";
        $content .= "<p>Click the link below to reset your password:</p>";
        $content .= "<a href='{$resetLink}'>{$resetLink}</a>";
        $subject = "Password Reset Request";
        $from_email = "noreply@tradabets.com";
        $from_name = "Tradabets Support";

        // Send email using Laravel Mailable
        try {
            Mail::to($user->email, $user->first_name . ' ' . $user->last_name)
                ->send(new ForgotPasswordOTP($content, $subject, $from_email, $from_name));

            $responseData = [
                'status' => 'success',
                'message' => 'Password reset link sent to your email.',
            ];

            if (config('app.debug')) {
                $responseData['debug_token'] = $token;
                $responseData['debug_reset_link'] = $resetLink;
            }
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Mail send error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send password reset link.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

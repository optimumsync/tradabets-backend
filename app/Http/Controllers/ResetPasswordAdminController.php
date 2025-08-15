<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail; // Add this line
use App\Mail\ForgotPasswordOTP;     // Add this line

class ResetPasswordAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Send a password reset link to the given user, initiated by an admin.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLink(User $user)
    {
        try {
            // Manually create a password reset token for the user.
            // This will also respect the throttling defined in your auth config.
            $token = Password::broker()->createToken($user);

            // Create the full password reset link.
            $resetLink = url("/password/reset/{$token}?email=" . urlencode($user->email));

            // Prepare the exact email content as requested.
            // A line is added to inform the user this was initiated by an admin.
            $content = "<h1>Password Reset Request</h1>";
            $content .= "<p>You are receiving this email because an administrator has requested a password reset for your account.</p>";
            $content .= "<p>Click the link below to reset your password:</p>";
            $content .= "<a href='{$resetLink}'>{$resetLink}</a>";
            $content .= "<p>If you did not request a password reset from an admin, no further action is required.</p>";
            
            $subject = "Password Reset Request";
            $from_email = "noreply@tradabets.com";
            $from_name = "Tradabets Support";

            // Send the email using your existing ForgotPasswordOTP Mailable.
            Mail::to($user->email, $user->first_name . ' ' . $user->last_name)
                ->send(new ForgotPasswordOTP($content, $subject, $from_email, $from_name));

            return redirect()->back()->with('success', 'Password reset link sent successfully to ' . $user->email);

        } catch (\Exception $e) {
            \Log::error('Admin Password Reset Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Could not send reset link. Please check the application logs.');
        }
    }
}
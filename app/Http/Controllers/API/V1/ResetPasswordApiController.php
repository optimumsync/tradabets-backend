<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordApiController extends Controller
{
    public function __construct()
    {
        // Ensure only guests can access
        $this->middleware('guest');
    }

    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response === Password::PASSWORD_RESET
            ? $this->sendResetResponse($response)
            : $this->sendResetFailedResponse($response);
    }

    protected function rules()
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required', 'string', 'min:6', 'max:50',
                'required_with:password_confirmation', 'same:password_confirmation'
            ],
            'password_confirmation' => ['required', 'string', 'min:6', 'max:50'],
        ];
    }

    protected function validationErrorMessages()
    {
        return [];
    }

    protected function credentials(Request $request)
    {
        return $request->only('email', 'password', 'password_confirmation', 'token');
    }

    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        // Do not auto-login; the user can login manually with their new password
    }

    protected function sendResetResponse($response)
    {
        return response()->json([
            'status' => 'success',
            'message' => trans($response),
        ], 200);
    }

    protected function sendResetFailedResponse($response)
    {
        return response()->json([
            'status' => 'error',
            'message' => trans($response),
        ], 422);
    }

    public function broker()
    {
        return Password::broker(); // or Password::broker('users') if you're using custom guards
    }
}

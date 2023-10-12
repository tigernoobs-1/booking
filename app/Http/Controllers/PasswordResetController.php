<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{

    public function sendEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        //dd($user->is_registered);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->is_registered == true) {
            $response = Password::sendResetLink($request->only('email'));

            return $response == Password::RESET_LINK_SENT
                ? response()->json(['message' => 'Reset link sent to your email.', 'status' => true], 200)
                : response()->json(['message' => 'Unable to send reset link', 'status' => false], 400);
        } else {
            return response()->json(['message' => 'Reset Password Only For Registered Account'], 404);
        }
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user && $user->is_registered == true) {
            $response = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password)
                ])->save();
                $user->tokens()->delete();

                event(new PasswordReset($user));
            });

            return $response == Password::PASSWORD_RESET
                ? response()->json(['message' => 'Password has been reset.', 'status' => true], 200)
                : response()->json(['message' => 'Password reset failed', 'status' => false], 400);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}

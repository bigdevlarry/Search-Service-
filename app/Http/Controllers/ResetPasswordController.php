<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

        $credentials = $request->only('email', 'password', 'token');

        /** @note attempt to reset the password, if successful return success message */
        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        return $response == Password::PASSWORD_RESET
            ? response()->json(['status' => 'success', 'message' => 'Password has been reset'], 200)
            : response()->json(['error' => 'Failed to reset password'], 400);
    }
}

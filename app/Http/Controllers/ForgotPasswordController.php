<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $user = User::where('email', $email)->first();

        if ($user) { // send reset to only registered users
            $response = Password::sendResetLink(['email' => $email]);
            return $response == Password::RESET_LINK_SENT
                ? response()->json(['status' => 'success', 'message' => 'Password reset link sent'], 200)
                : response()->json(['error' => 'Unable to send reset link'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'Password reset link sent'], 200);
    }
}

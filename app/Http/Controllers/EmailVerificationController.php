<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }
        $request->fulfill();
        return response()->json(['message' => 'Email verified']);
    }

    public function resend(Request $request): JsonResponse
    {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent']);
    }
}

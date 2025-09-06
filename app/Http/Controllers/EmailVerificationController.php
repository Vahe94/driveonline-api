<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->noContent(400);
        }
        $request->fulfill();
        return response()->noContent(204);
    }

    public function resend(Request $request): Response
    {
        $request->user()->sendEmailVerificationNotification();
        return response()->noContent(204);
    }
}

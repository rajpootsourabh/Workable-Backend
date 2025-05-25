<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SimpleMailController extends Controller
{
    /**
     * Send a simple plain text email without template.
     */
    public function sendSimpleEmail(Request $request)
    {
        $validated = $request->validate([
            'toEmail' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        Mail::raw($validated['message'], function ($message) use ($validated) {
            $message->to($validated['toEmail'])
                    ->subject($validated['subject']);
        });

        return response()->json(['message' => 'Email sent successfully']);
    }
}

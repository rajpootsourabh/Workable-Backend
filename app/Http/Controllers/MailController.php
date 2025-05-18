<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\EmployeeNotificationMail;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendEmployeeEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'temp_password' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'temp_password' => $request->temp_password,
            'it_support_email' => 'itsupport@bipani.co',
            'sender_name' => 'Jane Smith',
            'sender_position' => 'HR Manager',
            'company_name' => 'Bipani',
            'contact_info' => 'contact@bipani.co | +91-1234567890',
        ];

        Mail::to($data['email'])->send(new EmployeeNotificationMail($data));

        return response()->json(['message' => 'Email sent successfully']);
    }
}

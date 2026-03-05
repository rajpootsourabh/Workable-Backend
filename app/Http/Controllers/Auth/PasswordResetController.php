<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'app_type' => 'sometimes|in:admin,main' // Optional: specify which frontend
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'We couldn’t find a user with that email.'], 404);
        }

        // Determine which frontend URL to use
        $frontendUrl = $this->getFrontendUrl($request, $user);

        // Generate token
        $token = Str::random(64);

        // Store token in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Generate reset URL with the appropriate frontend
        $resetUrl = $frontendUrl . "/reset-password?token={$token}&email={$user->email}";

        // Send email
        Mail::to($user->email)->send(new ResetPasswordMail($user->first_name ?? 'User', $resetUrl));

        return response()->json(['message' => 'Password reset link sent successfully.']);
    }

    /**
     * Determine which frontend URL to use
     */
    private function getFrontendUrl(Request $request, User $user): string
    {
        // Option 1: Based on request input
        if ($request->has('app_type')) {
            return $request->app_type === 'admin'
                ? env('ADMIN_FRONTEND_URL', 'https://admin.hustoro.com')
                : env('FRONTEND_URL', 'https://hustoro.com');
        }

        // Option 2: Based on user role (if admins should go to admin panel)
        if ($user->role == 1) { // Admin role
            return env('ADMIN_FRONTEND_URL', 'https://admin.hustoro.com');
        }

        // Option 3: Based on referer header or origin
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if ($origin) {
            if (str_contains($origin, 'admin.hustoro.com')) {
                return env('ADMIN_FRONTEND_URL', 'https://admin.hustoro.com');
            }
            if (str_contains($origin, 'hustoro.com')) {
                return env('FRONTEND_URL', 'https://hustoro.com');
            }
        }

        // Default to main frontend
        return env('FRONTEND_URL', 'https://hustoro.com');
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json([
                'message' => 'This reset link is invalid or has already been used.'
            ], 400);
        }

        // Optional: Check token expiration (assuming you store created_at)
        $expiresInMinutes = 60;
        if (now()->diffInMinutes($record->created_at) > $expiresInMinutes) {
            return response()->json(['message' => 'This reset link has expired. Please request a new password reset.'], 400);
        }

        // Validate token hash
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid reset token.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete token so it can't be reused
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}

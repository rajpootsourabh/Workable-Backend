<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function getProfile()
    {
        $user = Auth::user();

        return response()->json([
            'user' => [
                'email' => $user->email,
            ]
        ]);
    }


    public function updateCredentials(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'old_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Check old password if new password is set
        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                throw ValidationException::withMessages([
                    'old_password' => ['The current password is incorrect.'],
                ]);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json(['message' => 'Credentials updated successfully.']);
    }

    // public function updateProfile(Request $request)
    // {
    //     $request->validate([
    //         'first_name' => 'required|string|max:50',
    //         'last_name' => 'required|string|max:50',
    //         'job_title' => 'nullable|string|max:100',
    //         'email' => 'required|email|unique:users,email,' . Auth::id(),
    //         'time_zone' => 'nullable|string',
    //     ]);

    //     $user = Auth::user();

    //     $user->update($request->only([
    //         'first_name',
    //         'last_name',
    //         'email',

    //     ]));

    //     return response()->json(['message' => 'Profile updated successfully']);
    // }

    // public function uploadProfilePicture(Request $request)
    // {
    //     $request->validate([
    //         'profile_picture' => 'required|image|max:3072',
    //     ]);

    //     $user = Auth::user();
    //     $path = $request->file('profile_picture')->store('profile_pictures', 'public');

    //     $user->profile_picture = '/storage/' . $path;
    //     $user->save();

    //     return response()->json([
    //         'message' => 'Profile picture updated successfully',
    //         'profile_picture' => $user->profile_picture
    //     ]);
    // }
}

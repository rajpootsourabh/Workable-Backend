<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'experience' => 'nullable|numeric|min:0|max:99.9',
            'phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'profile_pic' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'source_id' => 'required|uuid',
        ]);

        $validated['id'] = Str::uuid();

        // Handle file uploads
        if ($request->hasFile('profile_pic')) {
            $validated['profile_pic_url'] = $request->file('profile_pic')->store('candidates/profile_pics', 'public');
        }

        if ($request->hasFile('resume')) {
            $validated['resume_url'] = $request->file('resume')->store('candidates/resumes', 'public');
        }

        $candidate = Candidate::create($validated);

        return response()->json([
            'message' => 'Candidate created successfully',
            'data' => $candidate
        ], 201);
    }
}

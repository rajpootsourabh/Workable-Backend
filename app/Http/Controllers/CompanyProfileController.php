<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) return null;
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        $companyData = $company->toArray();
        $companyData['company_logo'] = $generateFileUrl($company->company_logo);

        return response()->json([
            'company' => $companyData
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'required|url',
            'size' => 'required|integer',
            'phone_number' => 'required|string|max:50',
            'evaluating_website' => 'nullable|string',
            'company_description' => 'nullable|string',
            'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ✅ Handle logo file if uploaded
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->company_logo && Storage::disk('private')->exists($company->company_logo)) {
                Storage::disk('private')->delete($company->company_logo);
            }

            // ✅ Clean company name for filename
            $companyName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $company->name);
            $extension = $request->file('logo')->extension();
            $filename = strtolower($companyName) . '_' . time() . '.' . $extension;

            // ✅ Store logo in private storage
            $company->company_logo = $request->file('logo')->storeAs(
                'companies/logos',
                $filename,
                'private'
            );
        }

        // ✅ Update company details
        $company->name = $validated['name'];
        $company->website = $validated['website'];
        $company->size = $validated['size'];
        $company->phone_number = $validated['phone_number'];
        $company->evaluating_website = $validated['evaluating_website'] ?? null;
        $company->company_description = $validated['company_description'] ?? null;
        $company->save();

        // ✅ Generate a public-access proxy URL
        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) return null;
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        return response()->json([
            'message' => 'Company updated successfully.',
            'company' => [
                ...$company->toArray(),
                'company_logo' => $generateFileUrl($company->company_logo),
            ]
        ]);
    }


    public function uploadLogo(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['message' => 'Company not found.'], 404);
        }

        $validated = $request->validate([
            'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old logo if exists
        if ($company->company_logo && Storage::disk('private')->exists($company->company_logo)) {
            Storage::disk('private')->delete($company->company_logo);
        }

        // ✅ Sanitize company name for filename
        $companyName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $company->name);
        $extension = $request->file('logo')->extension();
        $filename = strtolower($companyName) . '_' . time() . '.' . $extension;

        // ✅ Store logo
        $path = $request->file('logo')->storeAs(
            'companies/logos',
            $filename,
            'private'
        );

        // ✅ Save to DB
        $company->company_logo = $path;
        $company->save();

        // ✅ Generate File URL
        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) return null;
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        return response()->json([
            'message' => 'Logo uploaded successfully.',
            'company_logo' => $generateFileUrl($path),
        ]);
    }
}

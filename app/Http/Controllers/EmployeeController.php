<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\JobDetail;
use App\Models\CompensationDetail;
use App\Models\LegalDocument;
use App\Models\ExperienceDetail;
use App\Models\EmergencyContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    // Store all employee details (Personal, Job, Compensation, Legal, Experience, Emergency) in one API
    public function storeCompleteEmployee(Request $request)
    {
        DB::beginTransaction();
        // Log::info('Incoming employee data:', $request->all());

        try {
            // Convert all the incoming camelCase data into snake_case
            $requestData = $this->camelToSnake($request->all());

            // Step 1: Personal Details
            $personalData = $requestData['personal'] ?? [];
            $validatedEmployee = Validator::make($personalData, [
                'first_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'last_name' => 'required|string',
                'preferred_name' => 'nullable|string',
                'country' => 'nullable|string',
                'address' => 'nullable|string',
                'gender' => 'nullable|string|in:Male,Female,Others',
                'birthdate' => 'nullable|date',
                'marital_status' => 'required|string|in:Single,Married,Common Law,Domestic Partnership',
                'phone' => 'nullable|string',
                'work_email' => 'nullable|email',
                'personal_email' => 'nullable|email',
                'chat_video_call' => 'nullable|string',
                'social_media' => 'nullable|string',
                'profile_image' => 'nullable|file|image|max:2048',
            ])->validate();

            if ($request->hasFile('personal.profileImage')) {
                // Use store() method which will automatically move the file from the temporary location
                $validatedEmployee['profile_image'] = $request->file('personal.profileImage')->store('private/profiles', 'local');
            }


            $employee = Employee::create($validatedEmployee);

            // Step 2: Job Details
            $jobData = $requestData['job'] ?? [];
            $validatedJob = Validator::make($jobData, [
                'job_title' => 'required|string',
                'hire_date' => 'nullable|date',
                'start_date' => 'required|date',
                'entity' => 'nullable|string',
                'department' => 'nullable|string',
                'effective_date' => 'required|date',
                'employment_type' => 'required|string|in:Contractor,Full-Time,Part-Time',
                'workplace' => 'nullable|string|in:Onsite,Remote,Hybrid',
                'expiry_date' => 'nullable|date',
                'manager' => 'nullable|string',
                'work_schedule' => 'nullable|string',
                'note' => 'nullable|string'
            ])->validate();

            $employee->jobDetail()->create($validatedJob);

            // Step 3: Compensation Details
            $compData = $requestData['compensation_benefits'] ?? [];
            $validatedComp = Validator::make($compData, [
                'salary_details' => 'nullable|string',
                'bank_name' => 'required|string',
                'iban' => 'required|string',
                'account_number' => 'nullable|string',
            ])->validate();

            $employee->compensationDetail()->create($validatedComp);

            // Step 4: Legal Documents
            $legalData = $requestData['legal_documents'] ?? [];

            $validatedLegal = Validator::make($legalData, [
                'social_security_number' => 'required|string',
                'national_id' => 'required|string',
                'nationality' => 'nullable|string',
                'citizenship' => 'nullable|string',
                'passport' => 'nullable|string',
                'work_visa' => 'nullable|string',
                'visa_details' => 'nullable|string',
                'issue_date_national_id' => 'nullable|date',
                'issue_date_tax_id' => 'nullable|date',
                'issue_date_s_s_n' => 'nullable|date',
                'tax_id' => 'required|string',
                'social_insurance_number' => 'nullable|string',
                'ssn_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'national_id_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
                'tax_id_file' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            ])->validate();

            // Log::info('Validated legal data:', $validatedLegal);

            // Store files if present
            if ($request->hasFile('legalDocuments.ssnFile')) {
                $validatedLegal['ssn_file'] = $request->file('legalDocuments.ssnFile')->store('private/legal_docs', 'local');
            }
            if ($request->hasFile('legalDocuments.nationalIdFile')) {
                $validatedLegal['national_id_file'] = $request->file('legalDocuments.nationalIdFile')->store('private/legal_docs', 'local');
            }
            if ($request->hasFile('legalDocuments.taxIdFile')) {
                $validatedLegal['tax_id_file'] = $request->file('legalDocuments.taxIdFile')->store('private/legal_docs', 'local');
            }

            // Create related model entry
            $employee->legalDocument()->create($validatedLegal);


            // Step 5: Experience
            $expData = $requestData['experience'] ?? [];
            $validatedExperience = Validator::make($expData, [
                'skill' => 'nullable|string',
                'job' => 'nullable|string',
                'language' => 'nullable|string',
                'education' => 'nullable|string',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            ])->validate();

            // Log::info('Validated experience data:', $validatedExperience);


            if ($request->hasFile('experience.resume')) {
                $validatedExperience['resume'] = $request->file('experience.resume')->store('private/resumes', 'local');
            }

            $employee->experienceDetail()->create($validatedExperience);

            // Step 6: Emergency Contact
            $emergencyData = $requestData['emergency'] ?? [];
            $validatedEmergency = Validator::make($emergencyData, [
                'contact_name' => 'nullable|string',
                'contact_phone' => 'nullable|string',

            ])->validate();

            $employee->emergencyContact()->create($validatedEmergency);

            DB::commit();

            return response()->json([
                'message' => 'Employee and related information saved successfully!',
                'employee_id' => $employee->id
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();
            // Check for duplicate entry for the work_email field
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'employees_work_email_unique') !== false) {
                return response()->json([
                    'error' => 'The email address is already in use. Please provide a unique email address.'
                ], 422);
            }

            // For other exceptions
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    // Get by emplopyee id (specific-employee)
    public function showCompleteEmployee($id)
    {
        try {
            $employee = Employee::with([
                'jobDetail',
                'compensationDetail',
                'legalDocument',
                'experienceDetail',
                'emergencyContact'
            ])->findOrFail($id);

            return new EmployeeResource($employee);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Employee not found or an error occurred: ' . $e->getMessage()
            ], 404);
        }
    }

    public function listCompleteEmployees()
    {
        try {
            $employees = Employee::with([
                'jobDetail',
                'compensationDetail',
                'legalDocument',
                'experienceDetail',
                'emergencyContact'
            ])->get();

            return EmployeeResource::collection($employees);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to convert camelCase to snake_case
     */
    private function camelToSnake(array $data)
    {
        $converted = [];
        foreach ($data as $key => $value) {
            $key = Str::snake($key);
            if (is_array($value)) {
                $value = $this->camelToSnake($value);
            }
            $converted[$key] = $value;
        }
        return $converted;
    }
}

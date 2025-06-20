<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\CompanyWelcomeMail;
use App\Mail\EmployeeNotificationMail;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login','register','me']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => "error", 'message' => $validator->messages()], 400);
        }
        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * Register a User and create a new Company.
     */
    public function register(Request $request)
    {
        Log::info('Raw request body:', ['body' => $request->getContent()]);
        Log::info('Parsed request all():', $request->all());
        Log::info('Register request data:', $request->all());

        // Validation rules for user and company fields
        $validator = Validator::make($request->all(), [
            'companyName' => ['required', 'string', 'max:255'],
            'companyWebsite' => ['required', 'string'],
            'companySize' => ['nullable', 'integer'],
            'phoneNumber' => ['required', 'string', 'unique:companies,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'evaluatingWebsite' => ['required'],
            'role' => ['required'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => "error", 'message' => $validator->messages()], 400);
        }

        try {
            // Wrap DB operations in a transaction for rollback on failure
            DB::beginTransaction();

            // Create a new company record
            $company = Company::create([
                'name' => $request->companyName,
                'website' => $request->companyWebsite,
                'size' => $request->companySize,
                'phone_number' => $request->phoneNumber,
                'evaluating_website' => $request->evaluatingWebsite,
            ]);

            // Create the user and associate with company
            $user = User::create([
                'company_id' => $company->id,
                'first_name' => "N/A",
                'last_name' => "N/A",
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => 1,
                'password' => Hash::make($request->password),
            ]);

            DB::commit(); // Only now commit DB changes

            // Send welcome email only after successful DB commit

            $data = [
                'company_name' => $request->companyName,
                'email' => $user->email,
                'password' => $request->password,
                'role' => 'Admin',
            ];

            Mail::to($data['email'])->send(new CompanyWelcomeMail($data));

            return new UserResource($user);
        } catch (\Exception $exp) {
            DB::rollBack(); // Revert company/user creation on failure
            Log::error('Registration failed: ' . $exp->getMessage());
            return response()->json(['status' => "error", 'message' => $exp->getMessage()], 400);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth()->user();

        $response = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => new UserResource($user),
        ];

        // If role == 5, add employee_id to the response
        if ($user->role == 5) {
            $response['employee_id'] = $user->employee_id;
        }

        return response()->json($response);
    }




    /**
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'status' => "success"
        ]);
    }
}

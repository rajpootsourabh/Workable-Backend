<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
            // Create a new company record
            $company = Company::create([
                'name' => $request->companyName,
                'website' => $request->companyWebsite,
                'size' => $request->companySize,
                'phone_number' => $request->phoneNumber,
                'evaluating_website' => $request->evaluatingWebsite,
            ]);

            // Create the user record and associate it with the company
            $user = User::create([
                'company_id' => $company->id, // Store the company_id in the user table
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => 1,
                'password' => Hash::make($request->password),
            ]);

            return new UserResource($user);
        } catch (\Exception $exp) {
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
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
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

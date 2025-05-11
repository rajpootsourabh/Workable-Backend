<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\EmployeeController; 
use App\Http\Controllers\JobApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::group(['middleware' => 'api', 'prefix' => 'v.1'], function ($router) {

    // 🔐 Public routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::get('cacheclear', function () {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        return response()->json(['message' => 'Cache cleared successfully!']);
    });

    // 🔐 Protected routes (auth:api)
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('test', [AuthController::class, 'me']);

        // 📌 JobPost Routes
        Route::get('/job/list', [JobPostController::class, 'listJobs']);
        Route::get('/job/{id}', [JobPostController::class, 'getJob']);
        Route::post('job/create', [JobPostController::class, 'createJob']);
        Route::put('job/update/{id}', [JobPostController::class, 'updateJob']);
        Route::delete('job/delete/{id}', [JobPostController::class, 'deleteJob']);

        // 📌 Employee Multi-Step Form Routes
        Route::prefix('employee')->group(function () {
            Route::post('/', [EmployeeController::class, 'storeCompleteEmployee']); // POST /api/v.1/employee
            Route::put('/{id}', [EmployeeController::class, 'updateCompleteEmployee']); // PUT /api/v1/employee/{id}
            Route::get('/all', [EmployeeController::class, 'listAllEmployees']); // GET /api/v1/employee/all
            Route::get('{id}/details', [EmployeeController::class, 'getEmployeeDetailsById']); // GET /api/v.1/employee/{id}/complete
        });

        // 📌 Job Applications Routes
        Route::prefix('job-applications')->group(function () {
            Route::post('/', [JobApplicationController::class, 'applyForJob']); // Apply to a job (creates candidate + application)
            Route::get('/', [JobApplicationController::class, 'getApplications']); // Admin view of all applications
        });

        // // 📌 Candidates and Applications
        // Route::prefix('candidates')->group(function () {
        //     // Candidate routes
        //     Route::post('/', [CandidateController::class, 'store']);           // POST /api/v1/candidates
        //     Route::get('/all', [CandidateController::class, 'index']);         // GET /api/v1/candidates/all
        //     Route::get('/{id}', [CandidateController::class, 'show']);         // GET /api/v1/candidates/{id}
        //     Route::put('/{id}', [CandidateController::class, 'update']);       // PUT /api/v1/candidates/{id}
        //     Route::delete('/{id}', [CandidateController::class, 'destroy']);   // DELETE /api/v1/candidates/{id}

        //     // Applications nested under candidates
        //     Route::prefix('applications')->group(function () {
        //         Route::post('/apply', [CandidateApplicationController::class, 'apply']); // POST /api/v1/candidates/applications/apply
        //         Route::get('/', [CandidateApplicationController::class, 'index']);       // GET /api/v1/candidates/applications
        //     });
        // });
    });
});

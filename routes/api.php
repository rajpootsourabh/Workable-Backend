<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\EmployeeController; // ✅ Added
use App\Http\Controllers\FileController;

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
            Route::get('/all', [EmployeeController::class, 'listCompleteEmployees']); // GET /api/v1/employee/all
            Route::get('{id}/complete', [EmployeeController::class, 'showCompleteEmployee']);// GET /api/v.1/employee/{id}/complete
        });        

         // 📌 File Route for Serving Private Files
         Route::get('/file/{fileName}', [FileController::class, 'getFileByName']);

    });
});

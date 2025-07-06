<?php

use App\Http\Controllers\TimeOffRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateApplicationCommentController;
use App\Http\Controllers\CandidateApplicationCommunicationController;
use App\Http\Controllers\CandidateApplicationLogController;
use App\Http\Controllers\CandidateApplicationReviewController;
use App\Http\Controllers\CandidateApplicationStageController;
use App\Http\Controllers\CandidateAssignmentController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobApplicationStatsController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimpleMailController;
use App\Http\Controllers\UserProfileController;

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
            Route::get('/options', [EmployeeController::class, 'getEmployeeOptions']); // GET /api/v1/employee/options
            Route::get('{id}/details', [EmployeeController::class, 'getEmployeeDetailsById']); // GET /api/v.1/employee/{id}/complete
            Route::get('{employeeId}/assignments', [CandidateAssignmentController::class, 'getAssignedCandidatesForEmployee']);
            // Get all subordinates under a manager (employee)
            Route::get('{id}/subordinates', [EmployeeController::class, 'getSubordinates']); // GET /api/v1/employee/{id}/subordinates
        });

        // 📌 Job Applications Routes
        Route::prefix('job-applications')->group(function () {
            // 🔽 Core Application actions
            Route::post('/', [JobApplicationController::class, 'applyForJob']); // Apply to a job (creates candidate + application)
            Route::get('/', [JobApplicationController::class, 'getApplications']); // Admin view of all applications
            Route::get('/stats', [JobApplicationStatsController::class, 'getApplicationCountsByStage']);
            // 🔽 Filtered applications by job_post_id
            Route::get('/job/{jobPostId}', [JobApplicationController::class, 'getApplicationsForJob']);

            // ✅ Stage Pipeline APIs
            Route::post('/{applicationId}/next-stage', [CandidateApplicationStageController::class, 'moveToNextStage']);
            Route::post('/{applicationId}/set-stage', [CandidateApplicationStageController::class, 'setStage']);

            Route::post('/{applicationId}/disqualify', [JobApplicationController::class, 'disqualify']);
            Route::get('/{applicationId}', [JobApplicationController::class, 'getApplicationById']);

            // Only this PATCH route uses camel.to.snake middleware
            Route::patch('/{applicationId}', [JobApplicationController::class, 'updateCandidateApplication'])
                ->middleware('camel.to.snake');

            // 🗨️ Comments
            Route::post('/{applicationId}/comments', [CandidateApplicationCommentController::class, 'addComment'])
                ->middleware('camel.to.snake');
            Route::get('/{applicationId}/comments', [CandidateApplicationCommentController::class, 'listComments']);

            // ✉️ Communications
            Route::post('/communications', [CandidateApplicationCommunicationController::class, 'sendCommunication'])
                ->middleware('camel.to.snake');
            Route::get('/{applicationId}/communications', [CandidateApplicationCommunicationController::class, 'getCommunications']);

            // 📝 Reviews
            Route::post('/{applicationId}/reviews', [CandidateApplicationReviewController::class, 'addReview'])
                ->middleware('camel.to.snake');
            Route::get('/{applicationId}/reviews', [CandidateApplicationReviewController::class, 'getReviews']);

            // 🔁 Logs
            Route::post('/{applicationId}/log-stage-change', [CandidateApplicationLogController::class, 'logStageChange'])
                ->middleware('camel.to.snake');
            Route::get('/{applicationId}/logs', [CandidateApplicationLogController::class, 'getLogs']);
        });

        // 👥 Candidate Assignments
        Route::prefix('candidate')->group(function () {
            Route::get('/', [CandidateController::class, 'listCandidates']);
            // Assign a candidate to an employee
            Route::post('{candidateId}/assignments', [CandidateAssignmentController::class, 'assign']);
            // Unassign a candidate from an employee
            Route::delete('{candidateId}/assignments/{employeeId}', [CandidateAssignmentController::class, 'unassign']);
            // Get all employees assigned to a candidate
            Route::get('{candidateId}/assignments', [CandidateAssignmentController::class, 'showAssignments']);
        });

        // 🏢 Company Profile routes
        Route::prefix('company')->group(function () {
            Route::get('/', [CompanyProfileController::class, 'show']);
            Route::put('/', [CompanyProfileController::class, 'update']);
            Route::post('/logo', [CompanyProfileController::class, 'uploadLogo']);
        });

        // ✅ User Profile Routes
        Route::prefix('auth/profile')->group(function () {
            Route::get('/', [ProfileController::class, 'getProfile']);
            Route::put('/', [ProfileController::class, 'updateProfile']);
            Route::put('/credentials', [ProfileController::class, 'updateCredentials']);
            Route::post('/upload', [ProfileController::class, 'uploadProfilePicture']);
        });

        // 🗓️ Time Off Requests
        Route::prefix('time-off-requests')->controller(TimeOffRequestController::class)->group(function () {
            // Create a new time off request
            Route::post('/', 'submitTimeOffRequest');
            // Get upcoming approved time off for logged-in employee
            Route::get('/upcoming', 'getUpcomingTimeOff');
            Route::get('/all', 'getAllTimeOff');
            // Get leave balance for the logged-in employee
            Route::get('/leave-balance', 'getLeaveBalance');
            // Get requests by manager
            Route::get('/manager', 'getByManager');
            // Get requests by employee
            Route::get('/employee/{employeeId}', 'getByEmployeeId');
            // Approve/Reject time off request
            Route::patch('/{id}/status', 'updateStatus');
            // Delete a request
            Route::delete('/{id}', 'destroy');
            // Get single request
            Route::get('/{id}', 'getById');
        });


        // 🔔 Notifications
        Route::prefix('notifications')->middleware('auth:api')->group(function () {
            Route::get('/', [NotificationController::class, 'all']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
        });



        //Update at later stage above as comapny profile
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::patch('/profile', [UserProfileController::class, 'update']);

        // 📧 Mail Route
        Route::post('/send-employee-mail', [MailController::class, 'sendEmployeeEmail']);
    });

    // Serve Files
    Route::get('/files/{path}', [FileController::class, 'show'])->where('path', '.*');
});

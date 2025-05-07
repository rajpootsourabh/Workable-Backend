<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenInvalidException) {
            return response()->json(['error' => 'Invalid token. Token signature could not be verified.'], 401);
        }

        if ($exception instanceof TokenExpiredException) {
            return response()->json(['error' => 'Token has expired. Please log in again.'], 401);
        }

        if ($exception instanceof JWTException) {
            return response()->json(['error' => 'Token is required.'], 401);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json(['error' => 'Authentication failed. Invalid or missing token.'], 401);
        }

        return parent::render($request, $exception);
    }
}

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
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $exception->errors(),
                ], 422);
            }
        }

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
            return $this->unauthenticated($request, $exception);
        }

        return parent::render($request, $exception);
    }
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'error' => 'Authentication required. Please include a valid token in the Authorization header.'
        ], 401);
    }
}

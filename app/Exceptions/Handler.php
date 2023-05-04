<?php

namespace App\Exceptions;

use Error;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use ApiResponse;

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
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->expectsJson()) {
            if ($e instanceof AuthenticationException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'Unauthenticated or Token Expired, Please Login',
                    ],
                    401
                );
            }
            if ($e instanceof ModelNotFoundException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'Entry for ' . str_replace('App\\', '', $e->getModel()) . ' not found',
                    ],
                    404
                );
            }
            if ($e instanceof ValidationException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'Failed Validation',
                        'errors' => $e->errors(),
                    ],
                    422
                );
            }
            if ($e instanceof Error) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => "There was some internal error",
                        'exception' => $e,
                    ],
                    500
                );
            }
        }

        return parent::render($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}

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
            if ($e instanceof PostTooLargeException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => "Size of attached file should be less " . ini_get("upload_max_filesize") . "B",
                    ],
                    400
                );
            }

            if ($e instanceof AuthenticationException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'Unauthorized',
                    ],
                    401
                );
            }
            if ($e instanceof ThrottleRequestsException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'Too Many Requests,Please Slow Down',
                    ],
                    429
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
            if ($e instanceof QueryException) {
                return $this->apiResponse(
                    [
                        'success' => 0,
                        'error' => 'There was Issue with the Query',
                        'exception' => $e,
                    ],
                    500
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

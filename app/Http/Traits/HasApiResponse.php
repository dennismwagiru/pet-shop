<?php

namespace App\Http\Traits;

use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait HasApiResponse
{
    protected function respondWithResource(
        JsonResource $data,
        $error = null,
        $statusCode = 200,
        $headers = []
    ): JsonResponse {
        return $this->apiResponse(
            [
                'success' => 1,
                'data' => $data,
                'error' => $error,
            ],
            $statusCode,
            $headers
        );
    }

    /**
     * @param $exception
     * @return array
     */
    public function parseException($exception): array
    {
        if (config('app.env') !== 'production') {
            return [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTrace(),
            ];
        }

        return [];
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @param string $type
     * @return array
     */
    public function parseGivenData(
        array $data = [],
        int $statusCode = 200,
        array $headers = [],
        string $type = 'data'
    ): array {
        if ($type === 'payload') {
            return ["content" => $data[$type], "statusCode" => $statusCode, "headers" => $headers];
        }

        $responseStructure = [
            'success' => $data['success'] ?? 0,
            'data' => $data[$type] ?? [],
            'error' => $data['error'] ?? null,
            'errors' => $data['errors'] ?? null,
        ];
        if (isset($data['status'])) {
            $statusCode = $data['status'];
        }

        if (isset($data['exception']) &&
            ($data['exception'] instanceof Error || $data['exception'] instanceof Exception)) {
            $responseStructure['trace'] = $this->parseException($data['exception']);

            $statusCode = 500;
        }
        if ($responseStructure['success'] === 0) {
            if (!isset($responseStructure['trace'])) {
                $responseStructure['trace'] = [];
            }
        } else {
            $responseStructure['extra'] = [];
        }
        return ["content" => $responseStructure, "statusCode" => $statusCode, "headers" => $headers];
    }

    /*
     *
     * Just a wrapper to facilitate abstract
     */

    /**
     * Return generic json response with the given data.
     *
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @param string $type
     * @return JsonResponse
     */
    protected function apiResponse(
        array $data = [],
        int $statusCode = 200,
        array $headers = [],
        string $type = 'data'
    ): JsonResponse {
        $data = $this->parseGivenData($data, $statusCode, $headers, $type);

        return response()->json(
            $data['content'],
            $data['statusCode'],
            $data['headers']
        );
    }

    /*
     *
     * Just a wrapper to facilitate abstract
     */

    /**
     * @param ResourceCollection $resourceCollection
     * @param string|null $error
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondWithResourceCollection(
        ResourceCollection $resourceCollection,
        string $error = null,
        int $statusCode = 200,
        array $headers = []
    ): JsonResponse {
        return $this->apiResponse(
            [
                'success' => 1,
                'data' => $resourceCollection->response()->getData(),
                'error' => $error,
            ],
            $statusCode,
            $headers
        );
    }

    /**
     * Respond with success.
     *
     * @param array $data
     * @return JsonResponse
     */
    protected function respondSuccess(array $data = []): JsonResponse
    {
        return $this->apiResponse(['success' => 1, 'data' => $data]);
    }

    /**
     * Respond with created.
     *
     * @param $data
     *
     * @return JsonResponse
     */
    protected function respondCreated($data): JsonResponse
    {
        return $this->apiResponse($data, 201);
    }

    /**
     * Respond with no content.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function respondNoContent(string $message = 'No Content Found'): JsonResponse
    {
        return $this->apiResponse(['success' => 0, 'message' => $message], 200);
    }

    /**
     * Respond with unauthorized.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function respondUnAuthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->respondError($message, 401);
    }

    /**
     * Respond with error.
     *
     * @param string|null $error
     * @param int $statusCode
     *
     * @param Exception|null $exception
     * @return JsonResponse
     */
    protected function respondError(
        ?string $error,
        int $statusCode = 400,
        Exception $exception = null,
    ): JsonResponse {
        return $this->apiResponse(
            [
                'success' => 0,
                'data' => [],
                'error' => $error ?? 'There was an internal error, Pls try again later',
                'errors' => [],
                'trace' => $exception->getTrace(),
            ],
            $statusCode
        );
    }

    /**
     * Respond with forbidden.
     *
     * @param string $error
     * @return JsonResponse
     */
    protected function respondForbidden(string $error = 'Forbidden'): JsonResponse
    {
        return $this->respondError($error, 403);
    }

    /**
     * Respond with not found.
     *
     * @param string $error
     * @return JsonResponse
     */
    protected function respondNotFound(string $error = 'Not Found'): JsonResponse
    {
        return $this->respondError($error, 404);
    }

    /**
     * Respond with internal error.
     *
     * @param string $error
     *
     * @return JsonResponse
     */
    protected function respondInternalError(string $error = 'Internal Error'): JsonResponse
    {
        return $this->respondError($error, 500);
    }

    protected function respondValidationErrors(ValidationException $exception): JsonResponse
    {
        return $this->apiResponse(
            [
                'success' => 0,
                'error' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ],
            422
        );
    }
}

<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    // 200 - OK
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success.',
        int $code = 200
    ): JsonResponse {
        $response = ['message' => $message];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    // 201 - Created
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    // 422 - Unprocessable Entity
    protected function validationErrorResponse(
        string $message = 'The given data was invalid.'
    ): JsonResponse {
        return response()->json(['message' => $message], 422);
    }

    // 403 - Forbidden
    protected function forbiddenResponse(
        string $message = 'Forbidden.'
    ): JsonResponse {
        return response()->json(['message' => $message], 403);
    }

    // 404 - Not Found
    protected function notFoundResponse(
        string $message = 'Resource not found.'
    ): JsonResponse {
        return response()->json(['message' => $message], 404);
    }

    // 500 - Server Error
    protected function serverErrorResponse(
        string $message = 'An unexpected error occurred.',
        string $error = ''
    ): JsonResponse {
        $response = ['message' => $message];

        if (!empty($error) && config('app.debug')) {
            $response['error'] = $error;
        }

        return response()->json($response, 500);
    }
}

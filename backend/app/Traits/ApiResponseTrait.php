<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'general.success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message, // translation key
            'data' => $data,
            'meta' => [
                'request_id' => request()->attributes->get('request_id')
            ]
        ], $statusCode);
    }

    protected function errorResponse(
        string $errorCode,
        string $message,
        array $errors = [],
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'error_code' => $errorCode,
            'message' => $message, // translation key
            'errors' => $errors,
            'meta' => [
                'request_id' => request()->attributes->get('request_id')
            ]
        ], $statusCode);
    }
}

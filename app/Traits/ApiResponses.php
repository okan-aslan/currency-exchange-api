<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    /**
     * Generate a success response.
     *
     * @param mixed $data The data to include in the response
     * @param string|null $message The message to include in the response
     * @param int $statusCode The HTTP status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    protected function success($data, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Generate an error response.
     *
     * @param mixed $data The data to include in the response
     * @param string|null $message The message to include in the response
     * @param int $statusCode The HTTP status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    protected function error($data, ?string $message = null, int $statusCode): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
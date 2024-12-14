<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Send a success response.
     *
     * @param  string $message
     * @param  array  $data
     * @return JsonResponse
     */
    public function sendResponse($message, $data = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    /**
     * Send an error response.
     *
     * @param  string $message
     * @param  array  $errors
     * @param  int    $code
     * @return JsonResponse
     */
    public function sendError($message, $errors = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => $errors,
        ];

        return response()->json($response, $code);
    }
}

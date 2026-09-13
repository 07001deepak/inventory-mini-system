<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('sendResponse')) {
    /**
     * Standardized API Success Response Helper Function.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    function sendResponse(mixed $data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}

if (!function_exists('sendError')) {
    /**
     * Standardized API Error Response Helper Function.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    function sendError(string $error = 'Error', array $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}

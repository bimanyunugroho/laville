<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Return success response with data.
     *
     * @param mixed $data Data to return
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Operation successful', int $code = 200): JsonResponse
    {
        // If data is empty or null, return 204 (No Content) instead
        if (empty($data) || (is_array($data) && count($data) === 0) ||
            ($data instanceof Collection && $data->isEmpty()) ||
            ($data instanceof LengthAwarePaginator && $data->isEmpty())) {
            return self::noContent($message);
        }

        return response()->json([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Return error response.
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error details
     * @return JsonResponse
     */
    public static function error(string $message = 'An error occurred', int $code = 500, $errors = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return not found response.
     *
     * @param string $message Not found message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Return no content response (204).
     *
     * @param string $message No content message
     * @return JsonResponse
     */
    public static function noContent(string $message = 'No content available'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'code' => 204,
            'message' => $message,
            'data' => null
        ], 200);
    }

    /**
     * Return validation error response.
     *
     * @param mixed $errors Validation errors
     * @param string $message Validation error message
     * @return JsonResponse
     */
    public static function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Return server error response.
     *
     * @param string $message Server error message
     * @param mixed $exception Exception details (optional)
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Server error', $exception = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'code' => 500,
            'message' => $message,
        ];

        if ($exception !== null && config('app.debug')) {
            $response['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return response()->json($response, 500);
    }
}

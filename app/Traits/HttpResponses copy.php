<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponses
{

    public function success(
        string $message = 'Request successful.',
        mixed $data = null,
        string $responseCode = '000',
        int $statusCode = 200
    ): JsonResponse {
        $res = [
            'status'       => 'success',
            'responseCode' => $responseCode,
            'message'      => $message,
        ];

        if ($data !== null) {
            $res['data'] = $data;
        }

        return response()->json($res, $statusCode);
    }


    public function error(
        string $message = 'Request failed.',
        string|int $responseCode = '400',
        array $errors = [],
        int $statusCode = 400
    ): JsonResponse {
        $res = [
            'status'       => 'error',
            'responseCode' => (string) $responseCode,
            'message'      => $message,
        ];

        if (!empty($errors)) {
            $res['errors'] = $errors;
        }

        return response()->json($res, $statusCode);
    }
}

<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

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

    // ✅ Handle nested ResourceCollection
    if (is_array($data)) {
        foreach ($data as $key => $value) {

            if ($value instanceof ResourceCollection) {
                $resource = $value->response()->getData(true);

                // Replace collection with actual data
                $data[$key] = $resource['data'] ?? [];

                // Inject pagination
                $data['pagination'] = [
                    'current_page'   => $resource['meta']['current_page'] ?? null,
                    'last_page'      => $resource['meta']['last_page'] ?? null,
                    'per_page'       => $resource['meta']['per_page'] ?? null,
                    'total'          => $resource['meta']['total'] ?? null,
                    'next_page_url'  => $resource['links']['next'] ?? null,
                    'prev_page_url'  => $resource['links']['prev'] ?? null,
                    'first_page_url' => $resource['links']['first'] ?? null,
                    'last_page_url'  => $resource['links']['last'] ?? null,
                ];
            }
        }
    }

    // ✅ ONLY add data if it's not null
    if (!is_null($data)) {
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

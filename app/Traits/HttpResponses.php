<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
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

        if ($data !== null) {
            $res['data'] = $this->transformData($data);
        }

        return response()->json($res, $statusCode);
    }

    private function transformData($data)
    {
        if ($data instanceof ResourceCollection) {

            $resource = $data->resource;

            if ($resource instanceof LengthAwarePaginator) {
                return [
                    'current_page'   => $resource->currentPage(),
                    'data'           => $data->collection,
                    'first_page_url' => $resource->url(1),
                    'from'           => $resource->firstItem(),
                    'last_page'      => $resource->lastPage(),
                    'last_page_url'  => $resource->url($resource->lastPage()),
                    'links'          => $resource->linkCollection(),
                    'next_page_url'  => $resource->nextPageUrl(),
                    'path'           => $resource->path(),
                    'per_page'       => $resource->perPage(),
                    'prev_page_url'  => $resource->previousPageUrl(),
                    'to'             => $resource->lastItem(),
                    'total'          => $resource->total(),
                ];
            }

            return $data->collection;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->transformData($value);
            }
            return $data;
        }

        return $data;
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
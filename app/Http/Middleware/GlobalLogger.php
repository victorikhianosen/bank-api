<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GlobalLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = (string) Str::uuid();

        try {
            $response = $next($request);
        } catch (\Throwable $e) {

            Log::error('');
            Log::error('************* EXCEPTION *****************');
            Log::error('Request ID: ' . $requestId);
            Log::error('URL: ' . $request->fullUrl());
            Log::error('Method: ' . $request->method());
            Log::error('IP: ' . $request->ip());
            Log::error('User Agent: ' . $request->userAgent());
            Log::error('User ID: ' . $this->resolveUser());
            Log::error('Request Data: ' . json_encode($this->sanitize($request)));
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('****************************************');
            Log::error('');

            throw $e;
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('');
        Log::info('************* GLOBAL REQUEST *************');
        Log::info('Request ID: ' . $requestId);
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        Log::info('IP: ' . $request->ip());
        Log::info('Guard: ' . $this->resolveGuard());
        Log::info('User ID: ' . $this->resolveUser());
        Log::info('User Agent: ' . $request->userAgent());
        Log::info('Request Data: ' . json_encode($this->sanitize($request), JSON_PRETTY_PRINT));
        // Log::info('Request Data: ' . json_encode($this->sanitize($request)));
        Log::info('Status Code: ' . $response->getStatusCode());
        // Log::info('Response: ' . json_encode(
        //     json_decode($response->getContent(), true)
        // ));
        Log::info('Response: ' . json_encode(
            json_decode($response->getContent(), true),
            JSON_PRETTY_PRINT
        ));
        Log::info('Execution Time: ' . $executionTime . ' ms');
        Log::info('*******************************************');
        Log::info('');

        return $response;
    }

    protected function resolveUser()
    {
        return Auth::guard('user')->id()
            ?? Auth::guard('customer')->id()
            ?? 'Guest';
    }

    protected function resolveGuard()
    {
        if (Auth::guard('user')->check()) {
            return 'user';
        }

        if (Auth::guard('customer')->check()) {
            return 'customer';
        }

        return 'none';
    }

    protected function sanitize(Request $request): array
    {
        return $request->except([
            'password',
            'password_confirmation',
            'token',
        ]);
    }
}

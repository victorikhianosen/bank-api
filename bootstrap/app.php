<?php

use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\GlobalLogger;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        then: function () {
            Route::prefix('user')
                ->group(base_path('routes/user.php'));

            Route::prefix('customer')
                ->group(base_path('routes/customer.php'));
        },
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'customer' => CustomerMiddleware::class,
            'user' => UserMiddleware::class

        ]);
        $middleware->append(GlobalLogger::class);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {

            if (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('user/*') && ! $request->is('customer/*')) {
                return null;
            }

            $statusCode = 500;
            $responseCode = '500';
            $message = 'We couldn’t process your request at the moment. Please try again.';
            $errors = [];

            // Validation Error
            if ($e instanceof ValidationException) {
                $statusCode = 422;
                $responseCode = '422';
                $message = 'Validation error.';
                $errors = $e->errors();
            }

            // Authentication
            elseif ($e instanceof AuthenticationException) {
                $statusCode = 401;
                $responseCode = '200';
                $message = 'Unauthenticated.';
            }

            // Authorization
            elseif ($e instanceof AuthorizationException) {
                $statusCode = 403;
                $responseCode = '208';
                $message = 'Access denied.';
            }

            // Model Not Found
            elseif ($e instanceof ModelNotFoundException) {
                $statusCode = 404;
                $responseCode = '400';
                $model = class_basename($e->getModel() ?? 'Resource');
                $message = "{$model} not found.";
            }

            // Route Not Found
            elseif ($e instanceof NotFoundHttpException) {
                $statusCode = 404;
                $responseCode = '404';
                $message = 'Route not found.';
            }

            // Method Not Allowed
            elseif ($e instanceof MethodNotAllowedHttpException) {
                $statusCode = 405;
                $responseCode = '405';
                $message = 'Method not allowed.';
            }

            // Too Many Requests
            elseif ($e instanceof TooManyRequestsHttpException) {
                $statusCode = 429;
                $responseCode = '429';
                $message = 'Too many requests.';
            }

            // Too Package Requests
            elseif ($e instanceof InvalidArgumentException) {
                $statusCode = 422;
                $responseCode = '422';
                $message = $e->getMessage();
            }

            // Generic HTTP Exception
            elseif ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
                $responseCode = (string) $statusCode;
                $message = $e->getMessage() ?: 'HTTP error.';
            }

            return response()->json([
                'status'       => 'error',
                'responseCode' => $responseCode,
                'message'      => $message,
                'errors'       => !empty($errors) ? $errors : null,
            ], $statusCode);
        });
    })->create();

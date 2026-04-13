<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    use HttpResponses;
    public function handle(Request $request, Closure $next): Response
    {
         if (!Auth::guard('user')->check()) {
            return $this->error(message:'Unauthorized user', statusCode:401, responseCode:'401');
        }
        return $next($request);
    }
}

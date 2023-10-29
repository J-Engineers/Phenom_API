<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->api_key OR !$request->api_key === env('API_KEY')){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad Request'
            ], Response::HTTP_NOT_FOUND);
        }
        return $next($request);
    }
}

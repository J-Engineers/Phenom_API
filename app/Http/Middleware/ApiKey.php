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
    public function handle(Request $irequest, Closure $next): Response
    {
        $api_key = $irequest->api_key;

        if(!$api_key OR !$api_key == env('API_KEY')){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad Request'
            ], Response::HTTP_NOT_FOUND);
        }
        return $next($irequest);   
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BookStoreUser;
use Symfony\Component\HttpFoundation\Response;

class BookStoreAuthRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $auth = auth()->user();
        if(!($auth->user_type == 'bookshop')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $parent = BookStoreUser::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a Book Store',
            ], Response::HTTP_NOT_FOUND); // 404
        }
        return $next($request);
    }
}

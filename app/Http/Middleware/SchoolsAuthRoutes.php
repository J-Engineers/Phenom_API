<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\GreatSchool;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolsAuthRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = auth()->user();
        if(!($auth->user_type == 'school')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $parent = GreatSchool::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a School',
            ], Response::HTTP_NOT_FOUND); // 404
        }
        return $next($request);
    }
}

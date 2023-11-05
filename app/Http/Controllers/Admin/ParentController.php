<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Lesson;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ParentController extends Controller
{
    //
    public function getParents(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorizeds'
            ], Response::HTTP_NOT_FOUND);
        }

        $users = DB::table('parent_user as pu')
        ->leftJoin('users As u', function($join){
            $join->on('pu.user_id', '=', 'u.id');
        })
        
        ->select(
            'u.id as user_id',
            'u.email as parent_email',
        )
        ->get();

      

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Found',
            'total_parent' => count($users),
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function getParentDetails(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:users,id',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $user_parent = DB::table('users as u')
        ->leftJoin('parent_user as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $request->user_id)->get();

        $parent = ParentUser::where('user_id', $request->user_id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $learners = Lesson::dashboard($parent->id);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'View Lesson',
            'data' => [
                'parent' => $user_parent,
                'lessons' => $learners,
                
            ]
        ], Response::HTTP_OK);
    }
    
    public function searchParent(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'search' => 'required|string',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }
        $filter = '%'.$request->search.'%';

        $tutor_user = DB::table('users as u')
        ->where(
            [
                ['id' , 'LIKE', $filter],
                ['user_type' , '=', 'parent']
            ]
        )
        ->orWhere(
            [
                ['email' , 'LIKE', $filter],
                ['user_type' , '=', 'parent'],
            ]
        )
        ->select(
            'u.email as parent_email', 
            'u.first_name as parent_firstname',  
            'u.last_name as parent_lastname', 
            'u.phone as parent_contact', 
            'u.id as user_id',
        )
        ->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Search Result',
            'data' => [
                'result' => $tutor_user,
            ]
        ], Response::HTTP_OK);
    }
}

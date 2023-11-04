<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //
    public function getUsers(Request $request){

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
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $users = User::all();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Found',
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function getUser(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:education_levels,id'
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

        $search_user = User::where('id', $request->user_id)->first();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Found',
            'data' => $search_user
        ], Response::HTTP_OK);
    }

    public function removeUser(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string'
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

        $search_user = User::where('id', $request->user_id)->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Deleted',
            'data' => []
        ], Response::HTTP_OK);
    }

    public function deactivateUser(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:education_levels,id'
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

        $search_user = User::where('id', $request->user_id)->first();
        if(!$search_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_user->update(['activate' => false]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Deactivated',
            'data' => $search_user
        ], Response::HTTP_OK);
    }

    public function activateUser(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:education_levels,id'
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

        $search_user = User::where('id', $request->user_id)->first();
        if(!$search_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_user->update(['activate' => true]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Activated',
            'data' => $search_user
        ], Response::HTTP_OK);
    }

    public function makeAdmin(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:education_levels,id'
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

        $search_user = User::where(
            [
                ['id', $request->user_id],
                ['user_type', 'admin'],
            ]
        )->first();
        if(!$search_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User Not Found or User not an admin'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_user->update(['is_admin' => true]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Activated',
            'data' => $search_user
        ], Response::HTTP_OK);
    }

    public function cancelAdmin(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string|exists:education_levels,id'
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

        $search_user = User::where('id', $request->user_id)->first();
        if(!$search_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_user->update(['is_admin' => false]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Activated',
            'data' => $search_user
        ], Response::HTTP_OK);
    }
}

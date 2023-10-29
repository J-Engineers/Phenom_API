<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Storage;

class ProfileController extends Controller
{
    //
    public function details(Request $request){


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
        
        $auth = auth()->user()->id;
        
        $user = User::where('id', $auth)->first();
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Found',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request){

        $user = auth()->user();

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'error',
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        $password = Hash::make($request->password);
        $user->update([
            'password' => $password
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Password Reset Successful',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function updateDetails(Request $request){

        $user = auth()->user();

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'title' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'address' => 'required|string',
        ]);

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'error',
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        $user->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'address' => $request->address
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Profile Updated',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function updatePhoto(Request $request){

        $user = auth()->user();

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'image' => 'required|image'
        ]);

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'error',
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        if(!$request->hasfile('image'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->file('image');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $base_path = $disk->url($filePath);
        $user->update([
            'photo' => $base_path
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Profile Picture Updated',
            'data' => $user
        ], Response::HTTP_OK);
    }
}

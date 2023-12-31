<?php

namespace App\Http\Controllers\User;

use Storage;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AuthController\DetailsRequest;
use App\Http\Requests\AuthController\UpdatePhotoRequest;
use App\Http\Requests\AuthController\UpdateDetailsRequest;
use App\Http\Requests\AuthController\ChangePasswordRequest;

class ProfileController extends Controller
{
    //
    public function details(DetailsRequest $request){
        $request->validated();
        $auth = auth()->user()->id;
        
        $user = User::where('id', $auth)->first();
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'User Found',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function changePassword(ChangePasswordRequest $request){
        $request->validated();
        $user = auth()->user();

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

    public function updateDetails(UpdateDetailsRequest $request){
        $request->validated();
        $user = auth()->user();
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

    public function updatePhoto(UpdatePhotoRequest $request){
        $request->validated();
        $user = auth()->user();

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
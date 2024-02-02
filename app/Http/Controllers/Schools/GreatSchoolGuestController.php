<?php

namespace App\Http\Controllers\Schools;
use Storage;

use App\Models\GreatSchool;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Schools\GreatSchoolsRequest;
use App\Http\Requests\Schools\GreatSchoolSearchRequest;
use App\Http\Requests\Schools\GreatSchoolGuestRequestRequest;
use App\Models\GreatSchoolRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class GreatSchoolGuestController extends Controller
{
    public function toprated(GreatSchoolsRequest $request){
        $school = GreatSchool::where(
            [
                ['rated', '>', 3],
                ['status', '=', 1],
            ]
        )->get();
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Top School data',
                'data' => [
                    'top_schools' => $school,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function search(GreatSchoolSearchRequest $request){
        $school = GreatSchool::where(
            [
                ['rated', '>', 2],
                ['status', '=', 1],
                ['state', '=', $request->state],
                ['localgovernment', '=', $request->localgovernment],
            ]
        )->get();
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Top School data',
                'data' => [
                    'top_schools' => $school,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function request(GreatSchoolGuestRequestRequest $request){

        if(!$request->hasfile('picture'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found, upload a file for the student with the name picture',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!$request->hasfile('transcript'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found, upload a file for the transcript with the name transcript',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!$request->hasfile('prev_school_note'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found, upload a file for the previous school with the name prev_school_note',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $verify_token = mt_rand(1000000, 9999999);
        $to_name = $request->name;
        $to_email = $request->email;
        $data = array(
           "name"=> $request->name,
           "body" => "Welcome to Phenom Platform, We are glad you have requested a discounted entry fee to great partners of our organization. 
                    We shall review your request and communicate with you further. Take your token to the school. Your token: ".$verify_token,
           "link" => env('APP_URL')
        );
       
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
           $message->to($to_email, $to_name)->subject("Phenom Great Schools Request");
           $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
        })){

            return response()->json([
               'status_code' => Response::HTTP_NOT_FOUND,
               'status' => 'error',
               'message' => 'Mail Not Sent, try again later',
               'data' => $request
            ], Response::HTTP_NOT_FOUND);
        }

        $file = $request->file('picture');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $picture_path = $disk->url($filePath);

        $file = $request->file('transcript');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $transcript_path = $disk->url($filePath);

        $file = $request->file('prev_school_note');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $prev_school_note_path = $disk->url($filePath);
        
        $school = GreatSchoolRequest::create([
            'id' => (string)Str::uuid(),
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'state' => $request->state,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'localgovernment' => $request->localgovernment,
            'description' => $request->description,
            'great_school_id' => $request->great_school_id,
            'prev_school' => $request->prev_school,
            'picture' => $picture_path,
            'transcript' => $transcript_path,
            'prev_school_note' => $prev_school_note_path,
            'token' => $verify_token,
            'status' => 0
        ]);

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Request successfully',
                'data' => [
                    'request' => $school,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

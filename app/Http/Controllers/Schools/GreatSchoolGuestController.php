<?php

namespace App\Http\Controllers\Schools;
use Storage;

use App\Models\GreatSchool;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GreatSchoolRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Schools\SchoolRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Schools\GreatSchoolsRequest;
use App\Http\Requests\Schools\GreatSchoolSearchRequest;
use App\Http\Requests\Schools\GreatSchoolGuestRequestRequest;

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

    public function school(SchoolRequest $request){
        $school = GreatSchool::where(
            [
                ['rated', '>', 3],
                ['status', '=', 1],
                ['id', '=', $request->school_id],
            ]
        )->first();
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Top School data',
                'data' => [
                    'school' => $school,
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
            "p1" => "Welcome to ".env('APP_NAME')." – your premier destination for educational excellence! 
                 We are thrilled to have you join our community dedicated to connecting learners, parents, tutors, schools, 
                 and bookstores seamlessly.At ".env('APP_NAME').", we offer a diverse range of services to cater to your educational needs:",
           "p2" => "Whether you're seeking online or in-person classes, 
                 we connect you with exceptional tutors closest to you who are committed to helping you achieve your learning goals.",
           "p3" => "Easily discover and connect with top-notch schools in your vicinity, 
                 ensuring you find the perfect educational institution that meets your requirements.",
           "p4" => "Access a vast array of books from reputable bookstores right at your 
                 fingertips. With just a few clicks, you can order the resources you need to enhance your learning journey.",
           "p5" => "By signing up with ".env('APP_NAME').", you've taken the first step towards unlocking a world of educational opportunities. 
                 Our user-friendly platform is designed to streamline your learning experience, providing you with the tools and resources 
                 necessary to succeed.",
           "p6" => "We shall review your request and communicate with you further. Take your token to the school. Your token: ".$verify_token,
           "p7" => "We're here to support you every step of the way. Should you have any questions, feedback, or suggestions, please don't hesitate 
                 to reach out to our dedicated customer support team.",
           "p8" => "Once again, welcome to ".env('APP_NAME')."! Get ready to embark on an enriching learning journey unlike any other.",
           "p9" => "Best Regards,",
           "p10" => env('APP_NAME')." Team",
           "p11" => "P.S. Stay tuned for exciting updates, exclusive offers, and valuable educational insights delivered straight to your inbox!",
           
           "d1" => "1. Expert Tutor Connections via phenomtutors:",
           "d2" => "2. School Search and Connection via phenomconnect:",
           "d3" => "3. Effortless Book Procurement via the bookstore:",
           
            "link" => env('APP_URL')
         );
       
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
           $message->to($to_email, $to_name)->subject(env('APP_NAME')." Great Schools Request");
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

<?php

namespace App\Http\Controllers\Admin;

use Storage;

use App\Models\User;
use App\Models\GreatSchool;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\GreatSchoolRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\GreatSchoolsRequest;
use App\Http\Requests\Admin\GreatSchoolRequest as RequestGreatSchoolRequest;
use App\Http\Requests\Admin\GreatSchoolRateRequest;
use App\Http\Requests\Admin\GreatSchoolUpdateRequest;
use App\Http\Requests\Admin\GreatSchoolRequestRequest;
use App\Http\Requests\Admin\GreatSchoolsSignUpRequest;

class GreatSchoolsController extends Controller
{
    public function signup(GreatSchoolsSignUpRequest $request){

        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        if(!$request->hasfile('picture'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found, upload a file for the school with the name picture',
            ], Response::HTTP_UNAUTHORIZED);
        }

        

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if ($user) {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'Email Taken, Kindly login or recover your passward',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $temporal_password = mt_rand(100000, 999999);

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
           "p6" => "To get started, here is your temporary password: ".$temporal_password.". You can use this to sign in to the platform and 
                 explore all that Phenom has to offer.",
           "p7" => "We're here to support you every step of the way. Should you have any questions, feedback, or suggestions, please don't hesitate 
                 to reach out to our dedicated customer support team.",
           "p8" => "Once again, welcome to ".env('APP_NAME')."! Get ready to embark on an enriching learning journey unlike any other.",
           "p9" => "Best Regards,",
           "p10" => env('APP_NAME')." Team",
           "p11" => "P.S. Stay tuned for exciting updates, exclusive offers, and valuable educational insights delivered straight to your inbox!",
           
           "d1" => "1. Expert Tutor Connections via phenomtutors:",
           "d2" => "2. School Search and Connection via phenomconnect:",
           "d3" => "3. Effortless Book Procurement via the bookstore:",
           
            "link" => env('APP_URL').'learner/signup'
         );
       
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
           $message->to($to_email, $to_name)->subject(env('APP_NAME')." Great Schools Registration");
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
        $base_path = $disk->url($filePath);

        $mew_user_id = (string)Str::uuid();

        $password = Hash::make($temporal_password);
        $user_new = User::create([
            'id' => $mew_user_id,
            'user_name' => str_split($request->name)[0]." ".str_split($request->name)[1],
            'email' => $request->email,
            'is_admin' => false,
            'activate' => true,
            'password' => $password,
            'phone' => $request->phone,
            'verify_token' => '0',
            'verify_email' => true,
            'email_verified_at' => Carbon::now(),
            'title' => "Mr./Mrs.",
            'first_name' => $request->name,
            'last_name' => '',
            'gender' => 'male or femalw',
            'user_type' => 'school',
            'address' => $request->address,
            'photo' => $base_path,
        ]);

        if(!$user_new->id){
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'User not created',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $school = GreatSchool::create
        (
            [
                'id' => (string)Str::uuid(),
                'user_id' => $user_new->id,
                'name' => $user_new->first_name,
                'email' => $user_new->email,
                'phone' => $user_new->phone,
                'address' => $user_new->address,
                'state' => $request->state,
                'picture' => $user_new->photo,
                'localgovernment' => $request->localgovernment,
                'description' => $request->description,
                'population' => $request->population,
                'male_or_female_or_both' => $request->male_or_female_or_both,
                'day_or_boarding_or_both' => $request->day_or_boarding_or_both,
                'private_or_government_or_both' => $request->private_or_government_or_both,
                'token' => $verify_token,
                'status' => 0,
                'rated' => 3,
            ]
        );

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School signed up successfully',
                'data' => [
                    'user' => $user_new,
                    'school' => $school,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function schools(GreatSchoolsRequest $request){
        $request->validated();
        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $schools = GreatSchool::all();
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'schools' => $schools,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school(RequestGreatSchoolRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school = GreatSchool::where('id', $request->school_id)->first();
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'school' => $school,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school_updates(GreatSchoolUpdateRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        // if(!$request->hasfile('picture'))
        // {
        //     return response()->json([
        //         'status_code' => Response::HTTP_UNAUTHORIZED,
        //         'status' => 'error',
        //         'message' => 'File not Found, upload a file for the school with the name picture',
        //     ], Response::HTTP_UNAUTHORIZED);
        // }


        $School = GreatSchool::where('id',  $request->school_id)->first();
        if(!$School){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'School Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        // $file = $request->file('picture');
        // $name=time().$file->getClientOriginalName();
        // $filePath = 'images/' . $name;
        // $disk = Storage::disk('s3');
        // $disk->put($filePath, file_get_contents($file));
        // $base_path = $disk->url($filePath);


        $School->update(
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'state' => $request->state,
                // 'picture' => $base_path,
                'localgovernment' => $request->localgovernment,
                'description' => $request->description,
                'population' => $request->population,
                'male_or_female_or_both' => $request->male_or_female_or_both,
                'day_or_boarding_or_both' => $request->day_or_boarding_or_both,
                'private_or_government_or_both' => $request->private_or_government_or_both,
            ]
        );
        
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Updated',
                'data' => [
                    'school' => $School,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school_remove(RequestGreatSchoolRequest $request){

        $request->validated();
        
        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school = GreatSchool::where('id', $request->school_id)->first();
        if(!$school){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'School Not Found'
            ], Response::HTTP_NOT_FOUND);
        }
        User::where('id', $school->user_id)->delete();
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Record Removed',
                'data' => [
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school_approve(RequestGreatSchoolRequest $request){

        $request->validated();
        
        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school = GreatSchool::where('id', $request->school_id)->first();
        if(!$school){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'School Not Found'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $school->update([
            'status' => 1
        ]);

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Record Approved',
                'data' => [
                    'school' => $school
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school_disapprove(RequestGreatSchoolRequest $request){

        $request->validated();
        
        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school = GreatSchool::where('id', $request->school_id)->first();
        if(!$school){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'School Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $school->update([
            'status' => 0
        ]);

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Record Dsiapproved',
                'data' => [
                    'school' => $school
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function schoool_requests(RequestGreatSchoolRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school_request = GreatSchoolRequest::all();
        foreach($school_request as $school_request_value){
            $school = GreatSchool::where('id', $school_request_value->great_school_id)->first();
            $school_request_value['school'] = $school;
        }
        

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Request data',
                'data' => [
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function schoool_requestss(GreatSchoolsRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school_request = GreatSchoolRequest::all();
        foreach($school_request as $school_request_value){
            $school = GreatSchool::where('id', $school_request_value->id)->first();
            $school_request_value['school'] = $school;
        }
        

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Request data',
                'data' => [
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function schoool_request(GreatSchoolRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $school_request = GreatSchoolRequest::where('id', $request->school_request_id)->first();
        if(!$school_request){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Request Not Found'
            ], Response::HTTP_NOT_FOUND);
        }
        $school = GreatSchool::where('id', $school_request->great_school_id)->first();
        $school_request['school'] = $school;
        

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Request data',
                'data' => [
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function remove_schoool_request(GreatSchoolRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        GreatSchoolRequest::where('id', $request->school_request_id)->delete();

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Request data Remoeved',
                'data' => [
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function school_rate(GreatSchoolRateRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $School = GreatSchool::where('id',  $request->school_id)->first();
        if(!$School){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'School Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $School->update(
            [
                'rated' => $request->rated,
            ]
        );
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School Updated',
                'data' => [
                    'school' => $School,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

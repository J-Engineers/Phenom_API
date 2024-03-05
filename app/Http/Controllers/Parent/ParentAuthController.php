<?php

namespace App\Http\Controllers\Parent;

use App\Models\User;
use App\Helpers\Lesson;
use App\Models\Learner;
use App\Models\Lessons;
use App\Models\ParentUser;
use Illuminate\Support\Str;
use App\Models\LessonLearner;
use App\Models\LessonSubject;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\LessonSubjectTimetable;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ParentController\SignUpRequest;


class ParentAuthController extends Controller
{
    //
    public function signup(SignUpRequest $request){
        $request->validated();

        if(auth()->user()){
            auth()->user()->tokens()->delete();
        }
        
        for ($i_subjects=1; $i_subjects <= $request->total_subjects; $i_subjects++) {

            $fields = Validator::make($request->all(), [
                'subject_id_'.$i_subjects => 'required|string|exists:subjects,id',
                'tutor_gender_'.$i_subjects => 'required|string',
                'tutor_type_'.$i_subjects => 'required|string',
                'total_day_'.$i_subjects => 'required|integer',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
            $base_day = 'total_day_'.$i_subjects;

            for ($i_subjects_days=1; $i_subjects_days <= $request->$base_day; $i_subjects_days++) {

                $fields = Validator::make($request->all(), [
                    'day_id_'.$i_subjects_days.'_'.$i_subjects => 'required|string|exists:lesson_day,id',
                    'day_hours_'.$i_subjects_days.'_'.$i_subjects => 'required|string',
                    'start_time_'.$i_subjects_days.'_'.$i_subjects => 'required|string',
                ]); // request body validation rules
        
                if($fields->fails()){
                    return response()->json([
                        'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'status' => 'error',
                        'message' => $fields->messages(),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
                } // request body validation failed, so lets return
            }
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

        $verify_token = mt_rand(100000, 999999);
        $to_name = $request->firstname." ".$request->lastname;
        $to_email = $request->email;
        $data = array(
            "name"=> $request->firstname." ".$request->lastname,
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
           $message->to($to_email, $to_name)->subject("Welcome to Phenom.ng: Your One Stop Hub for Education Excellence");
           $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
        })){

            return response()->json([
               'status_code' => Response::HTTP_NOT_FOUND,
               'status' => 'error',
               'message' => 'Mail Not Sent, try again later',
               'data' => $request
            ], Response::HTTP_NOT_FOUND);
        }


        $password = Hash::make($temporal_password);
        $user = User::create([
            'id' => (string)Str::uuid(),
            'user_name' => str_split($request->firstname)[0]." ".str_split($request->lastname)[0],
            'email' => $request->email,
            'is_admin' => false,
            'activate' => true,
            'password' => $password,
            'phone' => $request->phone,
            'verify_token' => '0',
            'verify_email' => true,
            'email_verified_at' => Carbon::now(),
            'title' => $request->title,
            'first_name' => $request->firstname,
            'last_name' => $request->lastname,
            'gender' => $request->gender,
            'user_type' => 'parent',
            'address' => $request->address." ".$request->state." ".$request->country,
        ]);

        $user['temporal_passward'] = $temporal_password;

        if(!$user->id){
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'User not created',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $parent = ParentUser::create([
            'id' => (string)Str::uuid(),
            'how_did_you_know_us' => $request->how_did_you_know_about_us,
            'user_id' => $user->id,
        ]);

        $saerch_parent = Learner::where(
            [
                ['learners_name', '=', $request->learners_name],
                ['parent_id', '=', $parent->id],
            ]
        )->first();

        if($saerch_parent){
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'Learner Already Exist, Login and add learner to a lesson',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $learner = Learner::create([
            'id' => (string)Str::uuid(),
            'parent_id' => $parent->id,
            'learners_name' => $request->learners_name,
            'learners_dob' => $request->learners_dob,
            'learners_gender' => $request->learners_gender,
            'other_subjects' => $request->others,
        ]);

        $lesson = Lessons::create([
            'id' => (string)Str::uuid(),
            'parent_id' => $parent->id,
            'lesson_address' => $request->lesson_address,
            'lesson_goals' => $request->lesson_goals,
            'lesson_mode' => $request->lesson_mode,
            'lesson_period' => $request->lesson_period,
        ]);

        $lesson_learner = LessonLearner::create([
            'id' => (string)Str::uuid(),
            'lesson_id' => $lesson->id,
            'learner_id' => $learner->id,
            'learners_description' => $request->description_of_learner,
            'lesson_commence' => $request->lesson_commence,
        ]);

        
        

        for ($i_subjects=1; $i_subjects <= $request->total_subjects; $i_subjects++) {

            $subject_id = "subject_id_".$i_subjects;
            $tutor_gender = "tutor_gender_".$i_subjects;
            $tutor_type = "tutor_type_".$i_subjects;

            $total_days = "total_day_".$i_subjects;

            
            $education_level = LessonSubject::create([
                'id' => (string)Str::uuid(),
                'lesson_learner_id' => $lesson_learner->id,
                'subject_id' => $request->$subject_id,
                'learner_tutor_gender' => $request->$tutor_gender,
                'learner_tutor_type' => $request->$tutor_type,
            ]);
            

            for ($i_total_day=1; $i_total_day <= $request->$total_days; $i_total_day++) {


                $day_id = "day_id_".$i_total_day.'_'.$i_subjects;
                $day_hours = "day_hours_".$i_total_day.'_'.$i_subjects;
                $start_time = "start_time_".$i_total_day.'_'.$i_subjects;

                LessonSubjectTimetable::create([
                    'id' => (string)Str::uuid(),
                    'lesson_subject_id' => $education_level->id,
                    'lesson_day_id' => $request->$day_id,
                    'lesson_day_hours' => $request->$day_hours,
                    'lesson_day_start_time' => $request->$start_time,
                ]);
            }
        }

        $learners = Lesson::dashboard($parent->id);
        

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'User signed up successfully',
                'data' => [
                    'user' => $user,
                    'parent' => $parent,
                    'learners' => $learners,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

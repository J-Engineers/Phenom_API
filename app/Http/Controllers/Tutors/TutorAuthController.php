<?php

namespace App\Http\Controllers\Tutors;

use Storage;
use App\Models\User;
use App\Models\Tutor;
use Illuminate\Support\Str;
use App\Models\TutorSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TutorExperience;
use App\Models\TutorsIdentities;
use App\Models\TutorsCertification;
use App\Models\TutorsQualification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;


ini_set('max_execution_time', 180); // 3 minutes

class TutorAuthController extends Controller
{
    //
    public function signup(Request $request){

        if(auth()->user()){
            auth()->user()->tokens()->delete();
        }

        $fields = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone' => 'required|string',
            'birthday' => 'required|string',
            'gender' => 'required|string',
            'mode' => 'required|string',
            'photo' => 'required|image',
            'nationality' => 'required|string',
            'state' => 'required|string',
            'title' => 'required|string',
            'current_location' => 'required|string',
            'how_did_you_know_about_us' => 'required|string',
            'other_subjects' => 'required|string',
            'total_experiences' => 'required|integer',
            'total_certificates' => 'required|integer',
            'total_identities' => 'required|integer',
            'total_qualifications' => 'required|integer',
            'total_subjects' => 'required|integer',
            'api_key' => 'required|string',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'error',
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        for ($i_experience=1; $i_experience <= $request->total_experiences; $i_experience++) {

            $fields = Validator::make($request->all(), [
                'experience_'.$i_experience => 'required|string',
                'years_of_experience_'.$i_experience => 'required|integer',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
        }

        for ($i_cert=1; $i_cert <= $request->total_certificates; $i_cert++) {

            $fields = Validator::make($request->all(), [
                'organization_'.$i_cert => 'required|string',
                'course_'.$i_cert => 'required|string',
                'duration_'.$i_cert => 'required|string',
                'date_'.$i_cert => 'required|string',
                'certificate_link_'.$i_cert => 'required|file',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return

            if(!$request->hasfile('certificate_link_'.$i_cert))
            {
                return response()->json([
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'status' => 'error',
                    'message' => 'Certificate File not Found',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        for ($i_id=1; $i_id <= $request->total_identities; $i_id++) {

            $fields = Validator::make($request->all(), [
                'name_'.$i_id => 'required|string',
                'acquired_date_'.$i_id => 'required|string',
                'expiration_date_'.$i_id => 'required|string',
                'identity_link_'.$i_id => 'required|file',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return

            if(!$request->hasfile('identity_link_'.$i_id))
            {
                return response()->json([
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'status' => 'error',
                    'message' => 'Idenitity File not Found',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        for ($i_quali=1; $i_quali <= $request->total_qualifications; $i_quali++) {

            $fields = Validator::make($request->all(), [
                'university_'.$i_quali => 'required|string',
                'course_'.$i_quali => 'required|string',
                'country_'.$i_quali => 'required|string',
                'date_of_graduation_'.$i_quali => 'required|string',
                'grade_'.$i_quali => 'required|string',
                'degree_'.$i_quali => 'required|string',
                'qualification_link_'.$i_quali => 'required|file',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return

            if(!$request->hasfile('qualification_link_'.$i_quali))
            {
                return response()->json([
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'status' => 'error',
                    'message' => 'Qualification File not Found',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        for ($i_sub=1; $i_sub <= $request->total_subjects; $i_sub++) {

            $fields = Validator::make($request->all(), [
                'hours_per_week_'.$i_sub => 'required|string',
                'level_id_'.$i_sub => 'required|string|exists:education_levels,id',
                'subject_id_'.$i_sub => 'required|string|exists:subjects,id',
            ]); // request body validation rules
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
        }

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if ($user) {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'Email Taken',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $temporal_password = mt_rand(100000, 999999);

        $verify_token = mt_rand(100000, 999999);
        $to_name = $request->firstname." ".$request->lastname;
        $to_email = $request->email;
        $data = array(
           "name"=> $request->firstname." ".$request->lastname,
           "body" => "Welcome to Phenom Platform, We are glad you have requested to be a tutor in our organization. 
                    We shall review your request and comminicate with you further. Your temporal password is ".$temporal_password.", You are free to change it later.",
           "link" => env('APP_URL').'user/login'
        );
       
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
           $message->to($to_email, $to_name)->subject("Phenom Tutor Registration");
           $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
        })){

            return response()->json([
               'status_code' => Response::HTTP_NOT_FOUND,
               'status' => 'error',
               'message' => 'Mail Not Sent',
               'data' => $request
            ], Response::HTTP_NOT_FOUND);
        }


        if(!$request->hasfile('photo'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->file('photo');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $base_path = $disk->url($filePath);


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
            'address' => $request->address,
            'photo' => $base_path,
            'address' => $request->current_location,
            'user_type' => 'tutor',
        ]);
        $user['temporal_password'] = $temporal_password;

        if(!$user->id){
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'User not created',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tutor = Tutor::create([
            'id' => (string)Str::uuid(),
            'delivery_mode' => $request->mode,
            'birthday' => $request->birthday,
            'nationality' => $request->nationality,
            'state' => $request->state,
            'current_location' => $request->current_location,
            'how_did_you_know_about_us' => $request->how_did_you_know_about_us,
            'other_subjects' => $request->other_subjects,
            'user_id' => $user->id,
        ]);

        for ($i_experience=1; $i_experience <= $request->total_experiences; $i_experience++) {

            $experience = "experience_".$i_experience;
            $years_of_exp = "years_of_experience_".$i_experience;

            TutorExperience::create([
                'id' => (string)Str::uuid(),
                'experience' => $request->$experience,
                'years_of_experience' => $request->$years_of_exp,
                'user_id' => $user->id,
            ]);
        }
        $tutor_experience = TutorExperience::where('user_id', $user->id)->get();


        for ($i_cert=1; $i_cert <= $request->total_certificates; $i_cert++) {

            $organization = "organization_".$i_cert;
            $course = "course_".$i_cert;
            $duration = "duration_".$i_cert;
            $date = "date_".$i_cert;
            $link = "certificate_link_".$i_cert;


            
            $file = $request->file($link);
            $name=time().$file->getClientOriginalName();
            $filePath = 'images/' . $name;
            $disk = Storage::disk('s3');
            $disk->put($filePath, file_get_contents($file));
            $base_path = $disk->url($filePath);

            TutorsCertification::create([
                'id' => (string)Str::uuid(),
                'organization' => $request->$organization,
                'course' => $request->$course,
                'duration' => $request->$duration,
                'date' => $request->$date,
                'link' => $base_path,
                'user_id' => $user->id,
            ]);
        }
        $tutor_certification = TutorsCertification::where('user_id', $user->id)->get();


        for ($i_id=1; $i_id <= $request->total_identities; $i_id++) {

            $id_name = "name_".$i_id;
            $acquired_date = "acquired_date_".$i_id;
            $expiration_date = "expiration_date_".$i_id;
            $identity_link = "identity_link_".$i_id;


            $file = $request->file($identity_link);
            $name=time().$file->getClientOriginalName();
            $filePath = 'images/' . $name;
            $disk = Storage::disk('s3');
            $disk->put($filePath, file_get_contents($file));
            $base_path = $disk->url($filePath);

            TutorsIdentities::create([
                'id' => (string)Str::uuid(),
                'name' => $request->$id_name,
                'acquired_date' => $request->$acquired_date,
                'expiration_date' => $request->$expiration_date,
                'link' => $base_path,
                'user_id' => $user->id,
            ]);
        }
        $tutor_identity = TutorsIdentities::where('user_id', $user->id)->get();


        for ($i_quali=1; $i_quali <= $request->total_qualifications; $i_quali++) {

            $university = "university_".$i_quali;
            $course = "course_".$i_quali;
            $country = "country_".$i_quali;
            $date_of_graduation = "date_of_graduation_".$i_quali;
            $grade = "grade_".$i_quali;
            $degree = "degree_".$i_quali;
            $qualification_link = "qualification_link_".$i_quali;


            $file = $request->file($qualification_link);
            $name=time().$file->getClientOriginalName();
            $filePath = 'images/' . $name;
            $disk = Storage::disk('s3');
            $disk->put($filePath, file_get_contents($file));
            $base_path = $disk->url($filePath);

            TutorsQualification::create([
                'id' => (string)Str::uuid(),
                'university' => $request->$university,
                'course' => $request->$course,
                'country' => $request->$country,
                'date_of_graduation' => $request->$date_of_graduation,
                'grade' => $request->$grade,
                'degree' => $request->$degree,
                'link' => $base_path,
                'user_id' => $user->id
            ]);
        }
        $tutor_qualification = TutorsQualification::where('user_id', $user->id)->get();


        for ($i_sub=1; $i_sub <= $request->total_subjects; $i_sub++) {

            $hours_per_week = "hours_per_week_".$i_sub;
            $level_id = "level_id_".$i_sub;
            $subject_id = "subject_id_".$i_sub;

            TutorSubject::create([
                'id' => (string)Str::uuid(),
                'hours_per_week' => $request->$hours_per_week,
                'user_id' => $user->id,
                'level_id' => $request->$level_id,
                'subject_id' => $request->$subject_id,
            ]);
        }
        $tutor_subject = TutorSubject::where('user_id', $user->id)->get();

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'User signed up successfully',
                'data' => [
                    'user' => $user,
                    'tutor' => $tutor,
                    'tutor_experience' => $tutor_experience,
                    'tutor_certification' => $tutor_certification,
                    'tutor_identity' => $tutor_identity,
                    'tutor_qualification' => $tutor_qualification,
                    'tutor_subjects' => $tutor_subject,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

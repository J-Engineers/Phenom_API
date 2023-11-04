<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonSubject;
use App\Models\Tutor;
use App\Models\TutorExperience;
use App\Models\TutorsCertification;
use App\Models\TutorsIdentities;
use App\Models\TutorsQualification;
use App\Models\TutorSubject;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorController extends Controller
{
    //
    public function getTutors(Request $request){


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

        $users = DB::table('tutors as t')
        ->leftJoin('users As u', function($join){
            $join->on('t.user_id', '=', 'u.id');
        })
        ->leftJoin('lesson_subjects as ls', function($join){
            $join->on('ls.tutor_id', '=', 'u.id');
        })
        ->select(
            't.user_id as user_id',
            'u.email as tutor_email',
            'ls.learner_lesson_status as assigned_status',
        )
        ->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutors Found',
            'total_tutors' => count($users),
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function getTutorDetails(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string',
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

        $tutor_user = User::where('id' , $request->user_id)->first();
        $tutor = Tutor::where('user_id' , $request->user_id)->first();
        if(!$tutor_user or !$tutor){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User not found or not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->user_id],
            ]
        )->select('id')->first();

        $tutor_experience = TutorExperience::where('user_id', $request->user_id)->get();
        $tutor_certification = TutorsCertification::where('user_id', $request->user_id)->get();
        $tutor_identity = TutorsIdentities::where('user_id', $request->user_id)->get();
        $tutor_qualification = TutorsQualification::where('user_id', $request->user_id)->get();
        $tutor_subject = TutorSubject::where('user_id', $request->user_id)->get();
        $tutor_lesson = LessonSubject::where('tutor_id', $request_tutor_user->id)->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutors Details Found',
            'data' => [
                'user' => $tutor_user,
                'tutor' => $tutor,
                'tutor_experience' => $tutor_experience,
                'tutor_certification' => $tutor_certification,
                'tutor_identity' => $tutor_identity,
                'tutor_qualification' => $tutor_qualification,
                'tutor_subjects' => $tutor_subject,
                'lessons' => $tutor_lesson,
            ]
        ], Response::HTTP_OK);
    }

    public function approveTutor(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string',
            'comment' => 'required|string',
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

        $tutor_user = User::where('id' , $request->user_id)->first();
        if(!$tutor_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $tutor = Tutor::where('user_id' , $request->user_id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $tutor->update(
            [
                'status' => true,
                'comment' => $request->comment
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutors Details Updated',
            'data' => [
                'user' => $tutor_user,
                'tutor' => $tutor
            ]
        ], Response::HTTP_OK);
    }

    public function declineTutor(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string',
            'comment' => 'required|string',
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

        $tutor_user = User::where('id' , $request->user_id)->first();
        if(!$tutor_user){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $tutor = Tutor::where('user_id' , $request->user_id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'User not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $tutor->update(
            [
                'status' => false,
                'comment' => $request->comment
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutors Details Updated',
            'data' => [
                'user' => $tutor_user,
                'tutor' => $tutor
            ]
        ], Response::HTTP_OK);
    }
    
    public function searchTutor(Request $request){


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
                ['user_type' , '=', 'tutor']
            ]
        )
        ->orWhere(
            [
                ['email' , 'LIKE', $filter],
                ['user_type' , '=', 'tutor'],
            ]
        )
        ->select(
            'u.email as tutor_email', 
            'u.first_name as tutor_firstname',  
            'u.last_name as tutor_lastname', 
            'u.phone as tutor_contact', 
            'u.id as tutor_id',
        )
        ->get();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Search Result',
            'data' => [
                'result' => $tutor_user,
            ]
        ], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

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
        ->leftJoin('lesson_subjects as ls', function($join){
            $join->on('ls.parent_id', '=', 'u.id');
        })
        ->select(
            'u.id as user_id',
            'u.email as parent_email',
            'ls.learner_lesson_status as assigned_status',
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

        $my_lesson = DB::table('learners As l')
        ->leftJoin('lessons As ls', function($join){
            $join->on('l.id', '=', 'ls.learner_id');
            $join->on('l.parent_id', '=', 'ls.parent_id');
        })
        ->leftJoin('lesson_subjects As lss', function($join){
            $join->on('l.id', '=', 'lss.learner_id');
            $join->on('l.parent_id', '=', 'lss.parent_id');
            $join->on('ls.id', '=', 'lss.learner_lesson_id');
        })
        ->leftJoin('users As u', function($join){
            $join->on('lss.tutor_id', '=', 'u.id');
        })
        ->leftJoin('subjects As s', function($join){
            $join->on('lss.education_level_subject_id', '=', 's.id');
        })
        ->leftJoin('lesson_subjects_timetable As lsst', function($join){
            $join->on('l.id', '=', 'lsst.learner_id');
            $join->on('l.parent_id', '=', 'lsst.parent_id');
            $join->on('ls.id', '=', 'lsst.learner_lesson_id');
            $join->on('lss.id', '=', 'lsst.learner_lesson_subject_id');
        })
        ->leftJoin('lesson_day As ld', function($join){
            $join->on('lsst.lesson_day_id', '=', 'ld.id');
        })
        ->where('l.parent_id', '=', $parent->id)
        ->select('l.learners_name', 'l.learners_dob as learners_date_of_birth', 'l.learners_gender', 'l.id as learners_id',
            'ls.lesson_address', 'ls.lesson_goals', 'ls.lesson_mode', 'ls.lesson_period', 'ls.description_of_learner as learners_description', 'ls.id as lesson_id',
            'lss.learner_lesson_tutor_gender as tutors_gender', 'lss.learner_lesson_tutor_type as tutors_type', 'lss.learner_lesson_status as learners_status', 'lss.tutor_lesson_status as tutors_status','lss.id as lesson_subject_id',
            'u.first_name as tutors_firstname', 'u.last_name as tutors_lastname',
            's.name as subject_name', 'lss.id as lesson_subject_id',
            'ld.day_name as lesson_day', 'lsst.lesson_day_hours', 'lsst.lesson_day_start_time',
        
        )
        ->get();

        $cv = 0;
        $completed_lessons = [];
        $pending_lesson = [];
        $feedbacks = [];
        foreach ($my_lesson as $my_lesson_value) {

            $feedback = DB::table('lesson_feedback as lf')
            ->leftJoin('users As u', function($join){
                $join->on('u.id', '=', 'lf.user_id');
            })
           
            ->where(
                [
                    ['lf.learner_lesson_id', '=', $my_lesson_value->lesson_id],
                ]
            )->select(
                'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                'lf.feedback', 'lf.id as feedback_id',
            )->get();
            foreach($feedback as $feedback_value){
                $packey = [];
                $packey['feedback'] = $feedback_value;
                $feedbacks_reply = DB::table('lesson_feedback_reply as lfr')
                ->leftJoin('users As u', function($join){
                    $join->on('u.id', '=', 'lfr.user_id');
                })
                ->where(
                    [
                        ['lfr.lesson_feedback_id', '=',  $feedback_value->feedback_id],
                    ]
                )->select(
                    'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                    'lfr.response_reply', 'lfr.id as feedback_reply_id',
                )->get();

                $packey['reply'] = $feedbacks_reply;
                array_push($feedbacks, $packey);
            }

            if(!$my_lesson_value->learners_status == 'completed'){
                $cv += 1;
                array_push($pending_lesson, $my_lesson_value);
                
            }else{
                array_push($completed_lessons, $my_lesson_value);
            }
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'View Lesson',
            'data' => [
                'parent' => $user_parent,
                'pending' => $cv,
                'pending_lessons' => $pending_lesson,
                'completed_lessons' => $completed_lessons,
                'feedbacks' => $feedbacks,
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
            'u.id as parent_id',
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

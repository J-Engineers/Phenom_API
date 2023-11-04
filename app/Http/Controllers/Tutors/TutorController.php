<?php

namespace App\Http\Controllers\Tutors;

use App\Models\Learner;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LessonFeedback;
use App\Models\LessonFeedbackReply;
use App\Models\LessonSubject;
use App\Models\Tutor;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TutorController extends Controller
{
    

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
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $user_tutor = DB::table('users as u')
        ->leftJoin('tutors as t', function ($join){
            $join->on('u.id', '=', 't.user_id');
        })
        ->where('u.id', $auth->id)->get();

        $tutor = Tutor::where('user_id', $auth->id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a tutor',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $my_lesson = DB::table('lesson_subjects As l')
        ->leftJoin('lessons As ls', function($join){
            $join->on('ls.id', '=', 'l.learner_lesson_id');
        })
        ->leftJoin('learners As lss', function($join){
            $join->on('lss.id', '=', 'ls.learner_id');
            $join->on('lss.parent_id', '=', 'ls.parent_id');
            $join->on('lss.id', '=', 'l.learner_lesson_id');
        })
        ->leftJoin('users As u', function($join){
            $join->on('l.tutor_id', '=', 'u.id');
        })
        ->leftJoin('subjects As s', function($join){
            $join->on('l.education_level_subject_id', '=', 's.id');
        })
        ->leftJoin('lesson_subjects_timetable As lsst', function($join){
            $join->on('l.learner_id', '=', 'lsst.learner_id');
            $join->on('l.parent_id', '=', 'lsst.parent_id');
            $join->on('ls.id', '=', 'lsst.learner_lesson_id');
            $join->on('l.id', '=', 'lsst.learner_lesson_subject_id');
        })
        ->leftJoin('lesson_day As ld', function($join){
            $join->on('lsst.lesson_day_id', '=', 'ld.id');
        })
        ->where('l.tutor_id', '=', $tutor->id)
        ->select('lss.learners_name', 'lss.learners_dob as learners_date_of_birth', 'lss.learners_gender', 'lss.id as learners_id',
            'ls.lesson_address', 'ls.lesson_goals', 'ls.lesson_mode', 'ls.lesson_period', 'ls.description_of_learner as learners_description', 'ls.id as lesson_id',
            'l.learner_lesson_tutor_gender as tutors_gender', 'l.learner_lesson_tutor_type as tutors_type', 'l.learner_lesson_status as learners_status', 'l.tutor_lesson_status as tutors_status',
            'u.first_name as tutors_firstname', 'u.last_name as tutors_lastname',
            's.name as subject_name', 'l.id as lesson_subject_id',
            'ld.day_name as lesson_day', 'lsst.lesson_day_hours', 'lsst.lesson_day_start_time',
        )
        ->get();

        $cv = 0;
        $completed_lessons = [];
        $pending_lesson = [];
        $feedbacks = [];
        foreach ($my_lesson as $my_lesson_value) {
            if(!$my_lesson_value->learners_status == 'completed'){
                $cv += 1;
                array_push($pending_lesson, $my_lesson_value);

                $feedback = DB::table('lesson_feedback as lf')
                ->leftJoin('users As u', function($join){
                    $join->on('u.id', '=', 'lf.user_id');
                })
            
                ->where(
                    [
                        ['lf.learner_lesson_id', '=', $my_lesson_value->lesson_id],
                        ['lf.parent_tutor', '=', 'parent'],
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
                            ['lf.parent_tutor_admin', '=', 'admin'],
                        ]
                    )->select(
                        'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                        'lfr.response_reply', 'lfr.id as feedback_reply_id',
                    )->get();

                    $packey['reply'] = $feedbacks_reply;
                    array_push($feedbacks, $packey);
                }
                
            }else{
                array_push($completed_lessons, $my_lesson_value);
            }
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Dashboard',
            'data' => [
                'Tutor' => $user_tutor,
                'pending' => $cv,
                'pending_lessons' => $pending_lesson,
                'completed_lessons' => $completed_lessons,
                'feedbacks' => $feedbacks,
            ]
        ], Response::HTTP_OK);
    }

    public function lesson(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'learners_id' => 'required|string|exists:learners,id',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id'
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $user_tutor = DB::table('users as u')
        ->leftJoin('tutors as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $auth->id)->get();

        $tutor = Tutor::where('user_id', $auth->id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a Tutor',
            ], Response::HTTP_NOT_FOUND); // 404
        }


        $my_lesson = DB::table('lesson_subjects As l')
        ->leftJoin('lessons As ls', function($join){
            $join->on('ls.id', '=', 'l.learner_lesson_id');
        })
        ->leftJoin('learners As lss', function($join){
            $join->on('lss.id', '=', 'ls.learner_id');
            $join->on('lss.parent_id', '=', 'ls.parent_id');
            $join->on('lss.id', '=', 'l.learner_lesson_id');
        })
        ->leftJoin('users As u', function($join){
            $join->on('l.tutor_id', '=', 'u.id');
        })
        ->leftJoin('subjects As s', function($join){
            $join->on('l.education_level_subject_id', '=', 's.id');
        })
        ->leftJoin('lesson_subjects_timetable As lsst', function($join){
            $join->on('l.learner_id', '=', 'lsst.learner_id');
            $join->on('l.parent_id', '=', 'lsst.parent_id');
            $join->on('ls.id', '=', 'lsst.learner_lesson_id');
            $join->on('l.id', '=', 'lsst.learner_lesson_subject_id');
        })
        ->leftJoin('lesson_day As ld', function($join){
            $join->on('lsst.lesson_day_id', '=', 'ld.id');
        })
        ->where(
            [
                ['lss.id', '=', $request->learners_id],
                ['ls.id', '=', $request->lesson_id],
            ]
        )
        ->where('l.tutor_id', '=', $tutor->id)
        ->select('lss.learners_name', 'lss.learners_dob as learners_date_of_birth', 'lss.learners_gender', 'lss.id as learners_id',
            'ls.lesson_address', 'ls.lesson_goals', 'ls.lesson_mode', 'ls.lesson_period', 'ls.description_of_learner as learners_description', 'ls.id as lesson_id',
            'l.learner_lesson_tutor_gender as tutors_gender', 'l.learner_lesson_tutor_type as tutors_type', 'l.learner_lesson_status as learners_status', 'l.tutor_lesson_status as tutors_status',
            'u.first_name as tutors_firstname', 'u.last_name as tutors_lastname',
            's.name as subject_name', 'l.id as lesson_subject_id',
            'ld.day_name as lesson_day', 'lsst.lesson_day_hours', 'lsst.lesson_day_start_time',
        )
        ->get();


        $lesson = [];
        $feedbacks = [];
        foreach ($my_lesson as $my_lesson_value) {
         
            array_push($lesson, $my_lesson_value);

            $feedback = DB::table('lesson_feedback as lf')
            ->leftJoin('users As u', function($join){
                $join->on('u.id', '=', 'lf.user_id');
            })
        
            ->where(
                [
                    ['lf.learner_lesson_id', '=', $my_lesson_value->lesson_id],
                    ['lf.parent_tutor', '=', 'tutor'],
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
                        ['lf.parent_tutor_admin', '=', 'admin'],
                    ]
                )->select(
                    'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                    'lfr.response_reply', 'lfr.id as feedback_reply_id',
                )->get();

                $packey['reply'] = $feedbacks_reply;
                array_push($feedbacks, $packey);
            }
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Learner Lesson',
            'data' => [
                'Tutor' => $user_tutor,
                'lesson' => $lesson,
                'feedbacks' => $feedbacks,
            ]
        ], Response::HTTP_OK);
    }

    public function feedback(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'feedback' => 'required|string',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        LessonFeedback::create(
            [
                'learner_lesson_id' => $request->lesson_id,
                'learner_lesson_subject_id' => $request->lesson_subject_id,
                'parent_tutor' => 'tutor',
                'user_id' => $auth->id,
                'feedback' => $request->feedback,
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Add Feedback',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function feedback_reply(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'feedback_id' => 'required|string|exists:lesson_feedback,id',
            'feedback_reply' => 'required|string',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        LessonFeedbackReply::create(
            [
                'lesson_feedback_id' => $request->feedback_id,
                'parent_tutor_admin' => 'tutor',
                'user_id' => $auth->id,
                'response_reply' => $request->feedback_reply,
                'response_status' => '0',
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Add Feedback Reply',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function complete_lesson(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $tutor = Tutor::where('user_id', $auth->id)->first();
        $lesson = LessonSubject::where(
            [
                ['tutor_id', '=', $tutor->id],
                ['id', '=', $request->lesson_subject_id],
            ]
        )->first();

        if(!$lesson){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => "Lesson not found",
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $lesson->update(
            [ 'tutor_lesson_status' => 'completed']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Marked lesson completed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }
}

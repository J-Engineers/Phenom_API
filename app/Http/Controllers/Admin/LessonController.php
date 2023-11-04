<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Learner;
use App\Models\LessonFeedback;
use App\Models\LessonFeedbackReply;
use App\Models\Lessons;
use App\Models\LessonSubject;
use App\Models\LessonSubjectTimetable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ParentUser;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LessonController extends Controller
{
    

    public function lesson(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'learners_id' => 'required|string|exists:learners,id',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'user_id' => 'required|string|exists:users,id'
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
        
        $request_user = User::where('id', '=', $request->user_id)->first();
        if(!($request_user->user_type == 'parent')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'This is not a parent',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $user_parent = DB::table('users as u')
        ->leftJoin('parent_user as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $request->user_id)->get();

        

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
            ->where(
                [
                    ['l.id', '=', $request->learners_id],
                    ['ls.id', '=', $request->lesson_id],
                ]
            )
            ->select('l.learners_name', 'l.learners_dob as learners_date_of_birth', 'l.learners_gender', 'l.id as learners_id',
                'ls.lesson_address', 'ls.lesson_goals', 'ls.lesson_mode', 'ls.lesson_period', 'ls.description_of_learner as learners_description', 'ls.id as lesson_id',
                'lss.learner_lesson_tutor_gender as tutors_gender', 'lss.learner_lesson_tutor_type as tutors_type', 'lss.learner_lesson_status as learners_status', 'lss.tutor_lesson_status as tutors_status',
                'u.first_name as tutors_firstname', 'u.last_name as tutors_lastname',
                's.name as subject_name', 'lss.id as lesson_subject_id',
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
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Learner Lesson',
            'data' => [
                'parent' => $user_parent,
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
            'user_id' => 'required|string|exists:users,id'
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
        
       
        LessonFeedback::create(
            [
                'learner_lesson_id' => $request->lesson_id,
                'learner_lesson_subject_id' => $request->lesson_subject_id,
                'parent_tutor' => 'admin',
                'user_id' => $user->id,
                'feedback' => $request->feedback,
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin Add Feedback',
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
            'user_id' => 'required|string|exists:users,id'
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
        
    
        LessonFeedbackReply::create(
            [
                'lesson_feedback_id' => $request->feedback_id,
                'parent_tutor_admin' => 'admin',
                'user_id' => $user->id,
                'response_reply' => $request->feedback_reply,
                'response_status' => '0',
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin Add Feedback Reply',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function complete_lesson(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'user_id' => 'required|string|exists:users,id'
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
        
        $request_user = User::where('id', '=', $request->user_id)->first();
        if(!($request_user->user_type == 'parent')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $parent = ParentUser::where('user_id', $request->user_id)->first();
        $lesson = LessonSubject::where(
            [
                ['parent_id', '=', $parent->id],
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
            [ 'learner_lesson_status' => 'completed'],
            [ 'tutor_lesson_status' => 'completed']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin Marked lesson completed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function add_lesson(Request $request){

        $fields = Validator::make($request->all(), [

            'learners_name' => 'required|string',
            'learners_dob' => 'required|string',
            'learners_gender' => 'required|string',

            'lesson_address' => 'required|string',
            'lesson_goals' => 'required|string',
            'lesson_mode' => 'required|string',
            'lesson_period' => 'required|string',
            'description_of_learner' => 'required|string',
            'education_level_id' => 'required|string|exists:education_levels,id',
            'user_id' => 'required|string|exists:users,id',

           
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

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorizeds'
            ], Response::HTTP_NOT_FOUND);
        }

        $request_user = User::where('id', '=', $request->user_id)->first();
        $parent = ParentUser::where('user_id', $request_user->id)->first();

        if(!($request_user->user_type == 'parent')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
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

        $learner = Learner::create([
            'uuid' => (string)Str::uuid(),
            'parent_id' => $parent->id,
            'learners_name' => $request->learners_name,
            'learners_dob' => $request->learners_dob,
            'learners_gender' => $request->learners_gender,
        ]);

        $lesson = Lessons::create([
            'uuid' => (string)Str::uuid(),
            'parent_id' => $parent->id,
            'learner_id' => $learner->id,
            'lesson_address' => $request->lesson_address,
            'lesson_goals' => $request->lesson_goals,
            'lesson_mode' => $request->lesson_mode,
            'lesson_period' => $request->lesson_period,
            'description_of_learner' => $request->description_of_learner,
            'education_level_id' => $request->education_level_id,
        ]);

        for ($i_subjects=1; $i_subjects <= $request->total_subjects; $i_subjects++) {

            $subject_id = "subject_id_".$i_subjects;
            $tutor_gender = "tutor_gender_".$i_subjects;
            $tutor_type = "tutor_type_".$i_subjects;

            $total_days = "total_day_".$i_subjects;

            
            $education_level = LessonSubject::create([
                'uuid' => (string)Str::uuid(),
                'parent_id' => $parent->id,
                'learner_id' => $learner->id,
                'learner_lesson_id' => $lesson->id,
                'education_level_id' => $request->education_level_id,
                'education_level_subject_id' => $request->$subject_id,
                'learner_lesson_tutor_gender' => $request->$tutor_gender,
                'learner_lesson_tutor_type' => $request->$tutor_type,
            ]);

            for ($i_total_day=1; $i_total_day <= $request->$total_days; $i_total_day++) {


                $day_id = "day_id_".$i_total_day.'_'.$i_subjects;
                $day_hours = "day_hours_".$i_total_day.'_'.$i_subjects;
                $start_time = "start_time_".$i_total_day.'_'.$i_subjects;

                LessonSubjectTimetable::create([
                    'uuid' => (string)Str::uuid(),
                    'parent_id' => $parent->id,
                    'learner_id' => $learner->id,
                    'learner_lesson_id' => $lesson->id,
                    'learner_lesson_subject_id' => $education_level->id,
                    'lesson_day_id' => $request->$day_id,
                    'lesson_day_hours' => $request->$day_hours,
                    'lesson_day_start_time' => $request->$start_time,
                ]);
            }
        }

        $learner_subject = DB::table('lesson_subjects As subject')
        ->leftJoin('lesson_subjects_timetable As timetable', function($join){
            $join->on('subject.id', '=', 'timetable.learner_lesson_subject_id');
            $join->on('subject.parent_id', '=', 'timetable.parent_id');
            $join->on('subject.learner_id', '=', 'timetable.learner_id');
            $join->on('subject.learner_lesson_id', '=', 'timetable.learner_lesson_id');
        })
        ->where('subject.parent_id', '=', $parent->id)
        ->where('subject.learner_id', '=', $learner->id)
        ->where('subject.learner_lesson_id', '=', $lesson->id)
        ->where('subject.education_level_id', '=', $request->education_level_id)
        ->get();

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Created a lesson',
                'data' => [
                    'learner' => $learner,
                    'lesson' => $lesson,
                    'subjects' => $learner_subject
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function remove_lesson(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'user_id' => 'required|string|exists:users,id'

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
        
       
        $parent = ParentUser::where('user_id', $request->user_id)->first();
        $lesson = LessonSubject::where(
            [
                ['parent_id', '=', $parent->id],
                ['id', '=', $request->lesson_subject_id],
            ]
        )->first();

        if(!$lesson){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Lesson not found',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $lesson->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin removed a lesson',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function add_tutor(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'parent_id' => 'required|string|exists:users,id',
            'tutor_id' => 'required|string|exists:users,id'
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
        
        $request_user = User::where(
            [
                ['id', '=', $request->tutor_id],
            ]
        )->first();
        if(!($request_user->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $request_parent_user = ParentUser::where(
            [
                ['user_id', '=', $request->parent_id],
            ]
        )->select('id')->first();


        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->tutor_id],
            ]
        )->select('id')->first();

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
                ['learner_lesson_id', '=', $request->lesson_id],
                ['parent_id', '=', $request_parent_user->id],
            ]
        )->first();

        if(!$lesson){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        if(strlen($lesson->tutor_id) > 0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Lesson Has be assigned to another person'
            ], Response::HTTP_NOT_FOUND);
        }

        $lesson->update(
            ['tutor_id' => $request_tutor_user->id]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin Added a tutor to this lesson request',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function remove_tutor(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'parent_id' => 'required|string|exists:users,id',
            'tutor_id' => 'required|string|exists:users,id'
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
        
        $request_user = User::where(
            [
                ['id', '=', $request->tutor_id],
            ]
        )->first();
        if(!($request_user->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $request_parent_user = ParentUser::where(
            [
                ['user_id', '=', $request->parent_id],
            ]
        )->select('id')->first();


        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->tutor_id],
            ]
        )->select('id')->first();

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
                ['learner_lesson_id', '=', $request->lesson_id],
                ['parent_id', '=', $request_parent_user->id],
            ]
        )->first();

        if(!$lesson){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $lesson->update(
            ['tutor_id' => null]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin removed a tutor from this lesson request',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function replace_tutor(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_id' => 'required|string|exists:lessons,id',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
            'parent_id' => 'required|string|exists:users,id',
            'tutor_id' => 'required|string|exists:users,id'
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
        
        $request_user = User::where(
            [
                ['id', '=', $request->tutor_id],
            ]
        )->first();
        if(!($request_user->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Not a tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $request_parent_user = ParentUser::where(
            [
                ['user_id', '=', $request->parent_id],
            ]
        )->select('id')->first();


        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->tutor_id],
            ]
        )->select('id')->first();

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
                ['learner_lesson_id', '=', $request->lesson_id],
                ['parent_id', '=', $request_parent_user->id],
            ]
        )->first();

        if(!$lesson){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

       

        $lesson->update(
            ['tutor_id' => $request_tutor_user->id]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Admin replaced a tutor on this lesson request',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }
}

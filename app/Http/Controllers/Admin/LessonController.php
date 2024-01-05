<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Tutor;
use App\Helpers\Lesson;
use App\Models\Learner;
use App\Models\Lessons;
use App\Models\ParentUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LessonLearner;
use App\Models\LessonSubject;
use App\Models\LessonFeedback;
use Illuminate\Support\Facades\DB;
use App\Models\LessonFeedbackReply;
use App\Http\Controllers\Controller;
use App\Models\LessonSubjectTimetable;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LessonController extends Controller
{
    

    public function lessons(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string'
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
        
        
        // $learners = Lesson::lesson($parent->id, $request->lesson_subject_timetable_id);
        $padded = [];
        $tutor_parent_data = [];
        $query = DB::table('lesson_subjects')
        ->select('subject_id', 'tutor_status', 'learner_status', 'lesson_learner_id', 'id', 'tutor_id')
        ->get();
        if($query){
            foreach($query as $subject){
                $subject_id = $subject->subject_id;
                $tutor_status = $subject->tutor_status;
                $learner_status = $subject->learner_status;
                $lesson_learner_id = $subject->lesson_learner_id;
                $lesson_subject_id = $subject->id;
                $lesson_tutor_id = $subject->tutor_id;
                $lesson_tutor_name =  "";
                $lesson_tutor_email =  "";
                $tutor_user_id = '';

                $query7 = Tutor::where('id', $lesson_tutor_id)->first();
                if($query7){
                    $query8 = User::where('id', $query7->user_id)->first();
                    if($query8){
                        $lesson_tutor_email = $query8->email;
                        $lesson_tutor_name = $query8->first_name. " ".$query8->last_name;
                        $tutor_user_id = $query7->user_id;

                    }
                }

                $query1 = DB::table('subjects')
                ->where(
                    [
                        ['id', '=', $subject_id],
                    ]
                )
                ->first();
                if($query1){
                    $status = 'pending';
                    if($tutor_status == 'completed' && $learner_status == 'completed' ){
                        $status = 'completed';
                    }
                    $query2 = DB::table('lesson_learner')
                    ->where(
                        [
                            ['id', '=', $lesson_learner_id],
                        ]
                    )
                    ->select('lesson_id as lesson_id', 'learner_id as learner_id')
                    ->get();
                    if($query2){
                        foreach($query2 as $learner){
                            $learner_id = $learner->learner_id;
                            $lesson_id = $learner->lesson_id;


                            $query3 = DB::table('learners')
                            ->where(
                                [
                                    ['id', '=', $learner_id]
                                ]
                            )
                            ->first();
                            if($query3){
                                $learner_id = $query3->id;
                                $learner_name = $query3->learners_name;
                                $learner_dob = $query3->learners_dob;
                                $learner_gender = $query3->learners_gender;
                            }

                            $query4 = DB::table('lessons')
                            ->where(
                                [
                                    ['id', '=', $lesson_id],
                                ]
                            )
                            ->first();
                            if($query4){
                                $lesson_address = $query4->lesson_address;
                                $lesson_goal = $query4->lesson_goals;
                                $lesson_mode = $query4->lesson_mode;
                                $lesson_period = $query4->lesson_period;
                                $lesson_perent_id = $query4->parent_id;

                                $query5 = ParentUser::where('id', $lesson_perent_id)->first();
                                $query6 = User::where('id', $query5->user_id)->first();
                                $parent_email = $query6->email;
                                $parent_user_id = $query6->id;

                                $padded['lesson_details'] = array(
                                    'parent_email' => $parent_email,
                                    'parent_user_id' => $parent_user_id,
                                    'lesson_address' => $lesson_address,
                                    'lesson_goal' => $lesson_goal,
                                    'lesson_mode' => $lesson_mode,
                                    'lesson_period' => $lesson_period,
                                    'lesson_subject' => $query1->name,
                                    'lesson_subject_id' => $lesson_subject_id,
                                    
                                );

                                $padded['learner_details'] = array(
                                    'learner_id' => $learner_id,
                                    'learner_name' => $learner_name,
                                    'learner_dob' => $learner_dob,
                                    'learner_gender' => $learner_gender,
                                    'lesson_completed' => $status,
                                );

                                $padded['tutor_details'] = array(
                                    'lesson_tutor_email' => $lesson_tutor_email,
                                    'lesson_tutor_name' => $lesson_tutor_name,
                                    'tutor_user_id' => $tutor_user_id
                                );

                                $padded['lesson_period'] = []; 

                                $query5 = DB::table('lesson_subjects_timetable')
                                ->where(
                                    [
                                        ['lesson_subject_id', '=', $lesson_subject_id],
                                    ]
                                )
                                ->get();
                                if($query5){
                                    foreach($query5 as $timetable){
                                        $lesson_day_id = $timetable->lesson_day_id;
                                        $lesson_day_hours = $timetable->lesson_day_hours;
                                        $lesson_day_start_time = $timetable->lesson_day_start_time;
                                        $lesson_timetable_day_id = $timetable->id;


                                        $query6 = DB::table('lesson_day')
                                        ->where(
                                            [
                                                ['id', '=', $lesson_day_id],
                                            ]
                                        )
                                        ->first();
                                        if($query6){
                                            $day_name = $query6->day_name;
                                            $data_time = array( "day" => $day_name, "starts_by" => $lesson_day_start_time, "duration" => $lesson_day_hours, "timetable_id" => $lesson_timetable_day_id);
                                            array_push($padded['lesson_period'], $data_time);
                                        }
                                    }

                                }
                            }

                            $feedback = DB::table('lesson_feedback as lf')
                            ->leftJoin('users As u', function($join){
                                $join->on('u.id', '=', 'lf.user_id');
                            })
                        
                            ->where(
                                [
                                    ['lf.lesson_subject_id', '=', $lesson_subject_id],
                                ]
                            )->select(
                                'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                                'lf.feedback', 'lf.id as feedback_id',
                            )->get();

                            $feedbacks = [];
            
                            foreach($feedback as $feedback_value){
                                $packey = [];
                                $packey['feedback'] = $feedback_value;
                                $feedbacks_reply = DB::table('lesson_feedback_reply as lfr')
                                ->leftJoin('users As u', function($join){
                                    $join->on('u.id', '=', 'lfr.user_id');
                                })
                                ->where(
                                    [
                                        ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                                    ]
                                )
                                ->orWhere(
                                    [
                                        ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                                    ]
                                )
                                ->select(
                                    'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                                    'lfr.response_reply', 'lfr.id as feedback_reply_id',
                                )->get();
                
                                $packey['reply'] = $feedbacks_reply;
                                array_push($feedbacks, $packey);
                            }
                            $padded['learner_feedbacks'] = $feedbacks;
                            array_push($tutor_parent_data, $padded);
                        }
                    }
                }
            }
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Lessons',
            'data' => [
                'lesson' => $tutor_parent_data
            ]
        ], Response::HTTP_OK);
    }

    public function lesson(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id'
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
        

        
        // $learners = Lesson::lesson($parent->id, $request->lesson_subject_timetable_id);
        $padded = [];
        $tutor_parent_data = [];
        $query = DB::table('lesson_subjects')
        ->select('subject_id', 'tutor_status', 'learner_status', 'lesson_learner_id', 'id', 'tutor_id')
        ->where(
            [
                ['id', '=', $request->lesson_subject_id]
            ]
        )
        ->get();
        if($query){
            foreach($query as $subject){
                $subject_id = $subject->subject_id;
                $tutor_status = $subject->tutor_status;
                $learner_status = $subject->learner_status;
                $lesson_learner_id = $subject->lesson_learner_id;
                $lesson_subject_id = $subject->id;
                $lesson_tutor_id = $subject->tutor_id;
                $lesson_tutor_name =  "";
                $lesson_tutor_email =  "";
                $tutor_user_id = '';

                $query7 = Tutor::where('id', $lesson_tutor_id)->first();
                if($query7){
                    $query8 = User::where('id', $query7->user_id)->first();
                    if($query8){
                        $lesson_tutor_email = $query8->email;
                        $lesson_tutor_name = $query8->first_name. " ".$query8->last_name;
                        $tutor_user_id = $query7->user_id;

                    }
                }

                $query1 = DB::table('subjects')
                ->where(
                    [
                        ['id', '=', $subject_id],
                    ]
                )
                ->first();
                if($query1){
                    $status = 'pending';
                    if($tutor_status == 'completed' && $learner_status == 'completed' ){
                        $status = 'completed';
                    }
                    $query2 = DB::table('lesson_learner')
                    ->where(
                        [
                            ['id', '=', $lesson_learner_id],
                        ]
                    )
                    ->select('lesson_id as lesson_id', 'learner_id as learner_id')
                    ->get();
                    if($query2){
                        foreach($query2 as $learner){
                            $learner_id = $learner->learner_id;
                            $lesson_id = $learner->lesson_id;


                            $query3 = DB::table('learners')
                            ->where(
                                [
                                    ['id', '=', $learner_id]
                                ]
                            )
                            ->first();
                            if($query3){
                                $learner_id = $query3->id;
                                $learner_name = $query3->learners_name;
                                $learner_dob = $query3->learners_dob;
                                $learner_gender = $query3->learners_gender;
                            }

                            $query4 = DB::table('lessons')
                            ->where(
                                [
                                    ['id', '=', $lesson_id],
                                ]
                            )
                            ->first();
                            if($query4){
                                $lesson_address = $query4->lesson_address;
                                $lesson_goal = $query4->lesson_goals;
                                $lesson_mode = $query4->lesson_mode;
                                $lesson_period = $query4->lesson_period;
                                $lesson_perent_id = $query4->parent_id;

                                $query5 = ParentUser::where('id', $lesson_perent_id)->first();
                                $query6 = User::where('id', $query5->user_id)->first();
                                $parent_email = $query6->email;
                                $parent_user_id = $query6->id;

                                $padded['lesson_details'] = array(
                                    'parent_email' => $parent_email,
                                    'parent_user_id' => $parent_user_id,
                                    'lesson_address' => $lesson_address,
                                    'lesson_goal' => $lesson_goal,
                                    'lesson_mode' => $lesson_mode,
                                    'lesson_period' => $lesson_period,
                                    'lesson_subject' => $query1->name,
                                    'lesson_subject_id' => $lesson_subject_id,
                                    
                                );

                                $padded['learner_details'] = array(
                                    'learner_id' => $learner_id,
                                    'learner_name' => $learner_name,
                                    'learner_dob' => $learner_dob,
                                    'learner_gender' => $learner_gender,
                                    'lesson_completed' => $status,
                                );

                                $padded['tutor_details'] = array(
                                    'lesson_tutor_email' => $lesson_tutor_email,
                                    'lesson_tutor_name' => $lesson_tutor_name,
                                    'tutor_user_id' => $tutor_user_id
                                );

                                $padded['lesson_period'] = []; 

                                $query5 = DB::table('lesson_subjects_timetable')
                                ->where(
                                    [
                                        ['lesson_subject_id', '=', $lesson_subject_id],
                                    ]
                                )
                                ->get();
                                if($query5){
                                    foreach($query5 as $timetable){
                                        $lesson_day_id = $timetable->lesson_day_id;
                                        $lesson_day_hours = $timetable->lesson_day_hours;
                                        $lesson_day_start_time = $timetable->lesson_day_start_time;
                                        $lesson_timetable_day_id = $timetable->id;


                                        $query6 = DB::table('lesson_day')
                                        ->where(
                                            [
                                                ['id', '=', $lesson_day_id],
                                            ]
                                        )
                                        ->first();
                                        if($query6){
                                            $day_name = $query6->day_name;
                                            $data_time = array( "day" => $day_name, "starts_by" => $lesson_day_start_time, "duration" => $lesson_day_hours, "timetable_id" => $lesson_timetable_day_id);
                                            array_push($padded['lesson_period'], $data_time);
                                        }
                                    }

                                }
                            }

                            $feedback = DB::table('lesson_feedback as lf')
                            ->leftJoin('users As u', function($join){
                                $join->on('u.id', '=', 'lf.user_id');
                            })
                        
                            ->where(
                                [
                                    ['lf.lesson_subject_id', '=', $lesson_subject_id],
                                ]
                            )->select(
                                'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                                'lf.feedback', 'lf.id as feedback_id',
                            )->get();

                            $feedbacks = [];
            
                            foreach($feedback as $feedback_value){
                                $packey = [];
                                $packey['feedback'] = $feedback_value;
                                $feedbacks_reply = DB::table('lesson_feedback_reply as lfr')
                                ->leftJoin('users As u', function($join){
                                    $join->on('u.id', '=', 'lfr.user_id');
                                })
                                ->where(
                                    [
                                        ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                                    ]
                                )
                                ->orWhere(
                                    [
                                        ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                                    ]
                                )
                                ->select(
                                    'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                                    'lfr.response_reply', 'lfr.id as feedback_reply_id',
                                )->get();
                
                                $packey['reply'] = $feedbacks_reply;
                                array_push($feedbacks, $packey);
                            }
                            $padded['learner_feedbacks'] = $feedbacks;
                            array_push($tutor_parent_data, $padded);
                        }
                    }
                }
            }
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Learner Lesson',
            'data' => [
                'lesson' => $tutor_parent_data,
            ]
        ], Response::HTTP_OK);
    }

    public function feedback(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
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
                'lesson_subject_id' => $request->lesson_subject_id,
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
                'feedback_id' => $request->feedback_id,
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
        
        $lesson = LessonSubject::where(
            [
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
            'lesson_commence' => 'required|string',
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
            'id' => (string)Str::uuid(),
            'parent_id' => $parent->id,
            'learners_name' => $request->learners_name,
            'learners_dob' => $request->learners_dob,
            'learners_gender' => $request->learners_gender,
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

        $parent = ParentUser::where('user_id', '=', $request->user_id)->first();

        $all_lessons = [];

        for ($i_subjects=1; $i_subjects <= $request->total_subjects; $i_subjects++) {

            $subject_id = "subject_id_".$i_subjects;
            $tutor_gender = "tutor_gender_".$i_subjects;
            $tutor_type = "tutor_type_".$i_subjects;

            $total_days = "total_day_".$i_subjects;

            
            $education_level = LessonSubject::create([
                'uuid' => (string)Str::uuid(),
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
            $lessons = Lesson::lessons($parent->id, $education_level->id);
            array_push($all_lessons, $lessons);
        }

        

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Created a lesson',
                'data' => [
                    'lessons' => $all_lessons
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function remove_lesson(Request $request){

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
        
        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorizeds'
            ], Response::HTTP_NOT_FOUND);
        }
        
       
        $lesson = LessonSubject::where(
            [
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
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
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


        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->tutor_id],
            ]
        )->select('id')->first();

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
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
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
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
        
       

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
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
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
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

        $request_tutor_user = Tutor::where(
            [
                ['user_id', '=', $request->tutor_id],
            ]
        )->select('id')->first();

        $lesson = LessonSubject::where(
            [
                ['id', '=', $request->lesson_subject_id],
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

<?php

namespace App\Http\Controllers\Parent;

use App\Models\User;
use App\Models\Tutor;
use App\Helpers\Lesson;
use App\Models\Learner;
use App\Models\Lessons;
use App\Models\ParentUser;
use Illuminate\Support\Str;
use App\Models\LessonLearner;
use App\Models\LessonSubject;
use App\Models\LessonFeedback;
use Illuminate\Support\Facades\DB;
use App\Models\LessonFeedbackReply;
use App\Http\Controllers\Controller;
use App\Models\LessonSubjectTimetable;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ParentController\LessonRequest;
use App\Http\Requests\ParentController\LessonsRequest;
use App\Http\Requests\ParentController\FeedbackRequest;
use App\Http\Requests\ParentController\LearnersRequest;
use App\Http\Requests\ParentController\AddLessonRequest;
use App\Http\Requests\ParentController\DashboardRequest;
use App\Http\Requests\ParentController\FeedbackReplyRequest;
use App\Http\Requests\ParentController\CompleteLessonRequest;
use App\Http\Requests\ParentController\AddLessonLearnerRequest;
use App\Http\Requests\ParentController\AddLessonSubjectRequest;
use App\Http\Requests\ParentController\AddLearnerToLessonRequest;
use App\Http\Requests\ParentController\RemoveLessonLearnerRequest;
use App\Http\Requests\ParentController\RemoveLessonSubjectRequest;
use App\Http\Requests\ParentController\RemoveLearnerFromLessonRequest;

class ParantController extends Controller
{
    //
    public function details(DashboardRequest $request){
        $request->validated();
        $auth = auth()->user();

       

        $user_parent = DB::table('users as u')
        ->leftJoin('parent_user as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $auth->id)->get();

        $parent = ParentUser::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $learners = Lesson::dashboard($parent->id);

        $tutor_parent_data = [];
        $parents_tutor = [];

        foreach($learners as $learners_value){
            $tutor_user_id = $learners_value['lessons']->lesson_subject_id;
            if(array_search($tutor_user_id, $parents_tutor) === false){
                if($tutor_user_id === null){}else{
                    array_push($parents_tutor, $tutor_user_id);
                }
            }
        }

       

        foreach($parents_tutor as $tutors_parent_value){

            $query0 = DB::table('lesson_subjects')
            ->where(
                [
                    ['id', '=', $tutors_parent_value]
                ]
            )
            ->select('subject_id', 'tutor_status', 'learner_status', 'id', 'tutor_id', 'lesson_learner_id')
            ->first();
            if($query0){
                
                $subject_id = $query0->subject_id;
                $tutor_status = $query0->tutor_status;
                $learner_status = $query0->learner_status;
                $tutor_id = $query0->tutor_id;

                $tutor_email = '';
                $query2 = Tutor::where('id', $tutor_id)->first();
                if($query2){
                    $query1 = User::where('id', $query2->user_id)->first();
                    if($query1){
                        $tutor_email = $query1->email;
                    }
                }

                $query3 = DB::table('subjects')
                ->where(
                    [
                        ['id', '=', $subject_id],
                    ]
                )
                ->first();
                if($query3){
                    $status = 'pending';
                    if($tutor_status == 'completed' && $learner_status == 'completed' ){
                        $status = 'completed';
                    }

                    $padded = [$tutor_email, $query3->name, $status, $tutors_parent_value];
                    array_push($tutor_parent_data, $padded);

                }
                    
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Dashboard',
            'data' => [
                'user' => $auth,
                'parent_details'=> $parent,
                'lessons' => $tutor_parent_data
            ]
        ], Response::HTTP_OK);
    }

    public function lessons(LessonsRequest $request){
        $request->validated();
        
        $auth = auth()->user();

        $user_parent = DB::table('users as u')
        ->leftJoin('parent_user as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $auth->id)->get();

        $parent = ParentUser::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_NOT_FOUND); // 404
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
                                     ['id', '=', $learner_id],
                                     ['parent_id', '=', $parent->id],
                                 ]
                             )
                             ->first();
                             if($query3){
                                 $learner_id = $query3->id;
                                 $learner_name = $query3->learners_name;
                                 $learner_dob = $query3->learners_dob;
                                 $learner_gender = $query3->learners_gender;
 
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
                                     'lesson_learner_id' => $lesson_learner_id,
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
         }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Learner Lesson',
            'data' => [
                'lessons' => $tutor_parent_data
            ]
        ], Response::HTTP_OK);
    }

    public function lesson(LessonRequest $request){
        $request->validated();
        $auth = auth()->user();
        

       

        $user_parent = DB::table('users as u')
        ->leftJoin('parent_user as p', function ($join){
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where('u.id', $auth->id)->get();

        $parent = ParentUser::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_NOT_FOUND); // 404
        }



        // $learners = Lesson::lesson($parent->id, $request->lesson_subject_timetable_id);
        $padded = [];
        $tutor_parent_data = [];
        $query = DB::table('lesson_subjects')
        ->where(
            [
                ['id', '=', $request->lesson_subject_id]
            ]
        )
        ->select('subject_id', 'tutor_status', 'learner_status', 'lesson_learner_id', 'id', 'tutor_id')
        ->first();
        if($query){
            $subject_id = $query->subject_id;
            $tutor_status = $query->tutor_status;
            $learner_status = $query->learner_status;
            $lesson_learner_id = $query->lesson_learner_id;
            $lesson_subject_id = $query->id;
            $lesson_tutor_id = $query->tutor_id;

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
                ->first();
                if($query2){
                    $learner_id = $query2->learner_id;
                    $lesson_id = $query2->lesson_id;


                    $query3 = DB::table('learners')
                    ->where(
                        [
                            ['id', '=', $learner_id],
                            ['parent_id', '=', $parent->id],
                        ]
                    )
                    ->first();
                    if($query3){
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

                        $padded['lesson_details'] = array(
                            'parent_email' => $parent_email,
                            'lesson_address' => $lesson_address,
                            'lesson_goal' => $lesson_goal,
                            'lesson_mode' => $lesson_mode,
                            'lesson_period' => $lesson_period,
                            'lesson_subject' => $query1->name,
                            'lesson_learner_id' => $lesson_learner_id,
                            'lesson_subject_id' => $lesson_subject_id,
                        );


                        $padded['learner_details'] = array(
                            'learner_name' => $learner_name,
                            'learner_dob' => $learner_dob,
                            'learner_gender' => $learner_gender,
                            'lesson_completed' => $status,
                        );

                        $padded['lesson_period'] = []; 

                        $query5 = DB::table('lesson_subjects_timetable')
                        ->where(
                            [
                                ['lesson_subject_id', '=', $request->lesson_subject_id],
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
                            ['lf.lesson_subject_id', '=', $request->lesson_subject_id],
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

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Learner Lesson',
            'data' => [
                'lesson' => $tutor_parent_data
            ]
        ], Response::HTTP_OK);
    }

    public function feedback(FeedbackRequest $request){
        $request->validated();

       
        
        $auth = auth()->user();
        

      
        $return = LessonFeedback::create(
            [
                'lesson_subject_id' => $request->lesson_subject_id,
                'parent_tutor' => 'parent',
                'user_id' => $auth->id,
                'feedback' => $request->feedback,
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Add Feedback',
            'data' => [
                $return
            ]
        ], Response::HTTP_OK);
    }

    public function feedback_reply(FeedbackReplyRequest $request){

        $request->validated();
        
        $auth = auth()->user();
       

        $reply = LessonFeedbackReply::create(
            [
                'feedback_id' => $request->feedback_id,
                'parent_tutor_admin' => 'parent',
                'user_id' => $auth->id,
                'response_reply' => $request->feedback_reply,
                'response_status' => '0',
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Add Feedback Reply',
            'data' => [
                $reply
            ]
        ], Response::HTTP_OK);
    }

    public function complete_lesson(CompleteLessonRequest $request){

        $request->validated();
        
        $auth = auth()->user();
        

       
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
            [ 'learner_status' => 'completed']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Marked lesson completed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function add_lesson(AddLessonRequest $request){

        $request->validated();

        $auth = auth()->user();
        

        

        for ($i_learners=1; $i_learners <= $request->total_learners; $i_learners++) {

            $fields = Validator::make($request->all(), [
                'learner_id_'.$i_learners => 'required|string|exists:learners,id',
                'description_of_learner_'.$i_learners => 'required|string',
                'lesson_commence_'.$i_learners => 'required|string',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
            
        }

        $lesson = Lessons::create([
            'id' => (string)Str::uuid(),
            'parent_id' => $auth->id,
            'lesson_address' => $request->lesson_address,
            'lesson_goals' => $request->lesson_goals,
            'lesson_mode' => $request->lesson_mode,
            'lesson_period' => $request->lesson_period,
        ]);

        $lesson_learner_id = [];

        for ($i_learners=1; $i_learners <= $request->total_learners; $i_learners++) {

            $checks = LessonLearner::
            where(
                [
                    ['lesson_id', '=', $lesson->id],
                    ['learner_id', '=', $request->{'learner_id_'.$i_learners}]
                ]
            )->first();
            if(!$checks){
                $lesson_learner = LessonLearner::create([
                    'id' => (string)Str::uuid(),
                    'lesson_id' => $lesson->id,
                    'learner_id' => $request->{'learner_id_'.$i_learners},
                    'learners_description' => $request->{'description_of_learner_'.$i_learners},
                    'lesson_commence' => $request->{'lesson_commence_'.$i_learners},
                ]);
                $all_base = array("lesson_learner_id" => $lesson_learner->id);
                array_push($lesson_learner_id, $all_base);
            }

        }

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Created a lesson',
                'data' => [
                    'lesson_learners' => $lesson_learner_id
                ]
            ], Response::HTTP_CREATED
        );
    }
    
    public function add_lesson_subject(AddLessonSubjectRequest $request){

    $request->validated();

        $auth = auth()->user();
        

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

        $lesson_subject = [];

        for ($i_subjects=1; $i_subjects <= $request->total_subjects; $i_subjects++) {

            $subject_id = "subject_id_".$i_subjects;
            $tutor_gender = "tutor_gender_".$i_subjects;
            $tutor_type = "tutor_type_".$i_subjects;

            $total_days = "total_day_".$i_subjects;

            
            $subjectLesson = LessonSubject::create([
                'id' => (string)Str::uuid(),
                'lesson_learner_id' => $request->lesson_learner_id,
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
                    'lesson_subject_id' => $subjectLesson->id,
                    'lesson_day_id' => $request->$day_id,
                    'lesson_day_hours' => $request->$day_hours,
                    'lesson_day_start_time' => $request->$start_time,
                ]);

                array_push($lesson_subject, $subjectLesson->id);
            }
        }

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Created a lesson',
                'data' => [
                    'lesson_subjects' => $lesson_subject
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function remove_lesson(RemoveLessonSubjectRequest $request){

        $request->validated();
        
        $auth = auth()->user();
        

       

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

        if((int)$lesson->tutor_id > 0){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Lesson cannot be removed here, contact the organization',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $lesson->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent removed a lesson',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function add_learner(AddLessonLearnerRequest $request){

        $request->validated();
        
        $auth = auth()->user();
        

        $search_learner = Learner::where(
            [
                ['learners_name', '=', $request->learner_name],
                ['parent_id', '=', $auth->id],
            ]
        )->first();
        if($search_learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Learner Exist',
            ], Response::HTTP_NOT_FOUND); // 404
        }


        $learner = Learner::create([
            'id' => (string)Str::uuid(),
            'parent_id' => $auth->id,
            'learners_name' => $request->learner_name,
            'learners_dob' => $request->learner_dob,
            'learners_gender' => $request->learner_gender,
        ]);


        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent Added a learner',
            'data' => [
                $learner
            ]
        ], Response::HTTP_OK);
    }

    public function remove_learner(RemoveLessonLearnerRequest $request){

        $request->validated();
        
        $auth = auth()->user();
       

        $learner = Learner::where(
            [
                ['id', '=', $request->learner_id],
            ]
        )->first();

        if(!$learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Learner not found',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $learner->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Parent removed a learner',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function learners(LearnersRequest $request){

        $request->validated();
        
        $auth = auth()->user();

        $search_learner = Learner::where(
            [
                ['parent_id', '=', $auth->id],
            ]
        )->get();
        if(!$search_learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Learners not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Learners',
            'data' => [
                $search_learner
            ]
        ], Response::HTTP_OK);
    }

    public function learner(LessonsRequest $request){

        $request->validated();
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'parent')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $parent = ParentUser::where('user_id', $auth->id)->first();
        if(!$parent){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a parent',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $search_learner = Learner::where(
            [
                ['parent_id', '=', $parent->id],
                ['id', '=', $request->learner_id],
            ]
        )->first();
        if(!$search_learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Learner not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Learner',
            'data' => [
                $search_learner
            ]
        ], Response::HTTP_OK);
    }
    
    public function add_learner_to_lesson(AddLearnerToLessonRequest $request){

        $request->validated();
        
        $auth = auth()->user();
        

       

        $search_learner = Learner::where(
            [
                ['parent_id', '=', $auth->id],
                ['id', '=', $request->learner_id],
            ]
        )->first();
        if(!$search_learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Learner not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $search_lesson = Lessons::where(
            [
                ['parent_id', '=', $parent->id],
                ['id', '=', $request->lesson_id],
            ]
        )->first();
        if(!$search_lesson){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Lesson not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }


        $search_lesson_learner = LessonLearner::where(
            [
                ['lesson_id', '=', $request->lesson_id],
            ]
        )->get();
        if(!$search_lesson_learner){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Lesson Learner not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }


        $lesson_learner = LessonLearner::create([
            'id' => (string)Str::uuid(),
            'lesson_id' => $request->lesson_id,
            'learner_id' => $request->learner_id,
            'learners_description' => $request->description_of_learner,
            'lesson_commence' => $request->lesson_commence,
        ]);


        foreach($search_lesson_learner as $sllv){


            $check_lesson_learner = LessonSubject::where(
                [
                    ['lesson_learner_id', '=', $sllv->id],
                ]
            )->get();
            if(!$check_lesson_learner){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    "status" => "error",
                    'message' => 'Lesson Learner not found',
                ], Response::HTTP_NOT_FOUND); // 404
            }

            foreach($check_lesson_learner as $cllv){

                $education_level_new = LessonSubject::create([
                    'id' => (string)Str::uuid(),
                    'lesson_learner_id' => $lesson_learner->id,
                    'subject_id' => $cllv->subject_id,
                    'learner_tutor_gender' => $cllv->learner_tutor_gender,
                    'learner_tutor_type' => $cllv->learner_tutor_type,
                ]);

                $check_timetable = LessonSubjectTimetable::where(
                    [
                        ['lesson_subject_id', '=', $cllv->id],
                    ]
                )->get();
                if(!$check_timetable){
                    return response()->json([
                        'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        "status" => "error",
                        'message' => 'Lesson subject Timetable not found',
                    ], Response::HTTP_NOT_FOUND); // 404
                }
        
                foreach($check_timetable as $ctv){
        
                    LessonSubjectTimetable::create([
                        'id' => (string)Str::uuid(),
                        'lesson_subject_id' => $education_level_new->id,
                        'lesson_day_id' => $ctv->lesson_day_id,
                        'lesson_day_hours' => $ctv->lesson_day_hours,
                        'lesson_day_start_time' => $ctv->lesson_day_start_time,
                    ]);
                }
            }
        }


        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Learner Added to Lesson',
            'data' => [
                $lesson_learner
            ]
        ], Response::HTTP_OK);
    }

    public function remove_learner_from_lesson(RemoveLearnerFromLessonRequest $request){

        $request->validated();
        
        $auth = auth()->user();

        $search_lesson = LessonLearner::where(
            [
                ['id', '=', $request->lesson_learner_id],
            ]
        )->first();
        if(!$search_lesson){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Lesson not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $search_lesson->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Learner removed from Lesson',
            'data' => [
                
            ]
        ], Response::HTTP_OK);
    }
}

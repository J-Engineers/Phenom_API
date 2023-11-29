<?php

namespace App\Http\Controllers\Tutors;


use Storage;
use App\Helpers\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LessonFeedback;
use App\Models\LessonFeedbackReply;
use App\Models\LessonLearner;
use App\Models\LessonSubject;
use App\Models\ParentUser;
use App\Models\Tutor;
use App\Models\TutorsCertification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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

        $tutor = Tutor::where('user_id', $auth->id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a tutor',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        $learners = Lesson::tutor($tutor->id);

        $tutor_parent_data = [];
        $tutors_parent = [];

        foreach($learners as $learners_value){
            $parent_user_id = $learners_value['lessons']->parent_id;
            if(array_search($parent_user_id, $tutors_parent) === false){
                array_push($tutors_parent, $parent_user_id);
            }
        }

        foreach($tutors_parent as $tutors_parent_value){
            $query0 = User::where('id', $tutors_parent_value)->first();
            $query = ParentUser::where('user_id', $tutors_parent_value)->first();
            $parent_id = $query->id;

            $query1 = DB::table('lessons')
            ->where(
                [
                    ['parent_id', '=', $parent_id],
                ]
            )
            ->select('id as lesson_id')
            ->get();
            if($query1){
                foreach($query1 as $query1_value){

                    $lesson_id = $query1_value->lesson_id;
    
    
                    $query2 = DB::table('lesson_learner')
                    ->where(
                        [
                            ['lesson_id', '=', $lesson_id],
                        ]
                    )
                    ->select('id as lesson_learner_id', 'learner_id as learner_id')
                    ->get();
                    if($query2){
                        foreach($query2 as $query2_value){
                            $learner_id = $query2_value->learner_id;
                            $lesson_learner_id = $query2_value->lesson_learner_id;
        
        
                            $query3 = DB::table('learners')
                            ->where(
                                [
                                    ['parent_id', '=', $parent_id],
                                    ['id', '=', $learner_id],
                                ]
                            )
                            ->first();
                            if($query3){
        
                                $query4 = DB::table('lesson_subjects')
                                ->where(
                                    [
                                        ['lesson_learner_id', '=', $lesson_learner_id]
                                    ]
                                )
                                ->select('subject_id', 'tutor_status', 'learner_status', 'id')
                                ->first();
                                if($query4){
                                    $subject_id = $query4->subject_id;
                                    $tutor_status = $query4->tutor_status;
                                    $learner_status = $query4->learner_status;
        
        
                                    $query5 = DB::table('subjects')
                                    ->where(
                                        [
                                            ['id', '=', $subject_id],
                                        ]
                                    )
                                    ->first();
                                    if($query5){
                                        $status = 'pending';
                                        if($tutor_status == 'completed' && $learner_status == 'completed' ){
                                            $status = 'completed';
                                        }
        
                                        $padded = [$query0->email, $query5->name, $status, $query4->id];
                                        array_push($tutor_parent_data, $padded);
        
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Dashboard',
            'data' => [
                'user' => $auth,
                'tutor_details'=> $tutor,
                'courses' => $tutor_parent_data,
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
        
        $auth = auth()->user();
        

        if(!($auth->user_type == 'tutor')){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'You are not permitted to view this resource',
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $tutor = Tutor::where('user_id', $auth->id)->first();
        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => 'Not a tutor',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        // $learners = Lesson::tutor_lessons($tutor->id, $request->lesson_subject_id);

        $padded = [];
        $tutor_parent_data = [];
        $query = DB::table('lesson_subjects')
        ->where(
            [
                ['id', '=', $request->lesson_subject_id]
            ]
        )
        ->select('subject_id', 'tutor_status', 'learner_status', 'lesson_learner_id')
        ->first();
        if($query){
            $subject_id = $query->subject_id;
            $tutor_status = $query->tutor_status;
            $learner_status = $query->learner_status;
            $lesson_learner_id = $query->lesson_learner_id;

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
                        );


                        $padded['learner_details'] = array(
                            'learner_name' => $learner_name,
                            'learner_dob' => $learner_dob,
                            'learner_gender' => $learner_gender,
                            'lesson_completed' => $status,
                        );
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
            'message' => 'Tutor Learner Lesson',
            'data' => [
                'user' => $auth,
                'tutor_details'=> $tutor,
                'lesson' => $tutor_parent_data,
            ]
        ], Response::HTTP_OK);
    }

    public function feedback(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
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
                'lesson_subject_id' => $request->lesson_subject_id,
                'parent_tutor' => 'tutor',
                'user_id' => $auth->id,
                'feedback' => $request->feedback,
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Added Feedback',
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
                'feedback_id' => $request->feedback_id,
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
            [ 'tutor_status' => 'completed']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Marked lesson completed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function activity_badge(Request $request){

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

        $tutor = Tutor::where('user_id', $auth->id)->first();
       

        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => "Tutor Details not found",
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $tutor->update(
            [ 'activity_badge' => 'active']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor turned on activity badge',
            'data' => [
                $auth,
                $tutor
            ]
        ], Response::HTTP_OK);
    }

    public function activity_badge_remove(Request $request){

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

        $tutor = Tutor::where('user_id', $auth->id)->first();
       

        if(!$tutor){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => "Tutor Details not found",
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $tutor->update(
            [ 'activity_badge' => 'inactive']
        );

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor turned off activity badge',
            'data' => [
                $auth,
                $tutor
            ]
        ], Response::HTTP_OK);
    }
    
    public function add_certification(Request $request){

        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'organization' => 'required|string',
            'course' => 'required|string',
            'duration' => 'required|string',
            'date' => 'required|string',
            'certificate_link' => 'required|file',
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

        $file = $request->file('certificate_link');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $base_path = $disk->url($filePath);

        $cert = TutorsCertification::create([
            'id' => (string)Str::uuid(),
            'organization' => $request->organization,
            'course' => $request->course,
            'duration' => $request->duration,
            'date' => $request->date,
            'link' => $base_path,
            'user_id' => $auth->id,
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor added certificate',
            'data' => [
                'tutor' => $auth,
                'certificate' => $cert,
            ]
        ], Response::HTTP_OK);
    }
}

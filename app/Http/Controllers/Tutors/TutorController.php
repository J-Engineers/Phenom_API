<?php

namespace App\Http\Controllers\Tutors;


use Storage;
use App\Helpers\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LessonFeedback;
use App\Models\LessonFeedbackReply;
use App\Models\LessonSubject;
use App\Models\Tutor;
use App\Models\TutorsCertification;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

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

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Dashboard',
            'data' => [
                'user' => $auth,
                'tutor_details'=> $tutor,
                'lessons' => $learners,
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

        $learners = Lesson::tutor_lessons($tutor->id, $request->lesson_subject_id);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Tutor Learner Lesson',
            'data' => [
                'user' => $auth,
                'tutor_details'=> $tutor,
                'lessons' => $learners,
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

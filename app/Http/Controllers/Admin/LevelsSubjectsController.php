<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\EducationLevels;
use App\Http\Controllers\Controller;
use App\Models\Subjects;
use App\Models\TutorSubject;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LevelsSubjectsController extends Controller
{
    //
    public function createLevel(Request $request){


        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
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
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_education = EducationLevels::where('name', $request->name)->first();
        if($search_education){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Education Level Already Exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $created = EducationLevels::create([
            'uuid' => (string)Str::uuid(),
            'name' =>  $request->name
        ]);

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Level Created',
            'data' => $created
        ], Response::HTTP_CREATED);
    }

    public function editLevel(Request $request){


        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'api_key' => 'required|string',
            'education_level_id' => 'required|string'
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

        $search_education = EducationLevels::where(
            ['id'=> $request->education_level_id]
        )->first();
        if(!$search_education){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Level Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_education->update(
            [
                'name' =>  $request->name
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Education Level Updated',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function deleteLevel(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'education_level_id' => 'required|string'
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

        $search_education = EducationLevels::where(
            ['id'=> $request->education_level_id]
        )->first();
        if(!$search_education){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Level Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_education->delete();

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Education Level Deleted',
            'data' => []
        ], Response::HTTP_CREATED);
    }

    public function createSubject(Request $request){


        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'education_level_id' => 'required|string',
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
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_education = EducationLevels::where(['id' => $request->education_level_id])->first();
        $search_subject = Subjects::where(
            ['name'=> $request->name, 'education_levels_id' => $request->education_level_id]
        )->first();
        if($search_subject OR !$search_education){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Already Exist or Level Does not Exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $created = Subjects::create(
            [
                'uuid' => (string)Str::uuid(),
                'name' =>  $request->name,
                'education_levels_id' => $request->education_level_id
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Subject Created',
            'data' => $created
        ], Response::HTTP_CREATED);
    }

    public function editSubject(Request $request){


        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'education_level_id' => 'required|string',
            'subject_id' => 'required|string',
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
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject_id = Subjects::where(
            ['id'=> $request->subject_id]
        )->first();
        if(!$search_subject_id){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Not Found'
            ], Response::HTTP_NOT_FOUND);
        }


        $search_education = EducationLevels::where(['id'=> $request->education_level_id])->first();
        $search_subject = Subjects::where(
            ['name'=> $request->name, 'education_levels_id' => $request->education_level_id]
        )->first();
        if($search_subject OR !$search_education){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Already Exist or Level Does not Exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject_id->update(
            [
                'name' =>  $request->name,
                'education_levels_id' => $request->education_level_id
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Subject Updated',
            'data' => $search_subject_id
        ], Response::HTTP_CREATED);
    }

    public function deleteSubject(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'subject_id' => 'required|string'
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

        $search_subject = Subjects::where(
            ['id' => $request->subject_id]
        )->first();
        if(!$search_subject){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject->delete();

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Subject Deleted',
            'data' => []
        ], Response::HTTP_CREATED);
    }

    public function addSubjectToTutor(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'user_id' => 'required|string',
            'subject_id' => 'required|string',
            'hours_per_week' => 'required|string',
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

        $search_subject = Subjects::where(
            ['id' => $request->subject_id]
        )->first();
        if(!$search_subject){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $level_id = $search_subject->education_levels_id;


        $search_tutor_subject = TutorSubject::
        where('subject_id', $request->subject_id)
        ->where('user_id', $request->user_id)
        ->where('level_id', $level_id)
        ->first();
        if($search_tutor_subject){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Assigned Already'
            ], Response::HTTP_NOT_FOUND);
        }

        $new_record = TutorSubject::create([
            'uuid' => (string)Str::uuid(),
            'hours_per_week' => $request->hours_per_week,
            'user_id' => $request->user_id,
            'level_id' => $level_id,
            'subject_id' => $request->subject_id
        ]);

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Subject Assigned to tutor',
            'data' => $new_record
        ], Response::HTTP_CREATED);
    }

    public function editTutorSubject(Request $request){

        $fields = Validator::make($request->all(), [
            'hours_per_week' => 'required|string',
            'tutor_subject_id' => 'required|string',
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
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject_id = TutorSubject::where(
            ['id'=> $request->tutor_subject_id]
        )->first();
        if(!$search_subject_id){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Not attached to tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject_id->update(
            [
                'hours_per_week' =>  $request->hours_per_week
            ]
        );

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Tutor Subject Updated',
            'data' => $search_subject_id
        ], Response::HTTP_CREATED);
    }

    public function deleteTutorSubject(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'tutor_subject_id' => 'required|string'
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

        $search_subject = TutorSubject::where(
            ['id' => $request->tutor_subject_id]
        )->first();
        if(!$search_subject){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Subject Not Found assigned to tutor'
            ], Response::HTTP_NOT_FOUND);
        }

        $search_subject->delete();

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Subject Deleted',
            'data' => []
        ], Response::HTTP_CREATED);
    }
}

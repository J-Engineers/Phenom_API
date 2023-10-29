<?php

namespace App\Http\Controllers;

use App\Models\EducationLevels;
use App\Http\Controllers\Controller;
use App\Models\Subjects;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    //
    public function viewLevels(Request $request){


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


        $search_education = EducationLevels::all();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Level',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewLevel(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'level_id' => 'required|string|exists:education_levels,id'
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

       
        $search_education = EducationLevels::where([
            'id' => $request->level_id
        ])->first();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Level',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewSubjects(Request $request){


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

        $search_education = Subjects::all();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewSubject(Request $request){


        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'subject_id' => 'required|string|exists:subjects,id'
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

        
        $search_education = Subjects::where([
            'id' => $request->subject_id
        ])->first();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewLevelSubject(Request $request){



        $fields = Validator::make($request->all(), [
            'api_key' => 'required|string',
            'level_id' => 'required|string|exists:education_levels,id',
            'subject_id' => 'required|string|exists:subjects,id'
        ]); // request body validation rules

        if($fields->fails()){
            return response()->json([
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                "status" => "error",
                'message' => $fields->messages(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        } // request body validation failed, so lets return

       

        $search_education = Subjects::where([
            'id' => $request->subject_id,
            'education_levels_id' => $request->level_id
        ])->first();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }
}

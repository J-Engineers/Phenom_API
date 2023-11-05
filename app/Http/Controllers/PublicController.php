<?php

namespace App\Http\Controllers;

use App\Models\EducationLevels;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


        $search_education = DB::table('subjects as s')
        ->leftJoin('education_levels as el', function ($join) {
            $join->on('s.education_levels_id', '=', 'el.id');
        })
        ->select(
            'el.name as education_level_name', 'el.id as education_level_id',
            's.name as subject_name', 's.id as subject_id',
        )
        ->get();
       

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

        
        

        $search_education = DB::table('subjects as s')
        ->leftJoin('education_levels as el', function ($join) {
            $join->on('s.education_levels_id', '=', 'el.id');
        })
        ->where([
            's.id' => $request->subject_id
        ])
        ->select(
            'el.name as education_level_name', 'el.id as education_level_id',
            's.name as subject_name', 's.id as subject_id',
        )
        ->first();
       

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


        $search_education = DB::table('subjects as s')
        ->leftJoin('education_levels as el', function ($join) {
            $join->on('s.education_levels_id', '=', 'el.id');
        })
        ->where([
            's.id' => $request->subject_id,
            's.education_levels_id' => $request->level_id
        ])
        ->select(
            'el.name as education_level_name', 'el.id as education_level_id',
            's.name as subject_name', 's.id as subject_id',
        )
        ->get();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewLessonDays(Request $request){


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

        $search_education = DB::table('lesson_day')->select('day_name', 'id')->get();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EducationLevels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\PublicController\ViewDaysRequest;
use App\Http\Requests\PublicController\ViewLevelRequest;
use App\Http\Requests\PublicController\ViewLevelsRequest;
use App\Http\Requests\PublicController\ViewSubjectRequest;
use App\Http\Requests\PublicController\ViewSubjectsRequest;
use App\Http\Requests\PublicController\ViewLevelSubjectRequest;

class PublicController extends Controller
{
    //
    public function viewLevels(ViewLevelsRequest $request){
        $request->validated();


        $search_education = EducationLevels::all();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Level',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }

    public function viewLevel(ViewLevelRequest $request){
        $request->validated();

       
       
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

    public function viewSubjects(ViewSubjectsRequest $request){
        $request->validated();



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

    public function viewSubject(ViewSubjectRequest $request){
        $request->validated();

       
        

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

    public function viewLevelSubject(ViewLevelSubjectRequest $request){
        $request->validated();


       
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

    public function viewLessonDays(ViewDaysRequest $request){
        $request->validated();


        $search_education = DB::table('lesson_day')->select('day_name', 'id')->get();
       

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Found Subjects',
            'data' => $search_education
        ], Response::HTTP_CREATED);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\EducationLevels;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\PublicController\ViewLevelRequest;
use App\Http\Requests\PublicController\ViewLevelsRequest;

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

   
}
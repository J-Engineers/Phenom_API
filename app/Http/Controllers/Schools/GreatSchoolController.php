<?php

namespace App\Http\Controllers\Schools;


use App\Models\GreatSchool;
use App\Models\GreatSchoolRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schools\GreatSchoolRequestRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Schools\GreatSchoolsRequest;

class GreatSchoolController extends Controller
{
    public function dashboard(GreatSchoolsRequest $request){

        $user = auth()->user();
       
        $school = GreatSchool::where('user_id', $user->id)->first();
        $school_request = GreatSchoolRequest::where(
            [
                ['great_school_id', '=', $school->id],
                ['status', '=', 1],
                
            ]
        )->get();
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'school' => $school,
                    'school_requests' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function request(GreatSchoolRequestRequest $request){

        $user = auth()->user();
       
        $school = GreatSchool::where('user_id', $user->id)->first();
        $school_request = GreatSchoolRequest::where(
            [
                ['token', '=', $request->token],
                ['status', '=', 0],
            ]
        )->first();
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'school' => $school,
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function approve_request(GreatSchoolRequestRequest $request){

        $user = auth()->user();
       
        $school = GreatSchool::where('user_id', $user->id)->first();
        $school_request = GreatSchoolRequest::where('id', $request->request_id)->first();

        if(!$school_request){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Request Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $school_request->update(
            [
                'status' => 1
            ]
        );

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'school' => $school,
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function decline_request(GreatSchoolRequestRequest $request){

        $user = auth()->user();
       
        $school = GreatSchool::where('user_id', $user->id)->first();
        $school_request = GreatSchoolRequest::where('id', $request->request_id)->first();

        if(!$school_request){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Request Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $school_request->update(
            [
                'status' => 0
            ]
        );

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'School data',
                'data' => [
                    'school' => $school,
                    'school_request' => $school_request,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

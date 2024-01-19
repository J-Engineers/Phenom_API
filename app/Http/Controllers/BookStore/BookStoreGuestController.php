<?php

namespace App\Http\Controllers\BookStore;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\BookStoreCategoriesRequest;

class BookStoreGuestController extends Controller
{
    public function bookstoreCategories(BookStoreCategoriesRequest $request){
        $request->validated();

        $query = DB::table('store_category')
        ->select('id', 'name')
        ->get();
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Categories',
            'data' => [
                'category' => $query
            ]
        ], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\BookStore;

use App\Models\User;
use App\Models\BookCategory;
use App\Models\BookStoreUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\BookStore\BookStoreBookRequest;
use App\Http\Requests\BookStore\BookStoreBooksRequest;
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

    public function books(BookStoreBooksRequest $request){
        $request->validated();

        $all_books = [];

        $books = DB::table('book_store')
        ->select(
            'id', 'book_name', 'book_author_name', 'book_isbn', 
            'book_cover', 'book_category as book_category_id', 'book_quantity',
            'book_price', 'book_description', 'status',
        )
        ->get();
        if($books){
            foreach($books as $book){

                $data = [];

                $books_category = BookCategory::where(
                    [
                        ['id', '=', $book->book_category_id],
                    ]
                )->first();

                $data['id'] = $book->id;
                $data['name'] = $book->book_name;
                $data['author'] = $book->book_author_name;
                $data['img'] = $book->book_cover;
                $data['price'] = $book->book_price;
                $data['desc'] = $book->book_description;
                $data['category'] = $books_category->name;
                $data['quantity'] = $book->book_quantity;
                $data['isbn'] = $book->book_isbn;
                array_push($all_books, $data);

            }
        }
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Books data',
                'data' => [
                    'books' => $all_books,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function book(BookStoreBookRequest $request){
        $request->validated();
        $all_books = [];

        $books = DB::table('book_store')
        ->select(
            'id', 'book_name', 'book_author_name', 'book_isbn', 
            'book_cover', 'book_category as book_category_id', 'book_quantity',
            'book_price', 'book_description', 'status',
        )
        ->where(
            [
                ['id', '=', $request->book_id]
            ]
        )
        ->get();
        if($books){
            foreach($books as $book){

                $data = [];

                $books_category = BookCategory::where(
                    [
                        ['id', '=', $book->book_category_id],
                    ]
                )->first();

                $data['id'] = $book->id;
                $data['name'] = $book->book_name;
                $data['author'] = $book->book_author_name;
                $data['img'] = $book->book_cover;
                $data['price'] = $book->book_price;
                $data['desc'] = $book->book_description;
                $data['category'] = $books_category->name;
                $data['quantity'] = $book->book_quantity;
                $data['isbn'] = $book->book_isbn;
                array_push($all_books, $data);

            }
        }
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Book data',
                'data' => [
                    'book' => $all_books,
                ]
            ], Response::HTTP_CREATED
        );
    }
}

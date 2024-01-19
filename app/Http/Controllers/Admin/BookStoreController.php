<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\BookStore;
use App\Models\BookCategory;
use App\Models\BookStoreUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookStoreRequest;
use App\Http\Requests\Admin\BookStoreRequests;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\BookStoreCategoryRequest;
use App\Http\Requests\Admin\BookStoreCategoriesRequest;
use App\Http\Requests\Admin\BookStoreCategoryUpdateRequest;
use App\Http\Requests\BookStore\BookStoreGetRequestRequest;
use App\Http\Requests\BookStore\BookStoresGetRequestRequest;

class BookStoreController extends Controller
{
    public function bookstores(BookStoreRequests $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all_book_stores = [];
        $query0 = DB::table('users')
        ->select('first_name', 'last_name', 'address', 'phone', 'id', 'email')
        ->where(
            [
                ['user_type', '=', "bookshop"]
            ]
        )
        ->get();
        if($query0){
            foreach($query0 as $user){
                $first_name = $user->first_name;
                $last_name = $user->last_name;
                $address = $user->address;
                $phone = $user->phone;
                $id = $user->id;
                $email = $user->email;
                $data =  [];
                $query1 = BookStoreUser::where('user_id', $id)->first();
                if($query1){
                    $shop_address = $query1->store_address;

                    $data['name'] = $first_name ." ". $last_name;
                    $data['address'] = $address;
                    $data['phone'] = $phone;
                    $data['email'] = $email;
                    $data['id'] = $id;
                    $data['shop_address'] = $shop_address;
                }
                array_push($all_book_stores, $data);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Stores',
            'data' => [
                'stores' => $all_book_stores
            ]
        ], Response::HTTP_OK);
    }

    public function bookstore(BookStoreRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all_book_stores = [];
        $query0 = DB::table('users')
        ->select('first_name', 'last_name', 'address', 'phone', 'id', 'email')
        ->where(
            [
                ['id', '=', $request->user_id]
            ]
        )
        ->get();
        if($query0){
            foreach($query0 as $user){
                $first_name = $user->first_name;
                $last_name = $user->last_name;
                $address = $user->address;
                $phone = $user->phone;
                $id = $user->id;
                $email = $user->email;
                $data =  [];
                $query1 = BookStoreUser::where('user_id', $id)->first();
                if($query1){
                    $shop_address = $query1->store_address;

                    $data['name'] = $first_name ." ". $last_name;
                    $data['address'] = $address;
                    $data['phone'] = $phone;
                    $data['email'] = $email;
                    $data['id'] = $id;
                    $data['shop_address'] = $shop_address;
                }
                array_push($all_book_stores, $data);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store',
            'data' => [
                'store' => $all_book_stores
            ]
        ], Response::HTTP_OK);
    }

    public function removebookstore(BookStoreRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        User::where('id', $request->user_id)->delete();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Removed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function bookstoreCategories(BookStoreCategoriesRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $query = DB::table('store_category')
        ->select('id', 'name')
        ->get();
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Categories',
            'data' => [
                'stores' => $query
            ]
        ], Response::HTTP_OK);
    }

    public function bookstoreCategory(BookStoreCategoryRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $query = DB::table('store_category')
        ->select('name')
        ->where(
            [
                ['id', '=', $request->category_id],
            ]
        )
        ->get();
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Category',
            'data' => [
                'stores' => $query
            ]
        ], Response::HTTP_OK);
    }

    public function bookstoreCategoryUpdate(BookStoreCategoryUpdateRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $query = DB::table('store_category')
        ->select('name')
        ->where(
            [
                ['id', '=', $request->category_id],
            ]
        )
        ->get();
        if(!$query){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Store Category Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $query->update(['name' => $request->name]);
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Category Updated',
            'data' => [
                'stores' => $query
            ]
        ], Response::HTTP_OK);
    }

    public function bookstoreCategoryRemove(BookStoreCategoryRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        BookCategory::where('id', $request->category_id)->delete();
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Category Removed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorerequests(BookStoreRequests $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all_book_stores = [];
        $query0 = DB::table('book_store_request')
        ->select('name', 'email', 'phone', 'address', 'book_id', 'quantity', 'status', 'id', 'book_id')
       
        ->get();
        if($query0){
            foreach($query0 as $request_book){
                $name = $request_book->name;
                $email = $request_book->email;
                $phone = $request_book->phone;
                $address = $request_book->address;
                $book_id = $request_book->book_id;
                $quantity = $request_book->quantity;
                $id = $request_book->id;
                $book_id = $request_book->book_id;

                $data =  [];
                $query1 = BookStore::where('id', $book_id)->first();
                if($query1){
                    $store_user_id = $query1->store_user_id;
                    $book_name = $query1->book_name;
                    $book_author_name = $query1->book_author_name;
                    $book_isbn = $query1->book_isbn;
                    $book_cover = $query1->book_cover;
                    $book_category = $query1->book_category;
                    $book_quantity = $query1->book_quantity;
                    $book_price = $query1->book_price;
                    $book_description = $query1->book_description;

                    $query2 = BookStoreUser::where('user_id', $store_user_id)->first();
                    if($query2){
                        $store_address = $query2->store_address;
                    }

                    $query3 = User::where('id', $store_user_id)->first();
                    if($query3){
                        $store_name = $query3->first_name." ".$query3->last_name;
                        $store_email = $query3->email;
                        $store_phone = $query3->phone;
                    }

                    $query4 = BookCategory::where('id', $book_category)->first();
                    if($query4){
                        $store_book_category = $query4->name;
                    }

                    $data['store'] = [];

                    $data['store']['name'] = $store_name;
                    $data['store']['email'] = $store_email;
                    $data['store']['phone'] = $store_phone;
                    $data['store']['address'] = $store_address;
                    $data['store']['id'] = $store_user_id;

                    $data['book'] = [];

                    $data['book']['name'] = $book_name;
                    $data['book']['author_name'] = $book_author_name;
                    $data['book']['isbn'] = $book_isbn;
                    $data['book']['cover'] = $book_cover;
                    $data['book']['quantity'] = $book_quantity;
                    $data['book']['price'] = $book_price;
                    $data['book']['description'] = $book_description;
                    $data['book']['category'] = $store_book_category;
                    $data['book']['id'] = $book_id;

                    $data['request'] = [];

                    $data['request']['name'] = $name;
                    $data['request']['email'] = $email;
                    $data['request']['phone'] = $phone;
                    $data['request']['address'] = $address;
                    $data['request']['quantity'] = $quantity;
                    $data['request']['id'] = $id;
                    array_push($all_book_stores, $data);
                    
                }
                
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Stores Request',
            'data' => [
                'stores' => $all_book_stores
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorerequest(BookStoreGetRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all_book_stores = [];
        $query0 = DB::table('book_store_request')
        ->select('name', 'email', 'phone', 'address', 'book_id', 'quantity', 'status', 'id', 'book_id')
       ->where(
        [
            ['id', '=', $request->request_id],
        ]
       )
        ->get();
        if($query0){
            foreach($query0 as $request_book){
                $name = $request_book->name;
                $email = $request_book->email;
                $phone = $request_book->phone;
                $address = $request_book->address;
                $book_id = $request_book->book_id;
                $quantity = $request_book->quantity;
                $id = $request_book->id;
                $book_id = $request_book->book_id;

                $data =  [];
                $query1 = BookStore::where('id', $book_id)->first();
                if($query1){
                    $store_user_id = $query1->store_user_id;
                    $book_name = $query1->book_name;
                    $book_author_name = $query1->book_author_name;
                    $book_isbn = $query1->book_isbn;
                    $book_cover = $query1->book_cover;
                    $book_category = $query1->book_category;
                    $book_quantity = $query1->book_quantity;
                    $book_price = $query1->book_price;
                    $book_description = $query1->book_description;

                    $query2 = BookStoreUser::where('user_id', $store_user_id)->first();
                    if($query2){
                        $store_address = $query2->store_address;
                    }

                    $query3 = User::where('id', $store_user_id)->first();
                    if($query3){
                        $store_name = $query3->first_name." ".$query3->last_name;
                        $store_email = $query3->email;
                        $store_phone = $query3->phone;
                    }

                    $query4 = BookCategory::where('id', $book_category)->first();
                    if($query4){
                        $store_book_category = $query4->name;
                    }

                    $data['store'] = [];

                    $data['store']['name'] = $store_name;
                    $data['store']['email'] = $store_email;
                    $data['store']['phone'] = $store_phone;
                    $data['store']['address'] = $store_address;
                    $data['store']['id'] = $store_user_id;

                    $data['book'] = [];

                    $data['book']['name'] = $book_name;
                    $data['book']['author_name'] = $book_author_name;
                    $data['book']['isbn'] = $book_isbn;
                    $data['book']['cover'] = $book_cover;
                    $data['book']['quantity'] = $book_quantity;
                    $data['book']['price'] = $book_price;
                    $data['book']['description'] = $book_description;
                    $data['book']['category'] = $store_book_category;
                    $data['book']['id'] = $book_id;

                    $data['request'] = [];

                    $data['request']['name'] = $name;
                    $data['request']['email'] = $email;
                    $data['request']['phone'] = $phone;
                    $data['request']['address'] = $address;
                    $data['request']['quantity'] = $quantity;
                    $data['request']['id'] = $id;
                    array_push($all_book_stores, $data);
                    
                }
                
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Stores Request',
            'data' => [
                'stores' => $all_book_stores
            ]
        ], Response::HTTP_OK);
    }

    public function bookrequestbookstore(BookStoresGetRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all_book_stores = [];

        $query = DB::table('book_store')
        ->where(
            [
                ['id', '=', $request->book_store_id],
            ]
        )
        ->first();
        if($query){

            $data =  [];

            $store_user_id = $query->store_user_id;
            $book_name = $query->book_name;
            $book_author_name = $query->book_author_name;
            $book_isbn = $query->book_isbn;
            $book_cover = $query->book_cover;
            $book_category = $query->book_category;
            $book_quantity = $query->book_quantity;
            $book_price = $query->book_price;
            $book_description = $query->book_description;

            $query2 = BookStoreUser::where('user_id', $store_user_id)->first();
            if($query2){
                $store_address = $query2->store_address;
            }

            $query3 = User::where('id', $store_user_id)->first();
            if($query3){
                $store_name = $query3->first_name." ".$query3->last_name;
                $store_email = $query3->email;
                $store_phone = $query3->phone;
            }

            $query4 = BookCategory::where('id', $book_category)->first();
            if($query4){
                $store_book_category = $query4->name;
            }

            $data['store'] = [];

            $data['store']['name'] = $store_name;
            $data['store']['email'] = $store_email;
            $data['store']['phone'] = $store_phone;
            $data['store']['address'] = $store_address;
            $data['store']['id'] = $store_user_id;

            $data['book'] = [];

            $data['book']['name'] = $book_name;
            $data['book']['author_name'] = $book_author_name;
            $data['book']['isbn'] = $book_isbn;
            $data['book']['cover'] = $book_cover;
            $data['book']['quantity'] = $book_quantity;
            $data['book']['price'] = $book_price;
            $data['book']['description'] = $book_description;
            $data['book']['category'] = $store_book_category;
            $data['book']['id'] = $request->book_store_id;

            $data['requests'] = [];

            $query0 = DB::table('book_store_request')
            ->select('name', 'email', 'phone', 'address', 'id', 'quantity', 'status', 'id', 'book_id')
            ->where(
            [
                ['book_id', '=', $query->id],
            ]
            )
            ->get();
            if($query0){
                foreach($query0 as $request_book){
                    $name = $request_book->name;
                    $email = $request_book->email;
                    $phone = $request_book->phone;
                    $address = $request_book->address;
                    $quantity = $request_book->quantity;
                    $id = $request_book->id;

                    $data['requests']['request']['name'] = $name;
                    $data['requests']['request']['email'] = $email;
                    $data['requests']['request']['phone'] = $phone;
                    $data['requests']['request']['address'] = $address;
                    $data['requests']['request']['quantity'] = $quantity;
                    $data['requests']['request']['id'] = $id;
                    
                }
            }
            array_push($all_book_stores, $data);
        }

        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Stores Request',
            'data' => [
                'stores' => $all_book_stores
            ]
        ], Response::HTTP_OK);
    }
}

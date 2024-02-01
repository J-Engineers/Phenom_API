<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\BookStore;
use App\Models\BookRequest;
use Illuminate\Support\Str;
use App\Models\BookCategory;
use App\Models\BookStoreUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BookStoreRandomRequest;
use App\Http\Requests\Admin\BookStoreRequest;
use App\Http\Requests\Admin\BookStoreRequests;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\BookRequestRequest;
use App\Http\Requests\Admin\BookStoreBookRequest;
use App\Http\Requests\Admin\BookStoreBooksRequest;
use App\Http\Requests\Admin\BookStoreCategoryRequest;
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
            foreach($query0 as $user1){
                $first_name = $user1->first_name;
                $last_name = $user1->last_name;
                $address = $user1->address;
                $phone = $user1->phone;
                $id = $user1->id;
                $email = $user1->email;
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
                
                $all_books = [];
                $books = DB::table('book_store')
                ->select(
                    'id', 'book_name', 'book_author_name', 'book_isbn', 
                    'book_cover', 'book_category as book_category_id', 'book_quantity',
                    'book_price', 'book_description', 'status'
                )
                ->where(
                    [
                        ['store_user_id', '=', $id]
                    ]
                )
                ->get();
                if($books){
                    foreach($books as $book){
        
                        $books_category = BookCategory::where(
                            [
                                ['id', '=', $book->book_category_id],
                            ]
                        )->first();
        
                        $data1['id'] = $book->id;
                        $data1['name'] = $book->book_name;
                        $data1['author'] = $book->book_author_name;
                        $data1['img'] = $book->book_cover;
                        $data1['price'] = $book->book_price;
                        $data1['desc'] = $book->book_description;
                        $data1['category'] = $books_category->name;
                        $data1['quantity'] = $book->book_quantity;
                        $data1['isbn'] = $book->book_isbn;
                        
                        $data1['status'] = (isset($book->status) OR $book->status == 0)?'Not Approved':'Approved';
                        
                        
                        array_push($all_books, $data1);
        
                    }
                }
                $data['books'] = $all_books;
                array_push($all_book_stores, $data);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Stores 1',
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
            foreach($query0 as $user1){
                $first_name = $user1->first_name;
                $last_name = $user1->last_name;
                $address = $user1->address;
                $phone = $user1->phone;
                $id = $user1->id;
                $email = $user1->email;
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

                $all_books = [];
                $books = DB::table('book_store')
                ->select(
                    'id', 'book_name', 'book_author_name', 'book_isbn', 
                    'book_cover', 'book_category as book_category_id', 'book_quantity',
                    'book_price', 'book_description', 'status'
                )
                ->where(
                    [
                        ['store_user_id', '=', $id]
                    ]
                )
                ->get();
                if($books){
                    foreach($books as $book){
        
                        $books_category = BookCategory::where(
                            [
                                ['id', '=', $book->book_category_id],
                            ]
                        )->first();
        
                        $data1['id'] = $book->id;
                        $data1['name'] = $book->book_name;
                        $data1['author'] = $book->book_author_name;
                        $data1['img'] = $book->book_cover;
                        $data1['price'] = $book->book_price;
                        $data1['desc'] = $book->book_description;
                        $data1['category'] = $books_category->name;
                        $data1['quantity'] = $book->book_quantity;
                        $data1['isbn'] = $book->book_isbn;
                        
                        $data1['status'] = (isset($book->status) OR $book->status == 0)?'Not Approved':'Approved';
                        
                        
                        array_push($all_books, $data1);
        
                    }
                }
                $data['books'] = $all_books;
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

    public function bookstoreAddCategory(BookStoreCategoriesRequest $request){
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
        ->where(
            [
                ['name', '=', $request->name],
            ]
        )
        ->first();
        if($query){

            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Category Already exist'
            ], Response::HTTP_NOT_FOUND);
        }
        $category = BookCategory::create(
            [
                'id' => (string)Str::uuid(),
                'name' => $request->name
            ]
        );
        
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store Category Created',
            'data' => [
                'category' => $category
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

        $query = BookCategory::where(
            [
                ['id', '=', $request->category_id],
            ]
        )
        ->first();
        if(!$query){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Store Category Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $query->update(
            ['name' => $request->name]
        );
        
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
                $status = (isset($request_book->status) OR $request_book->status == 0)?'Not Delivered':'Delivered';
                $quantity = $request_book->quantity;
                $id = $request_book->id;

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
                    
                    $store_address = '';
                    $query2 = BookStoreUser::where('user_id', $store_user_id)->first();
                    if($query2){
                        $store_address = $query2->store_address;
                    }
                    $store_name = '';
                    $store_email = '';
                    $store_phone = '';

                    $query3 = User::where('id', $store_user_id)->first();
                    if($query3){
                        $store_name = $query3->first_name." ".$query3->last_name;
                        $store_email = $query3->email;
                        $store_phone = $query3->phone;
                    }
                    $store_book_category = '';

                    $query4 = BookCategory::where('id', $book_category)->first();
                    if($query4){
                        $store_book_category = $query4->name;
                    }

                    $data['store'] = [];

                    $data['store']['name'] = $store_name;
                    $data['store']['email'] = $store_email;
                    $data['store']['phone'] = $store_phone;
                    $data['store']['address'] = $store_address;
                    $data['store']['id'] = $book_id;

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
                    $data['request']['status'] = $status;
                    $data['request']['price'] = (int)$quantity * (int)$book_price;
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
                $quantity = $request_book->quantity;
                $id = $request_book->id;
                $book_id = $request_book->book_id;
                $status = (isset($request_book->status) OR $request_book->status == 0)?'Not Delivered':'Delivered';

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
                    
                    
                    $store_address = '';
                    $store_name = '';
                    $store_phone = '';
                    $store_email = '';
                    $store_book_category = '';
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
                    $data['store']['id'] = $book_id;

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
                    $data['request']['status'] = $status;
                    $data['request']['price'] = (int)$quantity * (int)$book_price;
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

            // dd($query->book_description);
            
            $store_address = '';
            $store_name = '';
            $store_phone = '';
            $store_email = '';
            $store_book_category = '';

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
            $data['store']['id'] = $request->book_store_id;

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
                    $status = (isset($request_book->status) OR $request_book->status == 0)?'Not Delivered':'Delivered';

                    $data['requests']['request']['name'] = $name;
                    $data['requests']['request']['email'] = $email;
                    $data['requests']['request']['phone'] = $phone;
                    $data['requests']['request']['address'] = $address;
                    $data['requests']['request']['quantity'] = $quantity;
                    $data['requests']['request']['id'] = $id;
                    $data['requests']['request']['status'] = $status;
                    $data['requests']['request']['price'] = (int)$quantity * (int)$book_price;

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

    public function bookrequests(BookStoreRequests $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = DB::table('book_store_random_requests')
        ->select('name', 'email', 'phone', 'address', 'book_name', 'book_author', 'id', 'status')
        ->get();
        if($query0){
            foreach($query0 as $request_book){
                $name = $request_book->name;
                $email = $request_book->email;
                $phone = $request_book->phone;
                $address = $request_book->address;
                $book_name = $request_book->book_name;
                $book_author = $request_book->book_author;
                $id = $request_book->id;
                $status = (isset($request_book->status) OR $request_book->status == 0)?'Not Delivered':'Delivered';

                $all_book_stores = [];

                $all_book_stores['name'] = $name;
                $all_book_stores['email'] = $email;
                $all_book_stores['phone'] = $phone;
                $all_book_stores['address'] = $address;
                $all_book_stores['book_name'] = $book_name;
                $all_book_stores['book_author'] = $book_author;
                $all_book_stores['status'] = $status;
                $all_book_stores['id'] = $id;

                array_push($all, $all_book_stores);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book  Requests',
            'data' => [
                'stores' => $all
            ]
        ], Response::HTTP_OK);
    }

    public function bookrequest(BookStoreGetRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = DB::table('book_store_random_requests')
        ->select('name', 'email', 'phone', 'address', 'book_name', 'book_author', 'id', 'status')
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
                $book_name = $request_book->book_name;
                $book_author = $request_book->book_author;
                $id = $request_book->id;
                $status = (isset($request_book->status) OR $request_book->status == 0)?'Not Delivered':'Delivered';

                $all_book_stores = [];

                $all_book_stores['name'] = $name;
                $all_book_stores['email'] = $email;
                $all_book_stores['phone'] = $phone;
                $all_book_stores['address'] = $address;
                $all_book_stores['book_name'] = $book_name;
                $all_book_stores['book_author'] = $book_author;
                $all_book_stores['status'] = $status;
                $all_book_stores['id'] = $id;

                array_push($all, $all_book_stores);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book  Request',
            'data' => [
                'stores' => $all
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorebooks(BookStoreBooksRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookStore::all();
        if($query0){
            foreach($query0 as $request_book){
                $store_user_id  = $request_book->store_user_id;
                $book_name  = $request_book->book_name;
                $book_author_name  = $request_book->book_author_name;
                $book_isbn  = $request_book->book_isbn;
                $book_cover  = $request_book->book_cover;
                $book_category  = $request_book->book_category;
                $book_quantity  = $request_book->book_quantity;
                $book_price  = $request_book->book_price;
                $book_description  = $request_book->book_description;
                $status  = (isset($request_book->status) OR $request_book->status == 0)?'Not Approved':'Approved';

                $all_book_stores = [];
                $book_category_name = '';
                $book_category_query = BookCategory::where('id', $book_category)->first();
                if($book_category_query){
                    $book_category_name = $book_category_query->name;
                }
                
                
                $bookshop_name = '';
                $bookshop_contact = '';
                $bookshop_address = '';
                $bookshop_photo = '';

                $book_shop_user_query = User::where('id', $store_user_id)->first();
                if($book_shop_user_query){
                    $bookshop_name = $book_shop_user_query->first_name." ".$book_shop_user_query->last_name;
                    $bookshop_contact = $book_shop_user_query->phone;
                    $bookshop_address = $book_shop_user_query->address;
                    $bookshop_photo = $book_shop_user_query->photo;
                }
                


                $all_book_stores['book_name'] = $book_name;
                $all_book_stores['book_author_name'] = $book_author_name;
                $all_book_stores['book_isbn'] = $book_isbn;
                $all_book_stores['book_cover'] = $book_cover;
                $all_book_stores['book_quantity'] = $book_quantity;
                $all_book_stores['book_description'] = $book_description;
                $all_book_stores['book_price'] = $book_price;
                $all_book_stores['book_status'] = $status;
                $all_book_stores['book_category'] = $book_category_name;
                $all_book_stores['book_publisher_name'] = $bookshop_name;
                $all_book_stores['book_publisher_contact'] = $bookshop_contact;
                $all_book_stores['book_publisher_address'] = $bookshop_address;
                $all_book_stores['book_publisher_photo'] = $bookshop_photo;
                $all_book_stores['id'] = $request_book->id;

                array_push($all, $all_book_stores);
            }
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store',
            'data' => [
                'books' => $all
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorebook(BookStoreBookRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $request_book = BookStore::where('id', $request->book_id)->first();
        if($request_book){
            
                $store_user_id  = $request_book->store_user_id;
                $book_name  = $request_book->book_name;
                $book_author_name  = $request_book->book_author_name;
                $book_isbn  = $request_book->book_isbn;
                $book_cover  = $request_book->book_cover;
                $book_category  = $request_book->book_category;
                $book_quantity  = $request_book->book_quantity;
                $book_price  = $request_book->book_price;
                $book_description  = $request_book->book_description;
                $status  = (isset($request_book->status) OR $request_book->status == 0)?'Not Approved':'Approved';

                $all_book_stores = [];

                $all_book_stores = [];
                $book_category_name = '';
                $book_category_query = BookCategory::where('id', $book_category)->first();
                if($book_category_query){
                    $book_category_name = $book_category_query->name;
                }
                
                
                $bookshop_name = '';
                $bookshop_contact = '';
                $bookshop_address = '';
                $bookshop_photo = '';

                $book_shop_user_query = User::where('id', $store_user_id)->first();
                if($book_shop_user_query){
                    $bookshop_name = $book_shop_user_query->first_name." ".$book_shop_user_query->last_name;
                    $bookshop_contact = $book_shop_user_query->phone;
                    $bookshop_address = $book_shop_user_query->address;
                    $bookshop_photo = $book_shop_user_query->photo;
                }
                


                $all_book_stores['book_name'] = $book_name;
                $all_book_stores['book_author_name'] = $book_author_name;
                $all_book_stores['book_isbn'] = $book_isbn;
                $all_book_stores['book_cover'] = $book_cover;
                $all_book_stores['book_quantity'] = $book_quantity;
                $all_book_stores['book_description'] = $book_description;
                $all_book_stores['book_price'] = $book_price;
                $all_book_stores['book_status'] = $status;
                $all_book_stores['book_category'] = $book_category_name;
                $all_book_stores['book_publisher_name'] = $bookshop_name;
                $all_book_stores['book_publisher_contact'] = $bookshop_contact;
                $all_book_stores['book_publisher_address'] = $bookshop_address;
                $all_book_stores['book_publisher_photo'] = $bookshop_photo;
                $all_book_stores['id'] = $request_book->id;

                array_push($all, $all_book_stores);
            
        }
        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Store',
            'data' => [
                'book' => $all
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorebookapprove(BookStoreBookRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookStore::where('id', $request->book_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 1
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Approved',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorebookrevoke(BookStoreBookRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookStore::where('id', $request->book_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 0
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Declined',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function bookstorebookremove(BookStoreBookRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0 = BookStore::where('id', $request->book_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        BookStore::where('id', $request->book_id)->delete();
        
       

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Removed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function completeRequest(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 1
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Delivered',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function reopenRequest(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 0
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Reopened',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function removeRequest(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $query0 = BookRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        BookRequest::where('id', $request->request_id)->delete();
       

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Removed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }

    public function completeRequest1(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookStoreRandomRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 1
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Delivered',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function reopenRequest1(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }

        $all = [];
        $query0 = BookStoreRandomRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0->update([
            'status' => 0
        ]);

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Reopened',
            'data' => [
                'book' => $query0
            ]
        ], Response::HTTP_OK);
    }

    public function removeRequest1(BookRequestRequest $request){
        $request->validated();

        $user = auth()->user();
        if(!$user->is_admin === true){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_NOT_FOUND);
        }
        $query0 = BookStoreRandomRequest::where('id', $request->request_id)->first();
        if(!$query0){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Bad request'
            ], Response::HTTP_NOT_FOUND);
        }
        BookStoreRandomRequest::where('id', $request->request_id)->delete();
       

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Book Request Removed',
            'data' => [
            ]
        ], Response::HTTP_OK);
    }
}

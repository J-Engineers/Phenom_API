<?php

namespace App\Http\Controllers\BookStore;

use Storage;

use App\Models\User;
use App\Models\BookStore;
use Illuminate\Support\Str;
use App\Models\BookCategory;
use App\Models\BookStoreUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\BookStore\BookStoreBookRequest;
use App\Http\Requests\BookStore\BookStoreBooksRequest;
use App\Http\Requests\BookStore\BookStoreSignUpRequest;
use App\Http\Requests\BookStore\BookStoreAddBookRequest;
use App\Http\Requests\BookStore\BookStoreBookUpdateRequest;

class RegisterBookStoreController extends Controller
{
    public function signup(BookStoreSignUpRequest $request){
        $request->validated();

        if(auth()->user()){
            auth()->user()->tokens()->delete();
        }

        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $fields = Validator::make($request->all(), [
                'book_category_id_'.$i_subjects => 'required|string|exists:store_category,id',
                'book_name_'.$i_subjects => 'required|string',
                'book_author_'.$i_subjects => 'required|string',
                'book_isbn_'.$i_subjects => 'required|string',
                "book_price_".$i_subjects  => 'required|string',
                "book_quantity_".$i_subjects  => 'required|string',
                "book_description_".$i_subjects  => 'required|string',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return

            if(!$request->hasfile('book_cover_'.$i_subjects))
            {
                return response()->json([
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'status' => 'error',
                    'message' => 'File not Found, upload a file for the cover of this book with the name book_cover_'.$i_subjects,
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if ($user) {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'Email Taken, Kindly login or recover your passward',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $temporal_password = mt_rand(100000, 999999);

        $verify_token = mt_rand(100000, 999999);
        $to_name = $request->firstname." ".$request->lastname;
        $to_email = $request->email;
        $data = array(
           "name"=> $request->firstname." ".$request->lastname,
           "body" => "Welcome to Phenom Platform, We are glad you have requested to enroll as a Book Store Owner in our organization. 
                    We shall review your request and communicate with you further. Your temporal password is ".$temporal_password.", You are free to change it later.",
           "link" => env('APP_URL').'user/login'
        );
       
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
           $message->to($to_email, $to_name)->subject("Phenom Book Stores Registration");
           $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
        })){

            return response()->json([
               'status_code' => Response::HTTP_NOT_FOUND,
               'status' => 'error',
               'message' => 'Mail Not Sent, try again later',
               'data' => $request
            ], Response::HTTP_NOT_FOUND);
        }

        $password = Hash::make($temporal_password);
        $user = User::create([
            'id' => (string)Str::uuid(),
            'user_name' => str_split($request->firstname)[0]." ".str_split($request->lastname)[0],
            'email' => $request->email,
            'is_admin' => false,
            'activate' => true,
            'password' => $password,
            'phone' => $request->phone,
            'verify_token' => '0',
            'verify_email' => true,
            'email_verified_at' => Carbon::now(),
            'title' => "Mr./Mrs.",
            'first_name' => $request->firstname,
            'last_name' => $request->lastname,
            'gender' => 'male or femal',
            'user_type' => 'bookshop',
            'address' => $request->address,
        ]);

        if(!$user->id){
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'User not created',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $store = BookStoreUser::create([
            'id' => (string)Str::uuid(),
            'store_address' => $request->book_store_address,
            'user_id' => $user->id,
        ]);

        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $book_category_id = "book_category_id_".$i_subjects;
            $book_name = "book_name_".$i_subjects;
            $book_author = "book_author_".$i_subjects;
            $book_isbn = "book_isbn_".$i_subjects;
            $book_cover = "book_cover_".$i_subjects;
            $book_price = "book_price_".$i_subjects;
            $book_quantity = "book_quantity_".$i_subjects;
            $book_description = "book_description_".$i_subjects;

            $file = $request->file($book_cover);
            $name=time().$file->getClientOriginalName();
            $filePath = 'images/' . $name;
            $disk = Storage::disk('s3');
            $disk->put($filePath, file_get_contents($file));
            $base_path = $disk->url($filePath);

            BookStore::create([
                'id' => (string)Str::uuid(),
                'store_user_id' => $user->id,
                'book_name' => $request->$book_name,
                'book_author_name' => $request->$book_author,
                'book_isbn' => $request->$book_isbn,
                'book_cover' => $base_path,
                'book_category' => $request->$book_category_id,
                'book_quantity' => $request->$book_quantity,
                'book_price' => $request->$book_price,
                'book_description' => $request->$book_description,
                'status' => '0',
            ]);
        }

        $all_books = [];

        $books = DB::table('book_store')
        ->select(
            'id', 'book_name', 'book_author_name', 'book_isbn', 
            'book_cover', 'book_category as book_category_id', 'book_quantity',
            'book_price', 'book_description', 'status',
        )
        ->where(
            [
                ['store_user_id', '=', $user->id]
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
                'message' => 'User signed up successfully',
                'data' => [
                    'user' => $user,
                    'store' => $store,
                    'books' => $all_books,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function books(BookStoreBooksRequest $request){
        $request->validated();

        $auth = auth()->user();

        $user = User::where('id', $auth->id)->first();
        $store = BookStoreUser::where('user_id', $auth->id)->first();

        $all_books = [];

        $books = DB::table('book_store')
        ->select(
            'id', 'book_name', 'book_author_name', 'book_isbn', 
            'book_cover', 'book_category as book_category_id', 'book_quantity',
            'book_price', 'book_description', 'status',
        )
        ->where(
            [
                ['store_user_id', '=', $auth->id]
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
                'message' => 'User data',
                'data' => [
                    'store' => $store,
                    'books' => $all_books,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function book(BookStoreBookRequest $request){
        $request->validated();

        $auth = auth()->user();

        $user = User::where('id', $auth->id)->first();
        $store = BookStoreUser::where('user_id', $auth->id)->first();

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
                'message' => 'User data',
                'data' => [
                    'store' => $store,
                    'books' => $all_books,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function book_updates(BookStoreBookUpdateRequest $request){
        $request->validated();

        $auth = auth()->user();

        $user = User::where('id', $auth->id)->first();
        $store = BookStoreUser::where('user_id', $auth->id)->first();

        $books = BookStore::where('id',  $request->book_id)->first();
        if(!$books){
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Book Not Found'
            ], Response::HTTP_NOT_FOUND);
        }

        $books->update(
            [
                'book_name' => $request->name,
                'book_author_name' => $request->author,
                'book_isbn' => $request->isbn,
                'book_quantity' => $request->quantity,
                'book_price' => $request->price,
                'book_description' => $request->description,
            ]
        );
        
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Book Updated',
                'data' => [
                    'store' => $store,
                    'books' => $books,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function book_add(BookStoreAddBookRequest $request){
        $request->validated();

        $auth = auth()->user();

        $user = User::where('id', $auth->id)->first();
        $store = BookStoreUser::where('user_id', $auth->id)->first();


        if(!$request->hasfile('book_cover'))
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'File not Found, upload a file for the cover of this book with the name book_cover',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $book = BookStore::where(
            [
                ['book_name', '=', $request->book_name],
                ['book_category', '=', $request->book_category_id],
                ['book_isbn', '=', $request->book_isbn],
            ]
        )->first();
        if($book && $book->num_rows > 0)
        {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'status' => 'error',
                'message' => 'Book name already exist',
            ], Response::HTTP_UNAUTHORIZED);
        }


        $file = $request->file('book_cover');
        $name=time().$file->getClientOriginalName();
        $filePath = 'images/' . $name;
        $disk = Storage::disk('s3');
        $disk->put($filePath, file_get_contents($file));
        $base_path = $disk->url($filePath);

        BookStore::create([
            'id' => (string)Str::uuid(),
            'store_user_id' => $user->id,
            'book_name' => $request->book_name,
            'book_author_name' => $request->book_author_name,
            'book_isbn' => $request->book_isbn,
            'book_cover' => $base_path,
            'book_category' => $request->book_category,
            'book_quantity' => $request->book_quantity,
            'book_price' => $request->book_price,
            'book_description' => $request->book_description,
            'status' => '0',
        ]);

        
        
        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Book Added',
                'data' => [
                    'store' => $store,
                ]
            ], Response::HTTP_CREATED
        );
    }

    public function book_remove(BookStoreBookRequest $request){

        $request->validated();

        BookStore::where('id', $request->book_id)->delete();

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Record Removed',
                'data' => [
                ]
            ], Response::HTTP_CREATED
        );
    }
}
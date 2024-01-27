<?php

namespace App\Http\Controllers\BookStore;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\BookStore\BookStoreRequestBookRequest;
use App\Models\BookRequest;
use App\Models\BookStoreRandomRequest;

class RequestBookController extends Controller
{
    public function request_book(BookStoreRequestBookRequest $request){
        $request->validated();

        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $fields = Validator::make($request->all(), [
                'book_id_'.$i_subjects => 'required|string|exists:book_store,id',
                "book_quantity_".$i_subjects  => 'required|string',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
        }

        $total_price = 0;

        $message = '';
        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $book_id = "book_id_".$i_subjects;
            $book_quantity = "book_quantity_".$i_subjects;

            $book = DB::table('book_store')
            ->select('book_price', 'book_name', 'book_author_name')
            ->where('id',  $request->$book_id)->first();
            if($book){

                $total_price += ((int)$book->book_price * (int)$request->$book_quantity);

                $message .= $book->book_name. " By " . $book->book_author_name . ", ";

                BookRequest::create([
                    'id' => (string)Str::uuid(),
                    'name' => $request->firstname." ".$request->lastname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'book_id' => $request->$book_id,
                    'quantity' => $request->$book_quantity,
                    'status' => '0',
                ]);

            }
            
        }

        if($total_price > 0){
            $to_name = $request->firstname." ".$request->lastname;
            $to_email = $request->email;
            $data = array(
               "name"=> $request->firstname." ".$request->lastname,
               "body" => "Welcome to Phenom Platform, We are glad you have requested to buy books from our Book Stores. 
                        We shall review your request and communicate with you further. Your have ordered the following 
                        books : ".$message.". The total price is ".number_format($total_price, 2).". We shall get back to you after verifying the availability and delivery of the books. Thank you for trusting us."
                        ,
               "link" => env('APP_URL')
            );
           
            if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
               $message->to($to_email, $to_name)->subject("Phenom Book Stores Request");
               $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
            })){
    
                return response()->json([
                   'status_code' => Response::HTTP_NOT_FOUND,
                   'status' => 'error',
                   'message' => 'Mail Not Sent, try again later',
                   'data' => $request
                ], Response::HTTP_NOT_FOUND);
            }
        }else{
            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Request Failed',
                'data' => []
             ], Response::HTTP_NOT_FOUND);
        }

       

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'User sent request successfully',
                'data' => []
            ], Response::HTTP_CREATED
        );
    }

    public function request_book_random(BookStoreRequestBookRequest $request){
        $request->validated();

        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $fields = Validator::make($request->all(), [
                'book_name_'.$i_subjects => 'required|string',
                "book_author_".$i_subjects  => 'required|string',
            ]); // request body validation rules
    
            if($fields->fails()){
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'status' => 'error',
                    'message' => $fields->messages(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
            } // request body validation failed, so lets return
        }


        $message = '';
        for ($i_subjects=1; $i_subjects <= $request->total_books; $i_subjects++) {

            $book_name = "book_name_".$i_subjects;
            $book_author = "book_author_".$i_subjects;

            $message .= $request->$book_name. " By " . $request->$book_author . ", ";

            BookStoreRandomRequest::create([
                'id' => (string)Str::uuid(),
                'name' => $request->firstname." ".$request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'book_name' => $request->$book_name,
                'book_author' => $request->$book_author,
                'status' => '0',
            ]);
        }

        $to_name = $request->firstname." ".$request->lastname;
        $to_email = $request->email;
        $data = array(
            "name"=> $request->firstname." ".$request->lastname,
            "body" => "Welcome to Phenom Platform, We are glad you have requested to buy books from our Book Stores. 
                    We shall review your request and communicate with you further. Your have ordered the following 
                    books : ".$message. "We shall get back to you after verifying the availability and delivery of the
                     books. Thank you for trusting us."
                    ,
            "link" => env('APP_URL')
        );
        
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)->subject("Phenom Book Stores Request");
            $message->from(env("MAIL_USERNAME", "jeorgejustice@gmail.com"), "Welcome");
        })){

            return response()->json([
                'status_code' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Mail Not Sent, try again later',
                'data' => $request
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(
            [
                'status_code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'User sent request successfully',
                'data' => []
            ], Response::HTTP_CREATED
        );
    }
}
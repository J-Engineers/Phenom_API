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
                "p1" => "Welcome to ".env('APP_NAME')." – your premier destination for educational excellence! 
                     We are thrilled to have you join our community dedicated to connecting learners, parents, tutors, schools, 
                     and bookstores seamlessly.At ".env('APP_NAME').", we offer a diverse range of services to cater to your educational needs:",
               "p2" => "Whether you're seeking online or in-person classes, 
                     we connect you with exceptional tutors closest to you who are committed to helping you achieve your learning goals.",
               "p3" => "Easily discover and connect with top-notch schools in your vicinity, 
                     ensuring you find the perfect educational institution that meets your requirements.",
               "p4" => "Access a vast array of books from reputable bookstores right at your 
                     fingertips. With just a few clicks, you can order the resources you need to enhance your learning journey.",
               "p5" => "By signing up with ".env('APP_NAME').", you've taken the first step towards unlocking a world of educational opportunities. 
                     Our user-friendly platform is designed to streamline your learning experience, providing you with the tools and resources 
                     necessary to succeed.",
               "p6" =>  "We shall review your request and communicate with you further. Your have ordered the following 
               books : ".$message.". The total price is ".number_format($total_price, 2).". We shall get back to you after 
               verifying the availability and delivery of the books. Thank you for trusting us.",
               "p7" => "We're here to support you every step of the way. Should you have any questions, feedback, or suggestions, please don't hesitate 
                     to reach out to our dedicated customer support team.",
               "p8" => "Once again, welcome to ".env('APP_NAME')."! Get ready to embark on an enriching learning journey unlike any other.",
               "p9" => "Best Regards,",
               "p10" => env('APP_NAME')." Team",
               "p11" => "P.S. Stay tuned for exciting updates, exclusive offers, and valuable educational insights delivered straight to your inbox!",
               
               "d1" => "1. Expert Tutor Connections via phenomtutors:",
               "d2" => "2. School Search and Connection via phenomconnect:",
               "d3" => "3. Effortless Book Procurement via the bookstore:",
               
                "link" => env('APP_URL')
             );
           
            if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
               $message->to($to_email, $to_name)->subject(env('APP_NAME')." Book Stores Request");
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
        $data = array(
            "name"=> $request->firstname." ".$request->lastname,
            "p1" => "Welcome to ".env('APP_NAME')." – your premier destination for educational excellence! 
                 We are thrilled to have you join our community dedicated to connecting learners, parents, tutors, schools, 
                 and bookstores seamlessly.At ".env('APP_NAME').", we offer a diverse range of services to cater to your educational needs:",
           "p2" => "Whether you're seeking online or in-person classes, 
                 we connect you with exceptional tutors closest to you who are committed to helping you achieve your learning goals.",
           "p3" => "Easily discover and connect with top-notch schools in your vicinity, 
                 ensuring you find the perfect educational institution that meets your requirements.",
           "p4" => "Access a vast array of books from reputable bookstores right at your 
                 fingertips. With just a few clicks, you can order the resources you need to enhance your learning journey.",
           "p5" => "By signing up with ".env('APP_NAME').", you've taken the first step towards unlocking a world of educational opportunities. 
                 Our user-friendly platform is designed to streamline your learning experience, providing you with the tools and resources 
                 necessary to succeed.",
           "p6" =>  " We shall review your request and communicate with you further. Your have ordered the following 
                books : ".$message. "We shall get back to you after verifying the availability and delivery of the
                books. Thank you for trusting us.",
           "p8" => "Once again, welcome to ".env('APP_NAME')."! Get ready to embark on an enriching learning journey unlike any other.",
           "p9" => "Best Regards,",
           "p10" => env('APP_NAME')." Team",
           "p11" => "P.S. Stay tuned for exciting updates, exclusive offers, and valuable educational insights delivered straight to your inbox!",
           
           "d1" => "1. Expert Tutor Connections via phenomtutors:",
           "d2" => "2. School Search and Connection via phenomconnect:",
           "d3" => "3. Effortless Book Procurement via the bookstore:",
           
            "link" => env('APP_URL')
         );
       
        
        if(!Mail::send("emails.registrationtutor", $data, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)->subject(env('APP_NAME')." Book Stores Request");
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
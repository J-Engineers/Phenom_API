<?php

namespace App\Http\Requests\BookStore;

use Illuminate\Foundation\Http\FormRequest;

class BookStoreSignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'book_store_address' => 'required|string',
            'total_books' => 'required|integer',
            'api_key' => [
                function ($attribute, $value, $fail)  {
                    if(!$value OR $value != env('API_KEY')){
                        $fail("Invalid API KEY");
                    }
                }
            ]
        ];
    }
}

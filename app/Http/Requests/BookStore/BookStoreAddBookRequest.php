<?php

namespace App\Http\Requests\BookStore;

use Illuminate\Foundation\Http\FormRequest;

class BookStoreAddBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_name' => 'required|string',
            'book_author_name' => 'required|string',
            'book_isbn' => 'required|string',
            'book_quantity' => 'required|string',
            'book_price' => 'required|string',
            'book_description' => 'required|string',
            'book_category' => 'required|string',
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

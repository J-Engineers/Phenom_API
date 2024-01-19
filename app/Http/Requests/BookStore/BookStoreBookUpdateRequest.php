<?php

namespace App\Http\Requests\BookStore;

use Illuminate\Foundation\Http\FormRequest;

class BookStoreBookUpdateRequest extends FormRequest
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
            'name' => 'required|string|email',
            'author' => 'required|string',
            'isbn' => 'required|string',
            'quantity' => 'required|string',
            'price' => 'required|string',
            'description' => 'required|string',
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

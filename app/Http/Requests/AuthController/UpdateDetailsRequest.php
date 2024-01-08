<?php

namespace App\Http\Requests\AuthController;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetailsRequest extends FormRequest
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
            'api_key' => [
                function ($attribute, $value, $fail)  {
                    if(!$value OR $value != env('API_KEY')){
                        $fail("Invalid API KEY");
                    }
                }
            ],
            'title' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'address' => 'required|string',
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'api_key.required' => 'API Key is required!',
            'api_key.string' => 'API Key must be a string',
            'title.required' => 'Title is required',
            'title.string' => 'Title must be a string',
            'first_name.required' => 'Firstname is required',
            'first_name.string' => 'Firstname must be a string',
            'last_name.required' => 'Lastname is required',
            'last_name.string' => 'Lastname must be a string',
            'gender.required' => 'Gender is required',
            'gender.string' => 'Gender must be a string',
            'address.required' => 'Address is required',
            'address.string' => 'Address must be a string',
        ];
    }
}

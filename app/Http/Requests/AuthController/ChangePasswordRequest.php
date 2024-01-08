<?php

namespace App\Http\Requests\AuthController;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'password' => 'required|string|min:6|confirmed',
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
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password_confirmation.required' => 'Password Confirmation is required',
            'password_confirmation.string' => 'Password Confirmation must be a string',
        ];
    }
}

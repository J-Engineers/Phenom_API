<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class LearnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'api_key' => [
                function ($attribute, $value, $fail)  {
                    if(!$value OR $value != env('API_KEY')){
                        $fail("Invalid API KEY");
                    }
                }
            ]
        ];
    }

    
    public function messages()
    {
        return [
            'api_key.required' => 'API Key is required!',
            'api_key.string' => 'API Key must be a string',
        ];
    }
}

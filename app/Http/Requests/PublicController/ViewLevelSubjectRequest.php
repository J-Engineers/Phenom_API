<?php

namespace App\Http\Requests\PublicController;

use Illuminate\Foundation\Http\FormRequest;

class ViewLevelSubjectRequest extends FormRequest
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
            'level_id' => ['required', 'string'],
            'subject_id' => ['required', 'string'],
            'api_key' => [
                function ($attribute, $value, $fail)  {
                    if(!$value OR $value != env('API_KEY')){
                        $fail("Invalid API KEY");
                    }
                }
            ]
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
            'level_id.required' => 'Level ID is required!',
            'level_id.string' => 'Level ID must be a string',
            'subject_id.required' => 'Subject ID is required!',
            'subject_id.string' => 'Subject ID must be a string',
        ];
    }
}

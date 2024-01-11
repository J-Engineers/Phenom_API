<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class RemoveLessonSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   
    public function rules(): array
    {
        return [
            'lesson_subject_id' => 'required|string|exists:lesson_subjects,id',
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
            'lesson_subject_id.required' => 'The lesson subject Id is required',
            'lesson_subject_id.string' => 'The lesson subject Idmust be a string',
            'lesson_subject_id.exists' => 'The lesson subject Id must be found in the lesson subject',
           
        ];
    }
}

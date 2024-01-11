<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class AddLearnerToLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learner_id' => 'required|string|exists:learners,id',
            'lesson_id' => 'required|string|exists:lessons,id',
            'description_of_learner' => 'required|string',
            'lesson_commence' => 'required|string',
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

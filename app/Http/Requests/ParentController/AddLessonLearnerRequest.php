<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class AddLessonLearnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learner_name' => 'required|string',
            'learner_dob' => 'required|string',
            'learner_gender' => 'required|string',
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

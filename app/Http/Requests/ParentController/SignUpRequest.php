<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
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
            'email' => 'required|string|email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'title' => 'required|string',
            'gender' => 'required|string',
            
            'how_did_you_know_about_us' => 'required|string',

            'learners_name' => 'required|string',
            'learners_dob' => 'required|string',
            'learners_gender' => 'required|string',

            'lesson_address' => 'required|string',
            'lesson_goals' => 'required|string',
            'lesson_mode' => 'required|string',
            'lesson_period' => 'required|string',
            'description_of_learner' => 'required|string',
            'lesson_commence' => 'required|string',
            'education_level_id' => 'required|string|exists:education_levels,id',
           
            'total_subjects' => 'required|integer',
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

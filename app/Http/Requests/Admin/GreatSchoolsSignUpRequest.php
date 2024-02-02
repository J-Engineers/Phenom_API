<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GreatSchoolsSignUpRequest extends FormRequest
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
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'state' => 'required|string',
            'localgovernment' => 'required|string',
            'description' => 'required|string',
            'population' => 'required|string',
            'male_or_female_or_both' => 'required|string',
            'day_or_boarding_or_both' => 'required|string',
            'private_or_government_or_both' => 'required|string',
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

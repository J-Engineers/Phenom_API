<?php

namespace App\Http\Requests\Schools;

use Illuminate\Foundation\Http\FormRequest;

class GreatSchoolGuestRequestRequest extends FormRequest
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
            'dob' => 'required|string',
            'gender' => 'required|string',
            'localgovernment' => 'required|string',
            'description' => 'required|string',
            'great_school_id' => 'required|string|exists:great_schools,id',
            'prev_school' => 'required|string',
            'picture' => 'required|file',
            'transcript' => 'required|file',
            'prev_school_note' => 'required|file',
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

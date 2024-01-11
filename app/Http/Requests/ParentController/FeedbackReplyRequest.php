<?php

namespace App\Http\Requests\ParentController;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   
    public function rules(): array
    {
        return [
            'feedback_id' => 'required|string|exists:lesson_feedback,id',
            'feedback_reply' => 'required|string',
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
            'feedback_reply.required' => 'Reply is required!',
            'feedback_reply.string' => 'Reply must be a string',
            'feedback_id.required' => 'Feedback Key is required!',
            'feedback_id.string' => 'Feedback Key must be a string',
            'feedback_id.exists' => 'Feedback Key should be valid',
        ];
    }
}

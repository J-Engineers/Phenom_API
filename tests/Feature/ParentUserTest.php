<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Helpers\Lesson;
use App\Models\Learner;
use App\Models\Lessons;
use App\Models\Subjects;
use App\Models\LessonDay;
use App\Models\ParentUser;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Models\LessonLearner;
use App\Models\LessonSubject;
use App\Models\LessonFeedback;
use App\Models\EducationLevels;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParentUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_user_registration_successful(): void
    {

        $education = EducationLevels::create([
            'uuid' => (string)Str::uuid(),
            'name' =>  "Secondary"
        ]);

        $subject = Subjects::create(
            [
                'uuid' => (string)Str::uuid(),
                'name' =>  "Biology",
                'education_levels_id' => $education->id
            ]
        );

        $day1 = LessonDay::create(
            [
                'uuid' => (string)Str::uuid(),
                'day_name' =>  "Monday",
            ]
        );

        $day2 = LessonDay::create(
            [
                'uuid' => (string)Str::uuid(),
                'day_name' =>  "Tuesday",
            ]
        );
       

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent/registration',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                
                'email' => 'user@gmail.com',
                'firstname' => 'Parent',
                'lastname' => 'User',
                'state' => 'Akwa Ibom',
                'country' => 'Nigeria',
                'address' => 'Uyo, Nigeria',
                'phone' => '08000000000',
                'title' => 'Mr.',
                'gender' => 'Male',
                
                'how_did_you_know_about_us' => 'Social Media',

                'learners_name' => 'Learner 1',
                'learners_dob' => '23-09-1999',
                'learners_gender' => 'Male',

                'lesson_address' => 'Uyo, Nigeria',
                'lesson_goals' => 'Improve on the childs understanding of mathematics',
                'lesson_mode' => 'Physical',
                'lesson_period' => '3 months',
                'description_of_learner' => 'Student is very playful',
                'lesson_commence' => '19th january, 2024',
                'education_level_id' => $education->id,

                'total_subjects' => 1,

                'subject_id_1' => $subject->id,
                'tutor_gender_1' => "Male",
                'tutor_type_1' => "Intermediate",

                'total_day_1' => 2,

                'day_id_1_1' => $day1->id,
                'day_hours_1_1' => "2hours",
                'start_time_1_1' => "11am",

                'day_id_2_1' => $day2->id,
                'day_hours_2_1' => "3hours",
                'start_time_2_1' => "1pm",
            ]
        );
    
        $response->assertStatus(201);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    'user' => [

                    ],
                    'parent' => [

                    ],
                    'learners' => [

                    ]
                ],
            ]
        );
    }

    public function test_parent_user_dashboard_successful(): void
    {

        $this->test_parent_user_registration_successful();
        $data = User::where('email', 'user@gmail.com')->first();
        $token = Sanctum::actingAs($data);

        $response = $this->json(
            'GET', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc=")
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    'user' => [

                    ],
                    'parent_details' => [

                    ],
                    'lessons' => [

                    ]
                ],
            ]
        );
    }

    public function test_parent_user_Learner_lessons_successful(): void
    {

        $this->test_parent_user_registration_successful();
        $data = User::where('email', 'user@gmail.com')->first();
        $parent_data = ParentUser::where('user_id', $data->id)->first();
        $token = Sanctum::actingAs($data);

        $response = $this->json(
            'GET', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent/lessons',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    'lessons' => [

                    ]
                ],
            ]
        );
    }

    public function test_parent_user_lesson_successful(): void
    {
        $this->test_parent_user_registration_successful();
        $data = User::where('email', 'user@gmail.com')->first();
        $parent_data = ParentUser::where('user_id', $data->id)->first();
        $token = Sanctum::actingAs($data);

        $learner = Learner::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson = Lessons::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson_learner = LessonLearner::where(
            [
                ['lesson_id', '=', $lesson->id],
                ['learner_id', '=', $learner->id],
            ]
        )->first();


        $lesson_subject = LessonSubject::where(
            [
                ['lesson_learner_id', '=', $lesson_learner->id],
            ]
        )->first();

        $response = $this->json(
            'GET', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent/lessons',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'lesson_subject_id' => $lesson_subject->id,
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    'lessons' => [
                    ]
                ],
            ]
        );
    }

    public function test_parent_user_lesson_send_feedback_successful(): void
    {
        $this->test_parent_user_registration_successful();
        $data = User::where('email', 'user@gmail.com')->first();
        $parent_data = ParentUser::where('user_id', $data->id)->first();
        $token = Sanctum::actingAs($data);

        $learner = Learner::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson = Lessons::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson_learner = LessonLearner::where(
            [
                ['lesson_id', '=', $lesson->id],
                ['learner_id', '=', $learner->id],
            ]
        )->first();


        $lesson_subject = LessonSubject::where(
            [
                ['lesson_learner_id', '=', $lesson_learner->id],
            ]
        )->first();

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent/lesson/feedback',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'lesson_subject_id' => $lesson_subject->id,
                'feedback' => "Please kindly know that my student is really getting better in the training, i need more of the trainings. Thank you.",
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                   
                ],
            ]
        );
    }

    public function test_parent_user_lesson_send_feedback_reply_successful(): void
    {
        $this->test_parent_user_lesson_send_feedback_successful();
        $data = User::where('email', 'user@gmail.com')->first();
        $parent_data = ParentUser::where('user_id', $data->id)->first();
        $token = Sanctum::actingAs($data);

        $learner = Learner::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson = Lessons::where(
            [
                ['parent_id', '=', $parent_data->id],
            ]
        )->first();

        $lesson_learner = LessonLearner::where(
            [
                ['lesson_id', '=', $lesson->id],
                ['learner_id', '=', $learner->id],
            ]
        )->first();


        $lesson_subject = LessonSubject::where(
            [
                ['lesson_learner_id', '=', $lesson_learner->id],
            ]
        )->first();

        $lesson_feedback = LessonFeedback::where(
            [
                ['lesson_subject_id', '=', $lesson_subject->id],
                ['user_id', '=', $data->id],
            ]
        )->first();

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'parent/lesson/feedback/reply',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'lesson_subject_id' => $lesson_subject->id,
                'feedback_id' => $lesson_feedback->id,
                'feedback_reply' => "Please kindly know that my student is really getting better in the training, i need more of the trainings. Thank you.",
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                   
                ],
            ]
        );
    }
}

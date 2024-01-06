<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Subjects;
use App\Models\LessonDay;
use App\Models\EducationLevels;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EducationLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_levels_exist_in_database(): void
    {

        EducationLevels::create([
            'name' => 'Crenche'
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/levels',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc='
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }

    public function test_education_level_exist_in_database(): void
    {

        $level = EducationLevels::create([
            'name' => 'Crenche'
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/level',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc=',
                'level_id' => $level->id,
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }

    public function test_education_subjects_exist_in_database(): void
    {

        $level = EducationLevels::create([
            'name' => 'Crenche'
        ]);

        $subject = Subjects::create([
            'name' => 'Biology',
            'education_levels_id' => $level->id
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/subjects',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc='
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }

    public function test_education_subject_exist_in_database(): void
    {

        $level = EducationLevels::create([
            'name' => 'Crenche'
        ]);

        $subject = Subjects::create([
            'name' => 'Biology',
            'education_levels_id' => $level->id
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/subjects',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc=',
                'subject_id' => $subject->id
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }

    public function test_education_subject_level_exist_in_database(): void
    {

        $level = EducationLevels::create([
            'name' => 'Crenche'
        ]);

        $subject = Subjects::create([
            'name' => 'Biology',
            'education_levels_id' => $level->id
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/level/subject',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc=',
                'subject_id' => $subject->id,
                'level_id' => $level->id,
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }

    public function test_education_day_exist_in_database(): void
    {

        LessonDay::create([
            'day_name' => 'Monday'
        ]);

        $response = $this->json(
            'GET', 
            'http://localhost:8000/api/v1/public/lesson/days',
            [
                'api_key' => 'base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc='
            ]
        );
    
        $response->assertStatus(201);
        $response->assertSee('success');
    }
}

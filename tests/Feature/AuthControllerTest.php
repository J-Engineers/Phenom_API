<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EducationLevels;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_level_exist_in_database(): void
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
}

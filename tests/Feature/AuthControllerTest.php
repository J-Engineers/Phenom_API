<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_login_successful(): void
    {

        $this->test_user_registration_successful();

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/login',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' => "user@gmail.com",
                'password' => "12345"
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    "access_token",
                    "email",
                    "id",
                    "is_admin",
                    "verification",
                    "user_type",
                ],
            ]
        );
    }

    public function test_user_registration_verification_email_successful(): void
    {
        

        $password = '12345';
        $password_hashed = Hash::make($password);
        $user = User::create([
            'uuid' => (string)Str::uuid(),
            'user_name' => 'User',
            'email' => 'user@gmail.com',
            'is_admin' => false,
            'activate' => true,
            'password' => $password_hashed,
            'phone' => '08000000000',
            'verify_token' => '12345',
            'verify_email' => false,
            'user_type' => 'admin',
        ]);

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/registration/verify',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' => $user->email,
                'verify_token' => (int)$user->verify_token
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    "access_token",
                    "email",
                    "is_admin",
                    "verification",
                ],
            ]
        );
    }

    public function test_user_registration_successful(): void
    {
       

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/registration',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' => 'user@gmail.com',
                'password' => '12345',
                'user_name' => 'user',
                'phone' => '08000000000',
                'org_id' => env('ORG_ID', "swatCat5MikrotikZssHr5Sha255"),
            ]
        );
    
        $response->assertStatus(201);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    "email",
                    "user_name",
                    "verify_token",
                    "id",
                    "is_admin",
                    "verification",
                    "user_type",
                ],
            ]
        );
    }

    public function test_user_forgot_password_successful(): void
    {
       $this->test_user_registration_successful();

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/password/forgot',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' => 'user@gmail.com',
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
                'data' =>  [
                    "token",
                ],
            ]
        );
    }

    public function test_user_reset_password_successful(): void
    {
        $this->test_user_registration_successful();

        $data = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/password/forgot',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' => 'user@gmail.com',
            ]
        );

        $response = $this->json(
            'POST', 
            env("APP_URL", "http://localhost:8000/api/v1/").'user/password/reset',
            [
                'api_key' => env("API_KEY", "base64:mrbHT4tAp2pe2lMYJfliwIugvVZkO7RSH7ojdfGJ9oc="),
                'email' =>  $data['data']['email'],
                'otp_token' => $data['data']['token'],
                'password' => '123456',
                'password_confirmation' => '123456',
            ]
        );
    
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'status_code',
                'status',
                'message',
            ]
        );
    }
}

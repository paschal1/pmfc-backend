<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
      // Prepare the registration data
      $data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '1234567890',
        'address' => 'Test Address',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Perform the registration POST request
    $response = $this->postJson('/api/register', $data);

    // Assert that the response was successful
    $response->assertStatus(201); // Or 201, based on your controller's response

    // Get the response data (user and token)
    $responseData = $response->json();

    // Assert that the response contains user and token
    $this->assertArrayHasKey('user', $responseData);
    $this->assertArrayHasKey('token', $responseData);

    // Assert that the token is valid
    $this->assertNotEmpty($responseData['token']);
    
    // Now authenticate using the token in subsequent requests
    $this->withHeaders([
        'Authorization' => 'Bearer ' . $responseData['token'],
    ]);

    // Perform an authenticated request (e.g., fetch user data)
    $userResponse = $this->getJson('/api/user'); // Make sure you have this route set up to return the user

    // Assert that the authenticated request returns correct user data
    $userResponse->assertStatus(201);
    $userResponse->assertJsonFragment([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    }
}

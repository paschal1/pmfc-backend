<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_can_create_contact_message()
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'message' => 'This is a test message.',
        ]);
    
        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['name' => 'John Doe']);
    }
    
}

<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function test_can_create_project()
    {
        $response = $this->postJson('/api/projects', [
            'title' => 'Test Project',
            'description' => 'This is a test project description.',
        ]);
    
        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['title' => 'Test Project']);
    }
    
}

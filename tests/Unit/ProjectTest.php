<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic unit test example.
     */
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

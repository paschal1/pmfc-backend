<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BlogControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_blog_creation()
{
    $response = $this->postJson('/api/blogs', [
        'title' => 'Test Blog',
        'content' => 'This is a test blog.',
        'author_id' => 1,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('blogs', ['title' => 'Test Blog']);
}

}

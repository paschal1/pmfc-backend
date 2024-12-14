<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TrainingManagementFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_can_add_training_program()
    {
        $trainingData = [
            'title' => 'Advanced Carpentry',
            'description' => 'Master advanced carpentry skills.',
            'start_date' => '2024-02-01',
            'end_date' => '2024-12-31',
        ];

        $response = $this->post('/api/training-programs', $trainingData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('training_programs', ['title' => 'Advanced Carpentry']);
    }

    public function test_can_enroll_students()
    {
        $trainee = Trainee::factory()->create();
        $trainingProgramId = 1;

        $response = $this->post("/api/training-programs/$trainingProgramId/enroll", ['trainee_id' => $trainee->id]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('training_program_trainee', ['trainee_id' => $trainee->id, 'training_program_id' => $trainingProgramId]);
    }
}

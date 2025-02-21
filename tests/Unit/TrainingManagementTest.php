<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\TrainingProgram;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrainingManagementTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function test_add_new_training_program()
    {
        $trainingData = [
            'title' => 'Carpentry Basics',
            'description' => 'Learn the basics of carpentry.',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $response = $this->post('/api/training-programs', $trainingData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('training_programs', ['title' => 'Carpentry Basics']);
    }

    public function test_manage_student_enrollments()
    {
        $trainee = Trainee::factory()->create();
        $trainingProgramId = 1; // Assume training program ID exists.

        $response = $this->post("/api/training-programs/$trainingProgramId/enroll", ['trainee_id' => $trainee->id]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('training_program_trainee', ['trainee_id' => $trainee->id, 'training_program_id' => $trainingProgramId]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingProgram;
use App\Models\Enrollment;
use App\Models\Student;

class EnrollmentController extends Controller
{
    /**
     * Enroll a student in a training program.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'training_program_id' => 'required|exists:training_programs,id',
            'enrollment_date' => 'required|date',
        ]);

        // Check if the student is already enrolled in the training program
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('training_program_id', $request->training_program_id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['message' => 'Student is already enrolled in this program.'], 409);
        }

        // Create the enrollment
        $enrollment = Enrollment::create([
            'student_id' => $request->student_id,
            'training_program_id' => $request->training_program_id,
            'enrollment_date' => $request->enrollment_date,
        ]);

        return response()->json(['message' => 'Enrollment successful.', 'enrollment' => $enrollment], 201);
    }

    /**
     * List all enrollments.
     */
    public function index()
    {
        $enrollments = Enrollment::with('student', 'trainingProgram')->get();

        return response()->json(['enrollments' => $enrollments], 200);
    }

    /**
     * Show a specific enrollment.
     */
    public function show($id)
    {
        $enrollment = Enrollment::with('student', 'trainingProgram')->findOrFail($id);

        return response()->json(['enrollment' => $enrollment], 200);
    }

    /**
     * Delete an enrollment.
     */
    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();

        return response()->json(['message' => 'Enrollment deleted successfully.'], 200);
    }
}



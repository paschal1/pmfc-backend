<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
   
    /**
     * Display a listing of training programs.
     */
    public function index()
    {
        $trainingPrograms = TrainingProgram::all();
        return response()->json(['trainingPrograms' => $trainingPrograms], 200);
    }

    /**
     * Store a new training program.
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create training program
        $trainingProgram = TrainingProgram::create($request->all());

        return response()->json(['message' => 'Training program created successfully', 'trainingProgram' => $trainingProgram], 201);
    }

    /**
     * Enroll a student in a training program.
     */
    public function enroll(Request $request, $trainingProgramId)
    {
        $trainingProgram = TrainingProgram::find($trainingProgramId);

        if (!$trainingProgram) {
            return response()->json(['message' => 'Training program not found'], 404);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Enroll student
        $enrollment = Enrollment::create([
            'training_program_id' => $trainingProgramId,
            'student_id' => $request->input('student_id'),
        ]);

        return response()->json(['message' => 'Student enrolled successfully', 'enrollment' => $enrollment], 201);
    }

    /**
     * Remove a student from a training program.
     */
    public function unenroll($trainingProgramId, $studentId)
    {
        $enrollment = Enrollment::where('training_program_id', $trainingProgramId)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Student unenrolled successfully'], 200);
    }

    /**
     * Delete a training program.
     */
    public function destroy($id)
    {
        $trainingProgram = TrainingProgram::find($id);

        if (!$trainingProgram) {
            return response()->json(['message' => 'Training program not found'], 404);
        }

        $trainingProgram->delete();

        return response()->json(['message' => 'Training program deleted successfully'], 200);
    }

}

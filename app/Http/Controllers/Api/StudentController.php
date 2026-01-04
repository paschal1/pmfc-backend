<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with('enrollments.trainingProgram')->get();
        return response()->json(['students' => $students], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer|min:18',
            'gender' => 'required|in:Male,Female,Other',
            'contact_number' => 'required|string|max:15',
            'email' => 'required|email|unique:students,email',
            'address' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'emergency_contact' => 'required|string|max:15',
            'previous_experience' => 'nullable|string',
            'joining_date' => 'required|date',
            'current_skill_level' => 'required|in:Beginner,Intermediate,Advanced',
            'goals' => 'nullable|string',
            'id_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'training_program_id' => 'required|exists:training_programs,id',
            'payment_method' => 'required|in:Bank Transfer,Paystack',
            'payment_reference' => 'nullable|string', // Paystack reference
            'payment_status' => 'required|in:Pending,Paid',
        ]);

        DB::beginTransaction();

        try {
            // Handle file uploads
            $idProofPath = $request->file('id_proof') 
                ? $request->file('id_proof')->store('uploads/id_proofs') 
                : null;
            $resumePath = $request->file('resume') 
                ? $request->file('resume')->store('uploads/resumes') 
                : null;

            // Create student
            $student = Student::create([
                'full_name' => $request->input('full_name'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender'),
                'contact_number' => $request->input('contact_number'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'date_of_birth' => $request->input('date_of_birth'),
                'emergency_contact' => $request->input('emergency_contact'),
                'previous_experience' => $request->input('previous_experience'),
                'joining_date' => $request->input('joining_date'),
                'program_duration' => '1 year',
                'current_skill_level' => $request->input('current_skill_level'),
                'goals' => $request->input('goals'),
                'id_proof' => $idProofPath,
                'resume' => $resumePath,
            ]);

            // Create enrollment
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'training_program_id' => $request->input('training_program_id'),
                'enrollment_date' => $request->input('joining_date'),
                'payment_method' => $request->input('payment_method'),
                'payment_reference' => $request->input('payment_reference'),
                'payment_status' => $request->input('payment_status'),
            ]);

            // Load relationships
            $enrollment->load('trainingProgram');

            DB::commit();

            return response()->json([
                'message' => 'Enrollment submitted successfully!',
                'student' => $student,
                'enrollment' => $enrollment,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Enrollment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with('enrollments.trainingProgram')->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json(['student' => $student], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:18',
            'gender' => 'sometimes|required|in:Male,Female,Other',
            'contact_number' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|unique:students,email,' . $id,
            'address' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date',
            'emergency_contact' => 'sometimes|required|string|max:15',
            'previous_experience' => 'nullable|string',
            'joining_date' => 'sometimes|required|date',
            'current_skill_level' => 'sometimes|required|in:Beginner,Intermediate,Advanced',
            'goals' => 'nullable|string',
            'id_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);
    
        $student = Student::findOrFail($id);
    
        // Handle file uploads
        if ($request->hasFile('id_proof')) {
            if ($student->id_proof) {
                Storage::delete($student->id_proof);
            }
            $student->id_proof = $request->file('id_proof')->store('uploads/id_proofs');
        }
    
        if ($request->hasFile('resume')) {
            if ($student->resume) {
                Storage::delete($student->resume);
            }
            $student->resume = $request->file('resume')->store('uploads/resumes');
        }
    
        // Update other fields
        $student->update([
            'full_name' => $request->input('full_name', $student->full_name),
            'age' => $request->input('age', $student->age),
            'gender' => $request->input('gender', $student->gender),
            'contact_number' => $request->input('contact_number', $student->contact_number),
            'email' => $request->input('email', $student->email),
            'address' => $request->input('address', $student->address),
            'date_of_birth' => $request->input('date_of_birth', $student->date_of_birth),
            'emergency_contact' => $request->input('emergency_contact', $student->emergency_contact),
            'previous_experience' => $request->input('previous_experience', $student->previous_experience),
            'joining_date' => $request->input('joining_date', $student->joining_date),
            'current_skill_level' => $request->input('current_skill_level', $student->current_skill_level),
            'goals' => $request->input('goals', $student->goals),
        ]);
    
        return response()->json(['message' => 'Student details updated successfully.', 'student' => $student], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully'], 200);
    }
}
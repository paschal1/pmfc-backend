<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enrollment = Student::all();
        return response()->json(['enrollment' => $enrollment], 200);
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
        ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        $idProofPath = $request->file('id_proof') ? $request->file('id_proof')->store('uploads/id_proofs') : null;
        $resumePath = $request->file('resume') ? $request->file('resume')->store('uploads/resumes') : null;

        $Enrollment = Student::create([
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

        return response()->json(['message' => 'Your have successfully Enrolled.', 'Enrollment' => $Enrollment], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Enrollment = Student::find($id);

        if (!$Enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        return response()->json(['Enrollment' => $Enrollment], 200);
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
    
        // Find the student record by ID
        $student = Student::findOrFail($id);
    
        // Handle file uploads, only replacing if new files are uploaded
        if ($request->hasFile('id_proof')) {
            if ($student->id_proof) {
                Storage::delete($student->id_proof); // Delete the old file
            }
            $student->id_proof = $request->file('id_proof')->store('uploads/id_proofs');
        }
    
        if ($request->hasFile('resume')) {
            if ($student->resume) {
                Storage::delete($student->resume); // Delete the old file
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
        $Enrollment = Student::find($id);

        if (!$Enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }

        $Enrollment->delete();

        return response()->json(['message' => 'Trainee deleted successfully'], 200);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory; // Ensure this is included
    protected $table = 'students';
    protected $fillable = [
        'full_name',
        'age',
        'gender',
        'contact_number',
        'email',
        'address',
        'date_of_birth',
        'emergency_contact',
        'previous_experience',
        'joining_date',
        'program_duration',
        'current_skill_level',
        'goals',
        'id_proof',
        'resume',
    ];

    public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

public function trainingPrograms()
{
    return $this->belongsToMany(TrainingProgram::class, 'enrollments');
}

}

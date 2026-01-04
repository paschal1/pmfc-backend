<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'training_program_id',
        'enrollment_date',
        'payment_method',
        'payment_reference',
        'payment_status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function trainingProgram()
    {
        return $this->belongsTo(TrainingProgram::class);
    }
}
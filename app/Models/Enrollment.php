<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
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
}

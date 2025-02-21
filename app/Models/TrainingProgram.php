<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'start_date',
        'end_date'

    ];

    public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

public function students()
{
    return $this->belongsToMany(Student::class, 'enrollments');
}

}

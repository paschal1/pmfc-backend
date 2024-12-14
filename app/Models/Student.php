<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory; // Ensure this is included

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];
}

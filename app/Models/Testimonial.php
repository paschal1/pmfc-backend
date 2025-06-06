<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'review',
        'is_approved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

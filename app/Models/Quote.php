<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'service_id',
        'quoted_price',
        'details',
    ];

    // Relationship with Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

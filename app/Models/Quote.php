<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    public function services()
{
    return $this->belongsToMany(Service::class, 'quote_service');
}

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quote extends Model
{
    use HasFactory;

  protected $fillable = [
    'email',
    'name',
    'phone',
    'message',
    'areasize',
    'location',
    'squarefeet',
    'budget',
    'service_ids',
    'service_titles',
    'service_prices',
    'details',
    'quote',
    'status',
];

protected $casts = [
    'details' => 'array',
    'quote' => 'array',
];

    // Relationship with Service
    public function services()
{
    return $this->belongsToMany(Service::class, 'quote_service');
}

}

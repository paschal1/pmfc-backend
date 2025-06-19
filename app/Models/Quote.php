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
        'squarefeet',
        'budget',
        'state_id',
        'address',
        'total_price',
        'service_ids',
        'service_titles',
        'service_prices',
        'details',
        'quote',
        'status',
    ];

    protected $casts = [
        'details' => 'array',
        'quote'   => 'array',
    ];

    /**
     * Relationship: State
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Optional: Polymorphic or real services relationship
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'quote_service'); // quote_service pivot optional
    }
}

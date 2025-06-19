<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationCost extends Model
{
    protected $fillable = ['state_id', 'cost'];

    /**
     * Get the state associated with the location cost.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the cost formatted as a currency string.
     *
     * @return string
     */
    public function getFormattedCostAttribute()
    {
        return '$' . number_format($this->cost, 2);
    }
}

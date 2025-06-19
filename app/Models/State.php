<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the name of the state.
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the location costs associated with the state.
     */
    public function locationCost()
    {
        return $this->hasOne(LocationCost::class);
    }
}


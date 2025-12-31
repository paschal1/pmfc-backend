<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get the name of the state (capitalize first letter).
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the location cost associated with the state.
     */
    public function locationCost()
    {
        return $this->hasOne(LocationCost::class);
    }

    /**
     * Get quotes for this state.
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
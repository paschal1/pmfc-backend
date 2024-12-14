<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory; // This enables factory functionality for the model.

    protected $fillable = [
        'name',
        'slug',
    ];

       // Define the relationship between Category and Product (one-to-many)
       public function products()
       {
           return $this->hasMany(Product::class);
       }
}

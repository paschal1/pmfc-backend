<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image1',
        'image2',
        'price',
        
    ];

    public function quotes()
{
    return $this->belongsToMany(Quote::class, 'quote_service');
}

}

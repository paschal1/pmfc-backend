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
        'type',
        'price',
        'min_price',
        'max_price',
        'image1',
        'image2',
    ];

    // Cast attributes to proper types
    protected $casts = [
        'price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
    ];

    /**
     * Service types
     */
    public const TYPES = [
        'Residential Design',
        'Hospitality Design',
        'Office Design',
        'Commercial Design',
    ];

    /**
     * Get formatted price display
     * Shows min-max range or single price if available
     */
    public function getPriceDisplay(): string
    {
        if ($this->min_price && $this->max_price) {
            return '₦' . number_format($this->min_price, 0) . ' - ₦' . number_format($this->max_price, 0);
        }

        if ($this->price) {
            return '₦' . number_format($this->price, 0);
        }

        return 'Price on request';
    }

    /**
     * Get price range as array
     */
    public function getPriceRange(): array
    {
        return [
            'min' => $this->min_price ?? $this->price,
            'max' => $this->max_price ?? $this->price,
            'single' => $this->price,
            'has_range' => !is_null($this->min_price) && !is_null($this->max_price),
        ];
    }

    /**
     * Get the type as a readable string
     */
    public function getTypeLabel(): string
    {
        return $this->type ?? 'Unknown Type';
    }

    /**
     * Check if service is a specific type
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by price range
     */
    public function scopeByPriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->where(function ($q) use ($minPrice, $maxPrice) {
            $q->whereBetween('min_price', [$minPrice, $maxPrice])
              ->orWhereBetween('max_price', [$minPrice, $maxPrice])
              ->orWhereBetween('price', [$minPrice, $maxPrice]);
        });
    }

    /**
     * Get all available types
     */
    public static function getAvailableTypes(): array
    {
        return self::TYPES;
    }

    /**
     * Relationship with Quote
     */
    public function quotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_service');
    }
}
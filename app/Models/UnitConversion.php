<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class UnitConversion extends Model
{
    /** @use HasFactory<\Database\Factories\UnitConversionFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'product_id', 'from_unit_id', 'to_unit_id', 'slug', 'conversion_factor'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['product_id', 'from_unit_id', 'to_unit_id'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}

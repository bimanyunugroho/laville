<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'variant_name',
        'default_unit_id',
        'purchase_price',
        'selling_price',
        'description',
        'is_active'
    ];

    protected function casts()
    {
        return [
            'is_active' => 'boolean'
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['code', 'name', 'variant_name'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function defaultUnit()
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }

    public function unitConversions()
    {
        return $this->hasMany(UnitConversion::class);
    }
}

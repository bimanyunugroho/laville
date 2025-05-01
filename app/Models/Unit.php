<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Unit extends Model
{
    /** @use HasFactory<\Database\Factories\UnitFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'code', 'slug', 'name', 'is_active'
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['code', 'name'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'default_unit_id');
    }

    public function fromUnitConversions()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function toUnitConversions()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    public function stockCardDetails()
    {
        return $this->hasMany(StockCardDetail::class);
    }
    
    public function currentStocks()
    {
        return $this->hasMany(CurrentStock::class);
    }
}

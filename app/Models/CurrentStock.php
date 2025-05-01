<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CurrentStock extends Model
{
    /** @use HasFactory<\Database\Factories\CurrentStockFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'product_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'slug',
        'month',
        'year',
        'status_running'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                $productCode = strtolower($model->product->code);
                $monthName = strtolower(\Carbon\Carbon::create()->month($model->month)->format('F'));
                $year = $model->year;
                return "current-stock-{$productCode}-{$monthName}-{$year}";
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }


}

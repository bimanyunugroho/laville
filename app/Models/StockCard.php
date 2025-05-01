<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class StockCard extends Model
{
    /** @use HasFactory<\Database\Factories\StockCardFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'product_id',
        'beginning_balance',
        'in_balance',
        'out_balance',
        'ending_balance',
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
                $monthName = strtolower(Carbon::create()->month($model->month)->format('F'));
                $year = $model->year;
                return "card-stock-{$productCode}-{$monthName}-{$year}";
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockCardDetails()
    {
        return $this->hasMany(StockCardDetail::class);
    }
}

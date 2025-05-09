<?php

namespace App\Models;

use App\Enums\StatusRunningCurrentStockEnum;
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
        'beginning_base_balance',
        'in_base_balance',
        'out_base_balance',
        'ending_base_balance',
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

    public function scopeActiveByProduct($query, $productId)
    {
        return $query->where('product_id', $productId)
            ->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
    }

    public function scopeActiveByStockCard($query, $month, $year)
    {
        return $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value)
            ->where('month', $month)
            ->where('year', $year);
    }
}

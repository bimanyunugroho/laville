<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class StockOut extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'stock_out_number',
        'slug',
        'supplier_id',
        'user_id',
        'out_date',
        'user_ack_id',
        'ack_date',
        'user_reject_id',
        'reject_date',
        'subtotal',
        'tax',
        'discount',
        'total_net',
        'status_notes',
        'status',
        'notes',
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['stock_out_number'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public static function generateStockOutNumber()
    {
        $today = now()->format('Ymd');
        $prefix = "INV/GDN/{$today}/";

        $lastOutNumber = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('stock_out_number', 'like', "{$prefix}%")
            ->orderBy('stock_out_number', 'desc')
            ->first();

        if ($lastOutNumber) {
            $lastNumber = (int)substr($lastOutNumber->stock_out_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $prefix . $newNumber;
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAck()
    {
        return $this->belongsTo(User::class);
    }

    public function userReject()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockOutDetail::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class StockOpname extends Model
{
    /** @use HasFactory<\Database\Factories\StockOpnameFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'so_number',
        'month',
        'year',
        'slug',
        'user_id',
        'so_date',
        'user_validator_id',
        'validator_date',
        'user_ack_id',
        'ack_date',
        'user_reject_id',
        'reject_date',
        'subtotal',
        'tax',
        'discount',
        'total_net',
        'status',
        'notes',
        'is_locked',
        'is_active'
    ];

    public function casts()
    {
        return [
            'is_locked' => 'boolean',
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
            ->generateSlugsFrom(['so_number'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public static function generateStockOpnameNumber()
    {
        $today = now()->format('Ymd');
        $prefix = "INV/SO/{$today}/";

        $lastOutSo = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('so_number', 'like', "{$prefix}%")
            ->orderBy('so_number', 'desc')
            ->first();

        if ($lastOutSo) {
            $lastNumber = (int)substr($lastOutSo->so_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $prefix . $newNumber;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userValidator()
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
        return $this->hasMany(StockOpnameDetail::class);
    }
}

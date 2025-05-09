<?php

namespace App\Models;

use App\Enums\StatusClosedPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ClosedPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\ClosedPeriodFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'no_closed',
        'slug',
        'month',
        'year',
        'user_id',
        'closed_date',
        'user_ack_id',
        'ack_date',
        'user_reject_id',
        'reject_date',
        'status_period',
        'status_confirm',
        'status'
    ];

    protected function casts()
    {
        return [
            'status' => 'boolean'
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['no_closed'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public static function generateClosedNumber()
    {
        $today = now()->format('Ymd');
        $prefix = "ACT/CLS/{$today}/";

        $lastClosed = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('no_closed', 'like', "{$prefix}%")
            ->orderBy('no_closed', 'desc')
            ->first();

        if ($lastClosed) {
            $lastNumber = (int)substr($lastClosed->no_closed, -5);
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

    public function userAck()
    {
        return $this->belongsTo(User::class);
    }

    public function userReject()
    {
        return $this->belongsTo(User::class);
    }

    public function stockCardDetails()
    {
        return $this->morphMany(StockCardDetail::class, 'reference');
    }

    public function scopePeriodIsActive($query)
    {
        return $query->where('status_period', '=', StatusClosedPeriod::RUNNING->value);
    }
}

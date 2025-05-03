<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class GoodReceipt extends Model
{
    /** @use HasFactory<\Database\Factories\GoodReceiptFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'receipt_number',
        'slug',
        'purchase_order_id',
        'supplier_id',
        'user_id',
        'receipt_date',
        'user_ack_id',
        'ack_date',
        'user_reject_id',
        'reject_date',
        'subtotal',
        'tax',
        'discount',
        'total_net',
        'status_receipt',
        'notes',
        'is_active'
    ];

    public function casts()
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
            ->generateSlugsFrom(['receipt_number', 'supplier_id'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public static function generateReceiptNumber()
    {
        $today = now()->format('Ymd');
        $prefix = "INV/GRM/{$today}/";

        $lastReceipt = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int)substr($lastReceipt->receipt_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $prefix . $newNumber;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
        return $this->hasMany(GoodReceiptDetail::class);
    }


}

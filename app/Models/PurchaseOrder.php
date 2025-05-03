<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PurchaseOrder extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseOrderFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'po_number',
        'slug',
        'supplier_id',
        'user_id',
        'po_date',
        'expected_date',
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
            ->generateSlugsFrom(['po_number', 'supplier_id'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public static function generatePoNumber()
    {
        $today = now()->format('Ymd');
        $prefix = "INV/PO/{$today}/";

        $lastPo = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPo) {
            $lastNumber = (int)substr($lastPo->po_number, -5);
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
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function goodReceipts()
    {
        return $this->hasMany(GoodReceipt::class);
    }
}

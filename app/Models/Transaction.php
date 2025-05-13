<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'invoice_number',
        'slug',
        'customer_id',
        'transaction_date',
        'user_id',
        'total',
        'discount',
        'subtotal',
        'tax',
        'total_amount',
        'paid_amount',
        'change_amount',
        'status',
        'source_transaction',
        'notes',
        'is_active'
    ];

    protected function casts()
    {
        return [
            'is_active' => 'boolean'
        ];
    }

    public static function generateTransactionInvoice()
    {
        $today = now()->format('Ymd');
        $prefix = "TRX/{$today}/";

        $lastInvoice = self::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int)substr($lastInvoice->invoice_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $prefix . $newNumber;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['invoice_number'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault([
            'name' => 'Guest Customer',
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }
}

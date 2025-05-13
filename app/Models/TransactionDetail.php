<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionDetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'price',
        'discount',
        'subtotal'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockCardDetails()
    {
        return $this->morphMany(StockCardDetail::class, 'reference');
    }
}

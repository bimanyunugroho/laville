<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodReceiptDetail extends Model
{
    /** @use HasFactory<\Database\Factories\GoodReceiptDetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'good_receipt_id',
        'purchase_order_detail_id',
        'product_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'price',
        'subtotal',
        'received_quantity',
        'received_base_quantity'
    ];

    public function goodRecipt()
    {
        return $this->belongsTo(GoodReceipt::class);
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

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class);
    }
}

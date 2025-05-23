<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOutDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_out_id',
        'product_id',
        'unit_id',
        'quantity',
        'base_quantity',
        'price',
        'subtotal',
        'received_quantity',
        'received_base_quantity',
        'notes_detail'
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
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

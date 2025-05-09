<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameDetail extends Model
{
    /** @use HasFactory<\Database\Factories\StockOpnameDetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'unit_id',
        'system_stock',
        'system_stock_base',
        'physical_stock',
        'physical_stock_base',
        'difference_stock',
        'difference_stock_base',
        'price',
        'subtotal',
        'status',
        'notes'
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
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

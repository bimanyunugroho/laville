<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCardDetail extends Model
{
    /** @use HasFactory<\Database\Factories\StockCardDetailFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_card_id',
        'reference_id',
        'reference_type',
        'reference_status',
        'unit_id',
        'transaction_date',
        'movement_type',
        'quantity',
        'base_quantity',
        'balance_quantity',
        'balance_base_quantity',
        'notes'
    ];

    public function stockCard()
    {
        return $this->belongsTo(StockCard::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}

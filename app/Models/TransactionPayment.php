<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionPayment extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionPaymentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'payment_date',
        'payment_method',
        'payment_reference',
        'amount',
        'status'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

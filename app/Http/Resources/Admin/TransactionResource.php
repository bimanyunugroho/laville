<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\TransactionDetailCollection;
use App\Http\Resources\TransactionPaymentCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
        static::withoutWrapping();
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'invoice_number' => $this->invoice_number,
            'slug'  => $this->slug,
            'customer_id' => $this->customer_id,
            'transaction_date' => $this->transaction_date,
            'user_id' => $this->user_id,
            'total' => $this->total,
            'discount' => $this->discount,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'change_amount' => $this->change_amount,
            'status' => $this->status,
            'source_transaction' => $this->source_transaction,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'customer'  => new CustomerResource($this->whenLoaded('customer')),
            'user'  => new UserResource($this->whenLoaded('user')),
            'details'   => new TransactionDetailCollection($this->whenLoaded('details')),
            'payments'  => new TransactionPaymentCollection($this->whenLoaded('payments'))
        ];
    }
}

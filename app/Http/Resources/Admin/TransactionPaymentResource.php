<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionPaymentResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'payment_date'  => $this->payment_date,
            'payment_method'    => $this->payment_method,
            'payment_reference'    => $this->payment_reference,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'transaction'   => new TransactionResource($this->whenLoaded('transaction'))
        ];
    }
}

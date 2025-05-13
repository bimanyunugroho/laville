<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
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
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'subtotal' => $this->subtotal,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'transaction'   => new TransactionResource($this->whenLoaded('transaction')),
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'  => new UnitResource($this->whenLoaded('unit'))
        ];
    }
}

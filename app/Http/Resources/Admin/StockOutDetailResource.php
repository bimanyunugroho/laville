<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOutDetailResource extends JsonResource
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
            'stock_out_id' => $this->stock_out_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'  => new UnitResource($this->whenLoaded('unit')),
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'received_quantity' => $this->received_quantity,
            'received_base_quantity' => $this->received_base_quantity,
            'notes_detail' => $this->notes_detail,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s')
        ];
    }
}

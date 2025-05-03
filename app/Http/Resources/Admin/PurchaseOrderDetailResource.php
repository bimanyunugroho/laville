<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderDetailResource extends JsonResource
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
            'purchase_order_id' => $this->purchase_order_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'purchaseOrder' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'  => new UnitResource($this->whenLoaded('unit')),
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'received_quantity' => $this->received_quantity,
            'received_base_quantity' => $this->received_base_quantity,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s')
        ];
    }
}

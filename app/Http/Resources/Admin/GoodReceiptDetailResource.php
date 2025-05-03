<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\PurchaseOrderDetailCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodReceiptDetailResource extends JsonResource
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
            'good_receipt_id' => $this->good_receipt_id,
            'purchase_order_detail_id' => $this->purchase_order_detail_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'received_quantity' => $this->received_quantity,
            'received_base_quantity' => $this->received_base_quantity,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s'),
            'goodReceipt'   => new GoodReceiptResource($this->whenLoaded('goodReceipt')),
            'purchaseOrderDetail' => new PurchaseOrderDetailResource($this->whenLoaded('purchaseOrderDetail')),
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'  => new UnitResource($this->whenLoaded('unit'))
        ];
    }
}

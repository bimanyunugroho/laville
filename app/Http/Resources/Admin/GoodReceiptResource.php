<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\GoodReceiptCollection;
use App\Http\Resources\GoodReceiptDetailCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodReceiptResource extends JsonResource
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
            'receipt_number' => $this->receipt_number,
            'slug' => $this->slug,
            'purchase_order_id' => $this->purchase_order_id,
            'supplier_id' => $this->supplier_id,
            'user_id' => $this->user_id,
            'receipt_date' => $this->receipt_date,
            'user_ack_id' => $this->user_ack_id,
            'ack_date' => $this->ack_date,
            'user_reject_id' => $this->user_reject_date,
            'reject_date' => $this->reject_date,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total_net' => $this->total_net,
            'status_receipt' => $this->status_receipt,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s'),
            'purchaseOrder' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'user' => new UserResource($this->whenLoaded('user')),
            'userAck' => new UserResource($this->whenLoaded('userAck')),
            'userReject' => new UserResource($this->whenLoaded('userReject')),
            'details' => new GoodReceiptDetailCollection($this->whenLoaded('details'))
        ];
    }
}

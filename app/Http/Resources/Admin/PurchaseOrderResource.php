<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\PurchaseOrderCollection;
use App\Http\Resources\PurchaseOrderDetailCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'po_number' => $this->po_number,
            'slug' => $this->slug,
            'supplier_id' => $this->supplier_id,
            'user_id' => $this->user_id,
            'user_ack_id' => $this->user_ack_id,
            'supplier'  => new SupplierResource($this->whenLoaded('supplier')),
            'user'  => new UserResource($this->whenLoaded('user')),
            'userAck'   => new UserResource($this->whenLoaded('userAck')),
            'userReject'   => new UserResource($this->whenLoaded('userReject')),
            'details'   => new PurchaseOrderDetailCollection($this->whenLoaded('details')),
            'po_date' => $this->po_date,
            'expected_date' => $this->expected_date,
            'ack_date' => $this->ack_date,
            'reject_date' => $this->reject_date,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total_net' => $this->total_net,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y h:i:s')
        ];
    }
}

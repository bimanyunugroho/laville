<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\StockOutDetailCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOutResource extends JsonResource
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
            'stock_out_number' => $this->stock_out_number,
            'slug' => $this->slug,
            'supplier_id' => $this->supplier_id,
            'user_id' => $this->user_id,
            'user_ack_id' => $this->user_ack_id,
            'user_reject_id'    => $this->user_reject_id,
            'supplier'  => new SupplierResource($this->whenLoaded('supplier')),
            'user'  => new UserResource($this->whenLoaded('user')),
            'userAck'   => new UserResource($this->whenLoaded('userAck')),
            'userReject'   => new UserResource($this->whenLoaded('userReject')),
            'details'   => new StockOutDetailCollection($this->whenLoaded('details')),
            'out_date' => $this->out_date,
            'ack_date' => $this->ack_date,
            'reject_date' => $this->reject_date,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total_net' => $this->total_net,
            'status_notes' => $this->status_notes,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y h:i:s')
        ];
    }
}

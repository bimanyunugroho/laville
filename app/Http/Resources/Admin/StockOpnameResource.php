<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\StockOpnameDetailCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameResource extends JsonResource
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
            'so_number' => $this->so_number,
            'month' => $this->month,
            'year'  => $this->year,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'so_date' => $this->so_date,
            'user_validator_id' => $this->user_validator_id,
            'validator_date' => $this->validator_date,
            'user_ack_id' => $this->user_ack_id,
            'ack_date' => $this->ack_date,
            'user_reject_id' => $this->user_reject_id,
            'reject_date' => $this->reject_date,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total_net' => $this->total_net,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_locked' => $this->is_locked,
            'is_active' => $this->is_active,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s'),
            'user'  => new UserResource($this->whenLoaded('user')),
            'userValidator'  => new UserResource($this->whenLoaded('userValidator')),
            'userAck'  => new UserResource($this->whenLoaded('userAck')),
            'userReject'  => new UserResource($this->whenLoaded('userReject')),
            'details'   => new StockOpnameDetailCollection($this->whenLoaded('details'))
        ];
    }
}

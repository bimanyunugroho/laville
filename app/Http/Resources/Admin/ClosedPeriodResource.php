<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClosedPeriodResource extends JsonResource
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
            'no_closed' => $this->no_closed,
            'slug'  => $this->slug,
            'month' => $this->month,
            'year'  => $this->year,
            'user_id'   => $this->user_id,
            'closed_date'   => $this->closed_date,
            'user_ack_id'   => $this->user_ack_id,
            'ack_date'  => $this->ack_date,
            'user_reject_id'    => $this->user_reject_id,
            'reject_date'   => $this->reject_date,
            'status_period'    => $this->status_period,
            'status_confirm'    => $this->status_confirm,
            'status'    => $this->status,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
            'userAck' => new UserResource($this->whenLoaded('userAck')),
            'userReject' => new UserResource($this->whenLoaded('userReject'))
        ];
    }
}

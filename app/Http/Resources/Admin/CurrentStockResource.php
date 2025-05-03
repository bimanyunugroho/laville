<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentStockResource extends JsonResource
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
            'id'        => $this->id,
            'product_id' => $this->product_id,
            'unit_id'   => $this->unit_id,
            'quantity'  => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'slug'      => $this->slug,
            'month'     => $this->month,
            'year'      => $this->year,
            'status_running' => $this->status_running,
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'      => new UnitResource($this->whenLoaded('unit')),
            'created_at'    => Carbon::parse($this->created_at)->format('Y-m-d H:i:s')
        ];
    }
}

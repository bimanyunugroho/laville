<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitConversionResource extends JsonResource
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
            'slug'      => $this->slug,
            'product_id'    => $this->product_id,
            'from_unit_id'  => $this->from_unit_id,
            'to_unit_id'  => $this->to_unit_id,
            'product'   => new ProductResource($this->whenLoaded('product')),
            'fromUnit'  => new UnitResource($this->whenLoaded('fromUnit')),
            'toUnit'    => new UnitResource($this->whenLoaded('toUnit')),
            'conversion_factor' => $this->conversion_factor,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updated_at'    => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}

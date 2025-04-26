<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'variant_name' => $this->variant_name,
            'defaultUnit'   => new UnitResource($this->whenLoaded('defaultUnit')),
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'description' => $this->description,
            'is_active' => $this->is_active
        ];
    }
}

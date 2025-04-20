<?php

namespace App\Http\Resources\Admin;

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
            'product_id'    => $this->product_id,
            'from_unit_id'  => $this->form_unit_id,
            'to_unit_id'    => $this->to_unit_id,
            'slug'      => $this->slug,
            'conversion_factor' => $this->conversion_factor
        ];
    }
}

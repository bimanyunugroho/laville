<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameDetailResource extends JsonResource
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
            'stock_opname_id' => $this->stock_opname_id,
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            'system_stock' => $this->system_stock,
            'system_stock_base' => $this->system_stock_base,
            'physical_stock' => $this->physical_stock,
            'physical_stock_base' => $this->physical_stock_base,
            'difference_stock' => $this->difference_stock,
            'difference_stock_base' => $this->difference_stock_base,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-Y h:i:s'),
            'stockOpname' => new StockOpnameResource($this->whenLoaded('stockOpname')),
            'product'   => new ProductResource($this->whenLoaded('product')),
            'unit'  => new UnitResource($this->whenLoaded('unit')),
        ];
    }
}

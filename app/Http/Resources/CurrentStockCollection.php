<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\CurrentStockResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CurrentStockCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($currentStock) {
            return new CurrentStockResource($currentStock);
        })->all();
    }
}

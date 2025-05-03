<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\PurchaseOrderDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseOrderDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($details) {
            return new PurchaseOrderDetailResource($details);
        })->all();
    }
}

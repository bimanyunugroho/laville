<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\PurchaseOrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($purchaseOrders) {
            return new PurchaseOrderResource($purchaseOrders);
        })->all();
    }
}

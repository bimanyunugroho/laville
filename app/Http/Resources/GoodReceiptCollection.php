<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\GoodReceiptResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GoodReceiptCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($goodReceipt) {
            return new GoodReceiptResource($goodReceipt);
        })->all();
    }
}

<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\GoodReceiptDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GoodReceiptDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($goodReceiptDetail) {
            return new GoodReceiptDetailResource($goodReceiptDetail);
        })->all();
    }
}

<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\StockOutDetailResource;
use App\Http\Resources\Admin\StockOutResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StockOutDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($stockOutDetail) {
            return new StockOutDetailResource($stockOutDetail);
        })->all();
    }
}

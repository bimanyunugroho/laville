<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\UnitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UnitCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($unit) {
            return new UnitResource($unit);
        })->all();
    }
}

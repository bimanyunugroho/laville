<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\UnitConversionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UnitConversionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection(function ($unitConversion) {
            return new UnitConversionResource($unitConversion);
        })->all();
    }
}

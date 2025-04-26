<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'code'  => $this->code,
            'slug'  => $this->slug,
            'name'  => $this->name,
            'is_active' => $this->is_active,
            'created_at'    => Carbon::parse($this->created_at)->format('d-m-y h:i:s')
        ];
    }
}

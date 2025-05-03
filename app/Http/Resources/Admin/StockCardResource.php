<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\StockCardDetailCollection;
use App\Http\Resources\UnitConversionCollection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockCardResource extends JsonResource
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
            'id'       => $this->id,
            'product_id'    => $this->product_id,
            'beginning_balance' => $this->beginning_balance,
            'in_balance'    => $this->in_balance,
            'out_balance'   => $this->out_balance,
            'ending_balance' => $this->ending_balance,
            'beginning_base_balance' => $this->beginning_base_balance,
            'in_base_balance'    => $this->in_base_balance,
            'out_base_balance'   => $this->out_base_balance,
            'ending_base_balance' => $this->ending_base_balance,
            'slug'          => $this->slug,
            'month'         => $this->month,
            'year'          => $this->year,
            'status_running' => $this->status_running,
            'product'       => new ProductResource($this->whenLoaded('product')),
            'stockCardDetails' => new StockCardDetailCollection($this->whenLoaded('stockCardDetails')),
            'unitConversions'   => new UnitConversionCollection($this->whenLoaded('unitConversions')),
            'created_at'    => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}

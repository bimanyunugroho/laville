<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\StockCardDetailCollection;
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
            'slug'          => $this->slug,
            'month'         => $this->month,
            'year'          => $this->year,
            'status_running' => $this->status_running,
            'product'       => new ProductResource($this->whenLoaded('product')),
            'stockCardDetails' => new StockCardDetailCollection($this->whenLoaded('stockCardDetails')),
            'created_at'    => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}

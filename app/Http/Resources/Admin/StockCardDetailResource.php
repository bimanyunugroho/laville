<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockCardDetailResource extends JsonResource
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
            'id' => $this->id,
            'stock_card_id' => $this->stock_card_id,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'reference_status' => $this->reference_status,
            'unit_id' => $this->unit_id,
            'transaction_date' => $this->transaction_date,
            'movement_type' => $this->movement_type,
            'quantity' => $this->quantity,
            'base_quantity' => $this->base_quantity,
            'balance_quantity' => $this->balance_quantity,
            'balance_base_quantity' => $this->balance_base_quantity,
            'notes' => $this->notes,
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}

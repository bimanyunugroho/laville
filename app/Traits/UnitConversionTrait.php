<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;

trait UnitConversionTrait
{
    /**
     * Convert quantity from one unit to another
     *
     * @param Product $product
     * @param Unit $fromUnit
     * @param Unit $toUnit
     * @param float $quantity
     * @return float
     */
    public function convertUnit(Product $product, Unit $fromUnit, Unit $toUnit, float $quantity): float
    {
        // If same unit, no conversion needed
        if ($fromUnit->id === $toUnit->id) {
            return $quantity;
        }

        // Try direct conversion
        $conversion = UnitConversion::where('product_id', $product->id)
            ->where('from_unit_id', $fromUnit->id)
            ->where('to_unit_id', $toUnit->id)
            ->first();

        if ($conversion) {
            return $quantity * $conversion->conversion_factor;
        }

        // Try reverse conversion
        $reverseConversion = UnitConversion::where('product_id', $product->id)
            ->where('from_unit_id', $toUnit->id)
            ->where('to_unit_id', $fromUnit->id)
            ->first();

        if ($reverseConversion) {
            return $quantity / $reverseConversion->conversion_factor;
        }

        // Try conversion through base unit
        $defaultUnit = $product->defaultUnit;

        $toBaseConversion = UnitConversion::where('product_id', $product->id)
            ->where('from_unit_id', $fromUnit->id)
            ->where('to_unit_id', $defaultUnit->id)
            ->first();

        $fromBaseConversion = UnitConversion::where('product_id', $product->id)
            ->where('from_unit_id', $defaultUnit->id)
            ->where('to_unit_id', $toUnit->id)
            ->first();

        if ($toBaseConversion && $fromBaseConversion) {
            $baseQuantity = $quantity * $toBaseConversion->conversion_factor;
            return $baseQuantity * $fromBaseConversion->conversion_factor;
        }

        // If no conversion found, throw exception
        throw new \Exception("No conversion found for product {$product->name} from {$fromUnit->name} to {$toUnit->name}");
    }

    /**
     * Convert quantity to base unit
     *
     * @param Product $product
     * @param Unit $unit
     * @param float $quantity
     * @return float
     */
    public function convertToBaseUnit(Product $product, Unit $unit, float $quantity): float
    {
        $defaultUnit = $product->defaultUnit;

        return $this->convertUnit($product, $unit, $defaultUnit, $quantity);
    }

    /**
     * Convert quantity from base unit to specified unit
     *
     * @param Product $product
     * @param Unit $unit
     * @param float $baseQuantity
     * @return float
     */
    public function convertFromBaseUnit(Product $product, Unit $unit, float $baseQuantity): float
    {
        $defaultUnit = $product->defaultUnit;

        return $this->convertUnit($product, $defaultUnit, $unit, $baseQuantity);
    }
}

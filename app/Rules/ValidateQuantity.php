<?php

namespace App\Rules;

use Closure;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates product quantity based on unit type
 * - Piece units: must be whole number (integer)
 * - KG, MG, Gram, Litre, Dozen: can be decimal
 * - Min: 0.01
 * - Max: 999999.99
 * 
 * Usage: new ValidateQuantity(unit: 'Piece')
 */
class ValidateQuantity implements ValidationRule
{
    public function __construct(
        private string $unit,
        private float $min = 0.01,
        private float $max = 999999.99
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if numeric
        if (!is_numeric($value)) {
            $fail('Quantity must be a valid number.');
            return;
        }

        $quantity = (float) $value;

        // Check minimum
        if ($quantity < $this->min) {
            $fail("Quantity must be at least " . $this->min . ".");
            return;
        }

        // Check maximum
        if ($quantity > $this->max) {
            $fail("Quantity cannot exceed " . number_format($this->max, 2) . ".");
            return;
        }

        // Unit-specific validation
        // Piece units MUST be whole numbers only
        if ($this->unit === Product::UNIT_PIECE) {
            if ($quantity != (int)$quantity) {
                $fail("Quantity for Piece unit must be a whole number (no decimals).");
                return;
            }
        }

        // KG, MG, Gram, Litre, Dozen: decimals are OK (already validated above)
    }
}

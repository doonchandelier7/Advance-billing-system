<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Stock IN (purchase): increase product stock and record movement.
     */
    public function stockIn(Product $product, float $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null, ?int $userId = null): StockMovement
    {
        return $this->recordMovement($product, StockMovement::TYPE_IN, abs($quantity), $referenceType, $referenceId, $notes, $userId);
    }

    /**
     * Stock OUT (sales): decrease product stock and record movement.
     */
    public function stockOut(Product $product, float $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null, ?int $userId = null): StockMovement
    {
        $qty = -1 * abs($quantity);
        $product->refresh();
        if ($product->stock + $qty < 0) {
            throw new \RuntimeException("Insufficient stock for product {$product->name}. Available: {$product->stock}, requested: ".abs($qty));
        }
        return $this->recordMovement($product, StockMovement::TYPE_OUT, $qty, $referenceType, $referenceId, $notes, $userId);
    }

    /**
     * Manual stock adjustment: set product stock to new value and record adjustment.
     */
    public function adjust(Product $product, float $newStock, ?string $notes = null, ?int $userId = null): StockMovement
    {
        $newStock = max(0, (float) $newStock);
        $current = (float) $product->stock;
        $quantity = $newStock - $current;
        if ($quantity == 0) {
            throw new \RuntimeException('No change in stock.');
        }
        $type = $quantity > 0 ? StockMovement::TYPE_ADJUSTMENT : StockMovement::TYPE_ADJUSTMENT;
        return $this->recordMovement($product, $type, $quantity, 'adjustment', null, $notes ?? 'Manual adjustment', $userId);
    }

    protected function recordMovement(Product $product, string $type, float $quantity, ?string $referenceType, ?int $referenceId, ?string $notes, ?int $userId): StockMovement
    {
        return DB::transaction(function () use ($product, $type, $quantity, $referenceType, $referenceId, $notes, $userId) {
            $product->refresh();
            $stockBefore = (float) $product->stock;
            $stockAfter = $stockBefore + $quantity;
            if ($stockAfter < 0) {
                throw new \RuntimeException("Insufficient stock for product {$product->name}. Available: {$stockBefore}, requested: ".abs($quantity));
            }
            $product->update(['stock' => $stockAfter]);
            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $userId ?? auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }
}

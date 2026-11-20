<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class InventoryService
{
    public function reserveStock(int $productId, ?int $variantId, int $quantity, string $reason, ?string $reference = null, $date = null): void
    {
        if ($quantity <= 0) return;
        $movementDate = $date ? Carbon::parse($date) : now();

        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            if ($variant->stock < $quantity) {
                throw new \Exception("Stok varian tidak mencukupi (tersedia {$variant->stock}, diminta {$quantity})");
            }
            $before = $variant->stock;
            $variant->decrement('stock', $quantity);
            $variant->refresh();
            StockMovement::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $variant->stock,
                'reason' => $reason,
                'reference' => $reference,
                'movement_date' => $movementDate,
            ]);
        } else {
            $product = Product::findOrFail($productId);
            if ($product->stock < $quantity) {
                throw new \Exception("Stok barang tidak mencukupi (tersedia {$product->stock}, diminta {$quantity})");
            }
            $before = $product->stock;
            $product->decrement('stock', $quantity);
            $product->refresh();
            StockMovement::create([
                'product_id' => $productId,
                'variant_id' => null,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $product->stock,
                'reason' => $reason,
                'reference' => $reference,
                'movement_date' => $movementDate,
            ]);
        }
    }

    public function releaseStock(int $productId, ?int $variantId, int $quantity, string $reason, ?string $reference = null, $date = null): void
    {
        if ($quantity <= 0) return;
        $movementDate = $date ? Carbon::parse($date) : now();

        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $before = $variant->stock;
            $variant->increment('stock', $quantity);
            $variant->refresh();
            StockMovement::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'type' => 'return',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $variant->stock,
                'reason' => $reason,
                'reference' => $reference,
                'movement_date' => $movementDate,
            ]);
        } else {
            $product = Product::findOrFail($productId);
            $before = $product->stock;
            $product->increment('stock', $quantity);
            $product->refresh();
            StockMovement::create([
                'product_id' => $productId,
                'variant_id' => null,
                'type' => 'return',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $product->stock,
                'reason' => $reason,
                'reference' => $reference,
                'movement_date' => $movementDate,
            ]);
        }
    }

    public function adjustStock(int $productId, ?int $variantId, string $type, int $quantity, string $reason, ?string $reference = null, $date = null): void
    {
        if ($quantity <= 0) throw new \Exception("Jumlah harus lebih dari 0");
        $movementDate = $date ? Carbon::parse($date) : now();

        $target = $variantId
            ? ProductVariant::findOrFail($variantId)
            : Product::findOrFail($productId);

        $before = $target->stock;
        $delta = $type === 'out' ? -$quantity : $quantity;
        $after = $before + $delta;
        if ($after < 0) throw new \Exception("Stok tidak boleh negatif");

        $target->update(['stock' => $after]);
        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'reason' => $reason,
            'reference' => $reference,
            'movement_date' => $movementDate,
        ]);
    }
}
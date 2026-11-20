<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SaleService
{
    public function __construct(private InventoryService $inventory) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $settings = AppSetting::find(1);
            $prefix = $settings?->trx_prefix ?? 'TRX';

            $items = $data['items'];
            if (empty($items)) throw new \Exception("Minimal satu barang harus dimasukkan");

            $orderDate = isset($data['order_date']) && $data['order_date']
                ? Carbon::parse($data['order_date'])
                : now();

            $resolved = array_map(function ($item) {
                $product = Product::with('variants')->findOrFail($item['product_id']);
                if ($product->status !== 'active') throw new \Exception("Barang {$product->name} tidak aktif");

                $costPrice = $product->cost_price;
                $sellPrice = $product->sell_price;
                $variantInfo = null;
                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

                if ($product->has_variants) {
                    if (!$variantId) throw new \Exception("Barang {$product->name} memiliki varian, pilih varian");
                    $v = $product->variants->firstWhere('id', $variantId);
                    if (!$v) throw new \Exception("Varian tidak ditemukan");
                    $costPrice = $v->cost_price;
                    $sellPrice = $v->sell_price;
                    $variantInfo = collect([$v->color, $v->size])->filter()->implode(' / ');
                }

                return [
                    'product' => $product,
                    'variant_id' => $variantId,
                    'cost_price' => $costPrice,
                    'sell_price' => $sellPrice,
                    'variant_info' => $variantInfo,
                    'quantity' => (int)$item['quantity'],
                    'subtotal' => $sellPrice * (int)$item['quantity'],
                    'cost_subtotal' => $costPrice * (int)$item['quantity'],
                ];
            }, $items);

            $subtotal = array_sum(array_column($resolved, 'subtotal'));
            $costTotal = array_sum(array_column($resolved, 'cost_subtotal'));
            $discount = $data['discount'] ?? 0;
            $additionalFee = $data['additional_fee'] ?? 0;
            $total = $subtotal - $discount + $additionalFee;
            if ($total < 0) throw new \Exception("Total tidak boleh negatif");

            $isCashier = ($data['channel'] ?? '') === 'kasir';
            $reserveImmediately = $data['reserve_immediately'] ?? $isCashier;
            $orderStatus = $data['order_status'] ?? ($isCashier ? 'completed' : 'pending');
            $paymentStatus = $data['payment_status'] ?? ($isCashier ? 'paid' : 'unpaid');

            $invoiceNumber = $this->generateInvoiceNumber($prefix, $orderDate);

            if ($reserveImmediately) {
                foreach ($resolved as $r) {
                    $avail = $r['variant_id']
                        ? $r['product']->variants->firstWhere('id', $r['variant_id'])->stock
                        : $r['product']->stock;
                    if ($avail < $r['quantity']) {
                        throw new \Exception("Stok {$r['product']->name} tidak mencukupi");
                    }
                }
            }

            $amountPaid = $data['amount_paid'] ?? null;

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'external_order' => $data['external_order'] ?? null,
                'channel' => $data['channel'],
                'customer_name' => $data['customer_name'] ?? null,
                'order_date' => $orderDate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'additional_fee' => $additionalFee,
                'total' => $total,
                'cost_total' => $costTotal,
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'payment_method' => $data['payment_method'] ?? null,
                'amount_paid' => $amountPaid,
                'change' => ($isCashier && $amountPaid !== null)
                    ? max(0, $amountPaid - $total)
                    : null,
                'note' => $data['note'] ?? null,
                'stock_reserved' => $reserveImmediately,
            ]);

            foreach ($resolved as $r) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $r['product']->id,
                    'variant_id' => $r['variant_id'],
                    'product_name' => $r['product']->name,
                    'variant_info' => $r['variant_info'],
                    'quantity' => $r['quantity'],
                    'cost_price' => $r['cost_price'],
                    'sell_price' => $r['sell_price'],
                    'subtotal' => $r['subtotal'],
                ]);
            }

            if ($reserveImmediately) {
                foreach ($resolved as $r) {
                    $reason = $isCashier ? 'Penjualan melalui kasir' : "Penjualan online ({$data['channel']})";
                    $this->inventory->reserveStock(
                        $r['product']->id,
                        $r['variant_id'],
                        $r['quantity'],
                        $reason,
                        $invoiceNumber,
                        $orderDate
                    );
                }
            }

            return $sale->load('items');
        });
    }

    public function updateStatus(Sale $sale, string $nextOrderStatus, ?string $nextPaymentStatus = null, ?string $note = null): Sale
    {
        return DB::transaction(function () use ($sale, $nextOrderStatus, $nextPaymentStatus, $note) {
            $sale->load('items');
            $cancelled = in_array($nextOrderStatus, ['cancelled', 'returned']);
            $wasCancelled = in_array($sale->order_status, ['cancelled', 'returned']);

            $activeStatuses = ['processing', 'shipped', 'completed'];
            $needsReserve = in_array($nextOrderStatus, $activeStatuses) && !$sale->stock_reserved && !$wasCancelled;
            $needsRelease = $cancelled && $sale->stock_reserved;

            if ($needsRelease) {
                foreach ($sale->items as $item) {
                    $this->inventory->releaseStock(
                        $item->product_id,
                        $item->variant_id,
                        $item->quantity,
                        $nextOrderStatus === 'cancelled' ? 'Pembatalan pesanan' : 'Pengembalian barang (retur)',
                        $sale->invoice_number
                    );
                }
            }

            if ($needsReserve) {
                foreach ($sale->items as $item) {
                    $this->inventory->reserveStock(
                        $item->product_id,
                        $item->variant_id,
                        $item->quantity,
                        'Konfirmasi pesanan',
                        $sale->invoice_number
                    );
                }
            }

            $sale->update([
                'order_status' => $nextOrderStatus,
                'payment_status' => $nextPaymentStatus ?? $sale->payment_status,
                'stock_reserved' => $needsRelease ? false : ($needsReserve ? true : $sale->stock_reserved),
                'note' => $note ?? $sale->note,
            ]);

            return $sale->fresh('items');
        });
    }

    public function partialReturn(Sale $sale, int $itemId, int $qty): Sale
    {
        return DB::transaction(function () use ($sale, $itemId, $qty) {
            $sale->load('items');
            if (!$sale->stock_reserved) throw new \Exception("Stok belum dikurangi, tidak bisa diretur");
            $item = $sale->items->firstWhere('id', $itemId);
            if (!$item) throw new \Exception("Item tidak ditemukan");
            if ($qty <= 0 || $qty > $item->quantity) throw new \Exception("Jumlah retur tidak valid");

            $this->inventory->releaseStock(
                $item->product_id,
                $item->variant_id,
                $qty,
                'Pengembalian sebagian (retur)',
                $sale->invoice_number
            );

            $newQty = $item->quantity - $qty;
            $newSubtotal = $item->sell_price * $newQty;
            $item->update(['quantity' => $newQty, 'subtotal' => $newSubtotal]);

            $items = $sale->items()->get();
            $subtotal = $items->sum('subtotal');
            $costTotal = $items->reduce(fn ($c, $i) => $c + ($i->cost_price * $i->quantity), 0);
            $total = $subtotal - $sale->discount + $sale->additional_fee;
            $sale->update(['subtotal' => $subtotal, 'cost_total' => $costTotal, 'total' => $total]);

            return $sale->fresh('items');
        });
    }

    private function generateInvoiceNumber(string $prefix, $date): string
    {
        $d = Carbon::parse($date);
        $datePart = $d->format('Ymd');
        $count = Sale::where('invoice_number', 'like', "{$prefix}-{$datePart}-%")->count();
        return "{$prefix}-{$datePart}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
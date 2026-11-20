<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        $products = Product::with('variants')->where('status', 'active')->orderBy('name')->get();
        return view('cashier.index', compact('products'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:cash,transfer,qris',
            'amount_paid' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'note' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $costTotal = 0;
            $resolved = [];

            foreach ($data['items'] as $item) {
                $p = Product::with('variants')->find($item['product_id']);
                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
                $costPrice = $p->cost_price;
                $sellPrice = $p->sell_price;
                $variantInfo = null;

                if ($variantId) {
                    $v = $p->variants->firstWhere('id', $variantId);
                    if (!$v) throw new \Exception("Varian tidak ditemukan");
                    $costPrice = $v->cost_price;
                    $sellPrice = $v->sell_price;
                    $variantInfo = collect([$v->color, $v->size])->filter()->implode(' / ');

                    if ($v->stock < $item['quantity']) {
                        throw new \Exception("Stok {$p->name} ({$variantInfo}) tidak mencukupi");
                    }
                } else {
                    if ($p->stock < $item['quantity']) {
                        throw new \Exception("Stok {$p->name} tidak mencukupi");
                    }
                }

                $qty = (int)$item['quantity'];
                $itemSubtotal = $sellPrice * $qty;
                $subtotal += $itemSubtotal;
                $costTotal += $costPrice * $qty;

                $resolved[] = [
                    'product' => $p,
                    'variant_id' => $variantId,
                    'variant_info' => $variantInfo,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'sell_price' => $sellPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $total = $subtotal - $discount;
            $now = now();
            $datePart = $now->format('Ymd');
            $count = Sale::where('invoice_number', 'like', "TRX-{$datePart}-%")->count();
            $invoiceNumber = "TRX-{$datePart}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $amountPaid = $data['payment_method'] === 'cash' ? $data['amount_paid'] : $total;
            $change = $data['payment_method'] === 'cash' ? max(0, $amountPaid - $total) : null;

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'channel' => 'kasir',
                'order_date' => $now,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'additional_fee' => 0,
                'total' => $total,
                'cost_total' => $costTotal,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'payment_method' => $data['payment_method'],
                'amount_paid' => $amountPaid,
                'change' => $change,
                'note' => $data['note'] ?? null,
                'stock_reserved' => true,
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

                // Kurangi stok + catat movement
                if ($r['variant_id']) {
                    $v = $r['product']->variants->firstWhere('id', $r['variant_id']);
                    $before = $v->stock;
                    $v->decrement('stock', $r['quantity']);
                    $v->refresh();
                    StockMovement::create([
                        'product_id' => $r['product']->id,
                        'variant_id' => $r['variant_id'],
                        'type' => 'out',
                        'quantity' => $r['quantity'],
                        'stock_before' => $before,
                        'stock_after' => $v->stock,
                        'reason' => 'Penjualan melalui kasir',
                        'reference' => $invoiceNumber,
                        'movement_date' => $now,
                    ]);
                } else {
                    $before = $r['product']->stock;
                    $r['product']->decrement('stock', $r['quantity']);
                    $r['product']->refresh();
                    StockMovement::create([
                        'product_id' => $r['product']->id,
                        'variant_id' => null,
                        'type' => 'out',
                        'quantity' => $r['quantity'],
                        'stock_before' => $before,
                        'stock_after' => $r['product']->stock,
                        'reason' => 'Penjualan melalui kasir',
                        'reference' => $invoiceNumber,
                        'movement_date' => $now,
                    ]);
                }
            }

            DB::commit();

            $sale = Sale::with('items')->find($sale->id);
            $store = StoreSetting::find(1);
            return view('cashier.receipt', compact('sale', 'store'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ERROR: ' . $e->getMessage())->withInput();
        }
    }

    public function receipt($sale)
    {
        $sale = Sale::with('items')->findOrFail($sale);
        $store = StoreSetting::find(1);
        return view('cashier.receipt', compact('sale', 'store'));
    }
}
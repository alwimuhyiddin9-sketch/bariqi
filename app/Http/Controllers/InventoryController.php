<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\AppSetting;
use App\Services\InventoryService;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request)
    {
        $minStockLimit = AppSetting::find(1)?->min_stock_limit ?? 5;
        $query = Product::with(['category', 'variants']);
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }
        $products = $query->orderBy('name')->get();

        $rows = [];
        $totalValue = 0; $totalStock = 0;
        foreach ($products as $p) {
            if ($p->has_variants) {
                foreach ($p->variants as $v) {
                    $min = $p->min_stock ?: $minStockLimit;
                    $totalValue += $v->stock * $v->cost_price;
                    $totalStock += $v->stock;
                    $status = $v->stock === 0 ? 'habis' : ($v->stock <= $min ? 'menipis' : 'aman');
                    $rows[] = ['product' => $p, 'variant' => $v, 'min' => $min, 'status' => $status, 'value' => $v->stock * $v->cost_price];
                }
            } else {
                $min = $p->min_stock ?: $minStockLimit;
                $totalValue += $p->stock * $p->cost_price;
                $totalStock += $p->stock;
                $status = $p->stock === 0 ? 'habis' : ($p->stock <= $min ? 'menipis' : 'aman');
                $rows[] = ['product' => $p, 'variant' => null, 'min' => $min, 'status' => $status, 'value' => $p->stock * $p->cost_price];
            }
        }

        $statusFilter = $request->status;
        if ($statusFilter === 'low') $rows = array_filter($rows, fn ($r) => $r['status'] !== 'aman');
        if ($statusFilter === 'ok') $rows = array_filter($rows, fn ($r) => $r['status'] === 'aman');

        return view('inventory.index', compact('rows', 'totalValue', 'totalStock', 'minStockLimit'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required',
            'reference' => 'nullable',
            'movement_date' => 'nullable|date',
        ]);

        try {
            $this->inventory->adjustStock(
                $data['product_id'],
                $data['variant_id'] ?? null,
                $data['type'],
                $data['quantity'],
                $data['reason'],
                $data['reference'] ?? null,
                $data['movement_date'] ? \Illuminate\Support\Carbon::parse($data['movement_date']) : null
            );
            return back()->with('success', $data['type'] === 'in' ? 'Barang masuk dicatat' : ($data['type'] === 'out' ? 'Barang keluar dicatat' : 'Penyesuaian stok dicatat'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

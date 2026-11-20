<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\StockMovement;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $variants = ProductVariant::with('product')->orderBy('product_id')->get();
        return view('variants.index', compact('variants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|unique:product_variants,sku',
            'color' => 'nullable',
            'size' => 'nullable',
            'cost_price' => 'nullable|integer|min:0',
            'sell_price' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
        ]);

        $v = ProductVariant::create($data);
        Product::where('id', $v->product_id)->update(['has_variants' => true]);
        if ($v->stock > 0) {
            StockMovement::create([
                'product_id' => $v->product_id, 'variant_id' => $v->id,
                'type' => 'in', 'quantity' => $v->stock, 'stock_before' => 0, 'stock_after' => $v->stock,
                'reason' => 'Penambahan varian baru', 'reference' => 'VARIANT-ADD', 'movement_date' => now(),
            ]);
        }
        return redirect()->route('variants.index')->with('success', 'Varian ditambahkan');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'sku' => 'required|unique:product_variants,sku,' . $variant->id,
            'color' => 'nullable', 'size' => 'nullable',
            'cost_price' => 'nullable|integer|min:0', 'sell_price' => 'nullable|integer|min:0',
        ]);
        $variant->update($data);
        return redirect()->route('variants.index')->with('success', 'Varian diperbarui');
    }

    public function destroy(ProductVariant $variant)
    {
        $used = \App\Models\SaleItem::where('variant_id', $variant->id)->count();
        if ($used > 0) {
            return back()->with('error', 'Varian pernah dipakai dalam transaksi, tidak dapat dihapus.');
        }
        $variant->delete();
        if (ProductVariant::where('product_id', $variant->product_id)->count() === 0) {
            Product::where('id', $variant->product_id)->update(['has_variants' => false]);
        }
        return redirect()->route('variants.index')->with('success', 'Varian dihapus');
    }
}

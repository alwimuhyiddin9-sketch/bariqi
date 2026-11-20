<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants']);
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }
        if ($c = $request->category_id) $query->where('category_id', $c);
        if ($st = $request->status) $query->where('status', $st);
        $products = $query->orderByDesc('created_at')->get();
        $categories = Category::orderBy('name')->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'sku' => 'nullable|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|url',
            'cost_price' => 'nullable|integer|min:0',
            'sell_price' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable',
            'description' => 'nullable',
            'status' => 'in:active,inactive',
            'has_variants' => 'boolean',
            'stock' => 'nullable|integer|min:0',
            'variants' => 'nullable|array',
        ]);

        if (empty($data['sku'])) {
            $count = Product::count();
            $data['sku'] = 'BRG-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        $variants = $data['variants'] ?? [];
        $hasVariants = !empty($variants);

        DB_TRANSACTION: {
            $product = Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'category_id' => $data['category_id'],
                'image' => $data['image'] ?? null,
                'cost_price' => $data['cost_price'] ?? 0,
                'sell_price' => $data['sell_price'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 5,
                'unit' => $data['unit'] ?? 'pcs',
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'has_variants' => $hasVariants,
                'stock' => $hasVariants ? 0 : ($data['stock'] ?? 0),
            ]);

            if ($hasVariants) {
                foreach ($variants as $v) {
                    $nv = $product->variants()->create([
                        'sku' => $v['sku'],
                        'color' => $v['color'] ?? null,
                        'size' => $v['size'] ?? null,
                        'cost_price' => $v['cost_price'] ?? 0,
                        'sell_price' => $v['sell_price'] ?? 0,
                        'stock' => $v['stock'] ?? 0,
                    ]);
                    if ($nv->stock > 0) {
                        StockMovement::create([
                            'product_id' => $product->id, 'variant_id' => $nv->id,
                            'type' => 'in', 'quantity' => $nv->stock, 'stock_before' => 0, 'stock_after' => $nv->stock,
                            'reason' => 'Stok awal varian baru', 'reference' => 'INIT-STOCK', 'movement_date' => now(),
                        ]);
                    }
                }
            } elseif ($product->stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id, 'variant_id' => null,
                    'type' => 'in', 'quantity' => $product->stock, 'stock_before' => 0, 'stock_after' => $product->stock,
                    'reason' => 'Stok awal barang baru', 'reference' => 'INIT-STOCK', 'movement_date' => now(),
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Barang ditambahkan');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required',
            'sku' => 'nullable|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|url',
            'cost_price' => 'nullable|integer|min:0',
            'sell_price' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable',
            'description' => 'nullable',
            'status' => 'in:active,inactive',
            'has_variants' => 'boolean',
            'variants' => 'nullable|array',
        ]);

        $variants = $data['variants'] ?? [];
        $hasVariants = !empty($variants);

        $product->update([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? $product->sku,
            'category_id' => $data['category_id'],
            'image' => $data['image'] ?? $product->image,
            'cost_price' => $data['cost_price'] ?? $product->cost_price,
            'sell_price' => $data['sell_price'] ?? $product->sell_price,
            'min_stock' => $data['min_stock'] ?? $product->min_stock,
            'unit' => $data['unit'] ?? $product->unit,
            'description' => $data['description'] ?? $product->description,
            'status' => $data['status'] ?? $product->status,
            'has_variants' => $hasVariants,
            'stock' => $hasVariants ? 0 : $product->stock,
        ]);

        if ($hasVariants) {
            $incomingIds = collect($variants)->pluck('id')->filter()->toArray();
            $product->variants()->whereNotIn('id', $incomingIds)->delete();
            foreach ($variants as $v) {
                if (!empty($v['id'])) {
                    $product->variants()->where('id', $v['id'])->update([
                        'sku' => $v['sku'], 'color' => $v['color'] ?? null, 'size' => $v['size'] ?? null,
                        'cost_price' => $v['cost_price'] ?? 0, 'sell_price' => $v['sell_price'] ?? 0, 'stock' => $v['stock'] ?? 0,
                    ]);
                } else {
                    $nv = $product->variants()->create([
                        'sku' => $v['sku'], 'color' => $v['color'] ?? null, 'size' => $v['size'] ?? null,
                        'cost_price' => $v['cost_price'] ?? 0, 'sell_price' => $v['sell_price'] ?? 0, 'stock' => $v['stock'] ?? 0,
                    ]);
                    if ($nv->stock > 0) {
                        StockMovement::create([
                            'product_id' => $product->id, 'variant_id' => $nv->id,
                            'type' => 'in', 'quantity' => $nv->stock, 'stock_before' => 0, 'stock_after' => $nv->stock,
                            'reason' => 'Penambahan varian baru', 'reference' => 'VARIANT-ADD', 'movement_date' => now(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Barang diperbarui');
    }

    public function destroy(Product $product)
    {
        $product->update(['status' => 'inactive']);
        return redirect()->route('products.index')->with('success', 'Barang dinonaktifkan');
    }
}

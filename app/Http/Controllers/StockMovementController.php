<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\ProductVariant;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with('product');
        if ($type = $request->type) $query->where('type', $type);
        $movements = $query->orderByDesc('movement_date')->limit(200)->get();

        $variantIds = $movements->pluck('variant_id')->filter()->unique();
        $variants = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        return view('inventory.movements', compact('movements', 'variants'));
    }
}

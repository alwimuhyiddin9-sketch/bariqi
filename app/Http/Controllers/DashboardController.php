<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\AppSetting;
use App\Models\StoreSetting;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $startOfToday = $now->copy()->startOfDay();
        $startOfMonth = $now->copy()->startOfMonth();
        $minStockLimit = AppSetting::find(1)?->min_stock_limit ?? 5;

        $products = Product::with('variants')->get();
        $totalProducts = $products->count();
        $totalVariants = \App\Models\ProductVariant::count();

        $totalStock = 0;
        $inventoryValue = 0;
        $lowStockItems = [];

        foreach ($products as $p) {
            if ($p->has_variants) {
                foreach ($p->variants as $v) {
                    $totalStock += $v->stock;
                    $inventoryValue += $v->stock * $v->cost_price;
                    if ($v->stock <= ($p->min_stock ?: $minStockLimit)) {
                        $lowStockItems[] = [
                            'product_id' => $p->id,
                            'name' => $p->name,
                            'sku' => $v->sku,
                            'variant_info' => collect([$v->color, $v->size])->filter()->implode(' / '),
                            'stock' => $v->stock,
                            'min_stock' => $p->min_stock ?: $minStockLimit,
                        ];
                    }
                }
            } else {
                $totalStock += $p->stock;
                $inventoryValue += $p->stock * $p->cost_price;
                if ($p->stock <= ($p->min_stock ?: $minStockLimit)) {
                    $lowStockItems[] = [
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'variant_info' => null,
                        'stock' => $p->stock,
                        'min_stock' => $p->min_stock ?: $minStockLimit,
                    ];
                }
            }
        }

        $salesToday = Sale::where('order_date', '>=', $startOfToday)->where('order_date', '<=', $now)->get();
        $todayRevenue = $salesToday->where('order_status', '!=', 'cancelled')->sum('total');

        $salesMonth = Sale::where('order_date', '>=', $startOfMonth)->where('order_date', '<=', $now)->get();
        $monthRevenue = $salesMonth->where('order_status', '!=', 'cancelled')->sum('total');
        $monthProfit = $salesMonth->where('order_status', '!=', 'cancelled')->reduce(fn ($c, $s) => $c + ($s->total - $s->cost_total), 0);

        // Grafik 7 hari terakhir
        $sales7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i);
            $key = $d->copy()->startOfDay();
            $keyEnd = $key->copy()->addDay();
            $daySales = Sale::where('order_date', '>=', $key)->where('order_date', '<', $keyEnd)->where('order_status', '!=', 'cancelled')->get();
            $sales7Days[] = [
                'label' => $d->format('d M'),
                'date' => $key->toDateString(),
                'omzet' => $daySales->sum('total'),
                'transaksi' => $daySales->count(),
            ];
        }

        // Penjualan per saluran (bulan ini)
        $byChannel = [];
        foreach ($salesMonth->where('order_status', '!=', 'cancelled')->groupBy('channel') as $ch => $rows) {
            $byChannel[] = ['channel' => $ch, 'total' => $rows->sum('total'), 'count' => $rows->count()];
        }

        // Ringkasan online vs offline
        $onlineTotal = 0; $onlineCount = 0; $offlineTotal = 0; $offlineCount = 0;
        foreach ($salesMonth->where('order_status', '!=', 'cancelled') as $s) {
            if ($s->channel === 'kasir') {
                $offlineTotal += $s->total; $offlineCount++;
            } else {
                $onlineTotal += $s->total; $onlineCount++;
            }
        }

        // Barang terlaris (12 bulan)
        $yearStart = $now->copy()->subMonths(11)->startOfMonth();
        $saleItems = SaleItem::whereHas('sale', function ($q) use ($yearStart) {
            $q->where('order_date', '>=', $yearStart)->where('order_status', '!=', 'cancelled');
        })->with('product')->get();

        $agg = [];
        foreach ($saleItems as $it) {
            $key = $it->product_id;
            if (!isset($agg[$key])) {
                $agg[$key] = ['id' => $it->product_id, 'name' => $it->product->name, 'image' => $it->product->image, 'qty' => 0, 'revenue' => 0];
            }
            $agg[$key]['qty'] += $it->quantity;
            $agg[$key]['revenue'] += $it->subtotal;
        }
        $topProducts = collect(array_values($agg))->sortByDesc('qty')->take(5)->values();

        $recentSales = Sale::with('items')->orderByDesc('order_date')->take(5)->get();
        $recentMovements = StockMovement::with('product')->orderByDesc('movement_date')->take(6)->get();

        $admin = User::first();

        return view('dashboard', compact(
            'totalProducts', 'totalVariants', 'totalStock', 'inventoryValue',
            'todayRevenue', 'monthRevenue', 'monthProfit', 'lowStockItems',
            'sales7Days', 'byChannel', 'onlineTotal', 'onlineCount', 'offlineTotal', 'offlineCount',
            'topProducts', 'recentSales', 'recentMovements', 'admin'
        ));
    }
}

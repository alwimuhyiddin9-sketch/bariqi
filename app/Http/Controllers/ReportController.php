<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Expense;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Models\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function parseRange(Request $request)
    {
        $preset = $request->range ?? 'month';
        $now = now();
        switch ($preset) {
            case 'today': $start = $now->copy()->startOfDay(); break;
            case 'week': $start = $now->copy()->startOfWeek(); break;
            case 'year': $start = $now->copy()->startOfYear(); break;
            case 'custom':
                $start = $request->from ? \Illuminate\Support\Carbon::parse($request->from) : $now->copy()->startOfMonth();
                $end = $request->to ? \Illuminate\Support\Carbon::parse($request->to)->endOfDay() : $now;
                return [$start, $end, $preset];
            case 'month':
            default: $start = $now->copy()->startOfMonth();
        }
        return [$start, $now, $preset];
    }

    public function index(Request $request)
    {
        $type = $request->type ?? 'barang';
        [$start, $end, $preset] = $this->parseRange($request);
        $store = StoreSetting::find(1);
        $app = AppSetting::find(1);

        if ($type === 'barang') {
            $products = Product::with('category', 'variants')->orderBy('name')->get();
           return view('reports.index', compact('products', 'store', 'app', 'start', 'end', 'preset', 'type'));
        }

        if ($type === 'persediaan') {
            $minStockLimit = $app?->min_stock_limit ?? 5;
            $products = Product::with('variants')->get();
            $rows = []; $totalValue = 0; $totalStock = 0; $lowCount = 0;
            foreach ($products as $p) {
                if ($p->has_variants) {
                    foreach ($p->variants as $v) {
                        $min = $p->min_stock ?: $minStockLimit;
                        $totalValue += $v->stock * $v->cost_price;
                        $totalStock += $v->stock;
                        $low = $v->stock <= $min;
                        if ($low) $lowCount++;
                        $rows[] = ['product' => $p, 'variant' => $v, 'min' => $min, 'low' => $low, 'value' => $v->stock * $v->cost_price];
                    }
                } else {
                    $min = $p->min_stock ?: $minStockLimit;
                    $totalValue += $p->stock * $p->cost_price;
                    $totalStock += $p->stock;
                    $low = $p->stock <= $min;
                    if ($low) $lowCount++;
                    $rows[] = ['product' => $p, 'variant' => null, 'min' => $min, 'low' => $low, 'value' => $p->stock * $p->cost_price];
                }
            }
            $movements = StockMovement::with('product')->whereBetween('movement_date', [$start, $end])->orderByDesc('movement_date')->get();
           return view('reports.index', compact('rows', 'movements', 'totalValue', 'totalStock', 'lowCount', 'store', 'app', 'start', 'end', 'preset', 'type'));
        }

        if ($type === 'penjualan') {
            $sales = Sale::with('items.product')->whereBetween('order_date', [$start, $end])->orderByDesc('order_date')->get();
            $active = $sales->where('order_status', '!=', 'cancelled');
            $totalSales = $active->sum('total');
            $totalTrx = $active->count();
            $totalDiscount = $active->sum('discount');
            $byChannel = $active->groupBy('channel')->map(fn ($r) => ['count' => $r->count(), 'total' => $r->sum('total')])->toArray();
            $topProducts = SaleItem::whereHas('sale', function ($q) use ($start, $end) {
                $q->whereBetween('order_date', [$start, $end])->where('order_status', '!=', 'cancelled');
            })->with('product')->get()->groupBy('product_id')->map(function ($items) {
                return ['name' => $items->first()->product->name, 'qty' => $items->sum('quantity'), 'revenue' => $items->sum('subtotal')];
            })->sortByDesc('qty')->take(10)->values();
           return view('reports.index', compact('sales', 'totalSales', 'totalTrx', 'totalDiscount', 'byChannel', 'topProducts', 'store', 'app', 'start', 'end', 'preset', 'type'));
        }
        if ($type === 'keuangan') {
            $sales = Sale::with('items')->whereBetween('order_date', [$start, $end])->get();
            $active = $sales->where('order_status', '!=', 'cancelled');
            $expenses = Expense::whereBetween('date', [$start, $end])->get();

            $pendapatan = $active->sum('subtotal');
            $diskon = $active->sum('discount');
            $biayaTambahan = $active->sum('additional_fee');
            $penjualanBersih = $pendapatan - $diskon + $biayaTambahan;
            $hpp = $active->sum('cost_total');
            $labaKotor = $penjualanBersih - $hpp;
            $totalPengeluaran = $expenses->sum('amount');
            $labaBersih = $labaKotor - $totalPengeluaran;
            $expByCat = $expenses->groupBy('category')->map(fn ($r) => $r->sum('amount'))->toArray();
          return view('reports.index', compact('sales', 'expenses', 'pendapatan', 'diskon', 'biayaTambahan', 'penjualanBersih', 'hpp', 'labaKotor', 'totalPengeluaran', 'labaBersih', 'expByCat', 'store', 'app', 'start', 'end', 'preset', 'type'));
        }
        abort(404);
    }

    public function pdf(Request $request, $type)
    {
        $request->merge(['type' => $type]);
        [$start, $end, $preset] = $this->parseRange($request);
        $store = StoreSetting::find(1);

        if ($type === 'barang') {
            $products = Product::with('category', 'variants')->get();
            $pdf = Pdf::loadView('reports.pdf.barang', compact('products', 'store', 'start', 'end', 'preset'));
            return $pdf->download('laporan-barang.pdf');
        }
        if ($type === 'persediaan') {
            $products = Product::with('variants')->get();
            $pdf = Pdf::loadView('reports.pdf.persediaan', compact('products', 'store', 'start', 'end', 'preset'));
            return $pdf->download('laporan-persediaan.pdf');
        }
        if ($type === 'penjualan') {
            $sales = Sale::with('items.product')->whereBetween('order_date', [$start, $end])->get();
            $pdf = Pdf::loadView('reports.pdf.penjualan', compact('sales', 'store', 'start', 'end', 'preset'));
            return $pdf->download('laporan-penjualan.pdf');
        }
        if ($type === 'keuangan') {
            $sales = Sale::with('items')->whereBetween('order_date', [$start, $end])->get();
            $expenses = Expense::whereBetween('date', [$start, $end])->get();
            $pdf = Pdf::loadView('reports.pdf.keuangan', compact('sales', 'expenses', 'store', 'start', 'end', 'preset'));
            return $pdf->download('laporan-keuangan.pdf');
        }
        abort(404);
    }
}

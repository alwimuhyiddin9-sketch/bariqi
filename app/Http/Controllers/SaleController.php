<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Services\SaleService;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService) {}

    public function index(Request $request)
    {
        $query = Sale::with('items');
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhere('external_order', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%");
            });
        }
        if ($c = $request->channel) $query->where('channel', $c);
        if ($st = $request->order_status) $query->where('order_status', $st);
        if ($ps = $request->payment_status) $query->where('payment_status', $ps);
        $sales = $query->orderByDesc('order_date')->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = \App\Models\Product::with('variants')->where('status', 'active')->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required',
            'external_order' => 'nullable',
            'customer_name' => 'nullable',
            'order_date' => 'nullable|date',
            'discount' => 'nullable|integer|min:0',
            'additional_fee' => 'nullable|integer|min:0',
            'note' => 'nullable',
            'payment_status' => 'in:unpaid,paid,refunded',
            'order_status' => 'in:pending,processing,shipped,completed,cancelled,returned',
            'payment_method' => 'nullable|in:cash,transfer,qris',
            'amount_paid' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($data['items'] as &$item) {
            if (empty($item['variant_id'])) {
                $item['variant_id'] = null;
            }
        }
        unset($item);

        try {
            $this->saleService->createSale([
                'channel' => $data['channel'],
                'external_order' => $data['external_order'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'order_date' => $data['order_date'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'additional_fee' => $data['additional_fee'] ?? 0,
                'note' => $data['note'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'order_status' => $data['order_status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'amount_paid' => $data['amount_paid'] ?? null,
                'items' => $data['items'],
            ]);
            return redirect()->route('sales.index')->with('success', 'Pesanan berhasil dicatat');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product');
        return view('transactions.show', compact('sale'));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,completed,cancelled,returned',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'note' => 'nullable',
        ]);
        try {
            $this->saleService->updateStatus($sale, $data['order_status'], $data['payment_status'] ?? null, $data['note'] ?? null);
            return back()->with('success', 'Status transaksi diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function returnItem(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'item_id' => 'required|exists:sale_items,id',
            'quantity' => 'required|integer|min:1',
        ]);
        try {
            $this->saleService->partialReturn($sale, $data['item_id'], $data['quantity']);
            return back()->with('success', 'Retur barang dicatat');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
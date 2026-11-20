<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk {{ $sale->invoice_number }}</title>
<style>
@page { margin: 8mm; size: 80mm auto; }
@media print { body { margin: 0; } .no-print { display: none !important; } }
* { -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box; }
body { background: #f4f6f9; padding: 16px; display: flex; justify-content: center; font-family: 'Courier New', monospace; }
.receipt { background: #fff; width: 72mm; padding: 8mm 6mm; font-size: 11px; color: #000; line-height: 1.5; }
.receipt h1 { font-size: 14px; font-weight: bold; text-align: center; margin: 0 0 2px; }
.receipt .center { text-align: center; font-size: 10px; margin: 1px 0; }
.receipt .meta { font-size: 10px; }
.receipt .divider { border-top: 1px dashed #000; margin: 6px 0; }
.receipt .item-name { font-size: 10px; font-weight: bold; }
.receipt .item-row { display: flex; justify-content: space-between; font-size: 10px; }
.receipt .totals { font-size: 11px; }
.receipt .totals .row { display: flex; justify-content: space-between; }
.receipt .totals .bold { font-weight: bold; font-size: 13px; margin: 2px 0; }
.receipt .footer { text-align: center; margin-top: 6px; font-size: 10px; }
.receipt .footer .small { font-size: 9px; color: #444; }
.btn-print { display: inline-block; padding: 8px 16px; background: #1d4ed8; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; margin: 4px; }
.btn-new { display: inline-block; padding: 8px 16px; background: #fff; color: #1d4ed8; border: 1px solid #1d4ed8; border-radius: 6px; font-size: 13px; text-decoration: none; margin: 4px; }
.actions { position: fixed; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; }
</style>
</head>
<body>
<div class="receipt">
    <h1>{{ $store?->name ?? 'Bariqi.co Store' }}</h1>
    <p class="center">{{ $store?->address ?? 'Jl. Merdeka No. 45, Bandung' }}</p>
    <p class="center">Telp: {{ $store?->phone ?? '081234567890' }}</p>
    <div class="divider"></div>
    <div class="meta">
        <div>{{ $sale->invoice_number }}</div>
        <div>{{ $sale->order_date->format('d/m/Y H:i') }}</div>
        <div>Pelanggan: {{ $sale->customer_name ?? 'Pelanggan Toko' }}</div>
    </div>
    <div class="divider"></div>
    @foreach($sale->items as $it)
        <div class="item-name">{{ $it->product_name }}@if($it->variant_info) ({{ $it->variant_info }})@endif</div>
        <div class="item-row"><span>{{ $it->quantity }} x Rp {{ number_format($it->sell_price, 0, ',', '.') }}</span><span>Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span></div>
    @endforeach
    <div class="divider"></div>
    <div class="totals">
        <div class="row"><span>Subtotal</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
        @if($sale->discount > 0)
            <div class="row"><span>Diskon</span><span>-Rp {{ number_format($sale->discount, 0, ',', '.') }}</span></div>
        @endif
        <div class="row bold"><span>TOTAL</span><span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span></div>
        <div class="divider"></div>
        @if($sale->payment_method)
            <div class="row"><span>{{ ['cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS'][$sale->payment_method] }}</span><span>Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</span></div>
            @if($sale->change !== null)
                <div class="row"><span>Kembali</span><span>Rp {{ number_format($sale->change, 0, ',', '.') }}</span></div>
            @endif
        @endif
    </div>
    <div class="divider"></div>
    <div class="footer">
        <div>Terima kasih telah berbelanja!</div>
        <div class="small">Barang yang sudah dibeli tidak dapat ditukar</div>
    </div>
</div>
<div class="actions no-print">
    <button onclick="window.print()" class="btn-print">🖨 Cetak Struk</button>
    <a href="{{ route('cashier.index') }}" class="btn-new">+ Transaksi Baru</a>
</div>
</body>
</html>
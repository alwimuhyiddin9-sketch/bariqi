@extends('layouts.app')
@section('title', 'Detail Transaksi — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); $channelLabel = ['shopee'=>'Shopee','tokopedia'=>'Tokopedia','tiktok'=>'TikTok Shop','instagram'=>'Instagram','whatsapp'=>'WhatsApp','kasir'=>'Kasir','lainnya'=>'Lainnya']; @endphp
<div class="page-header"><div><h1>Detail Transaksi</h1><p class="desc">{{ $sale->invoice_number }}</p></div><a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="card"><div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<p class="text-muted small mb-0">No. Transaksi</p><p class="fw-bold" style="font-family:monospace">{{ $sale->invoice_number }}</p>
<p class="text-muted small mb-0 mt-2">Saluran</p><p>{{ $channelLabel[$sale->channel] ?? $sale->channel }}</p>
<p class="text-muted small mb-0 mt-2">Pelanggan</p><p>{{ $sale->customer_name ?: '-' }}</p>
@if($sale->external_order)<p class="text-muted small mb-0 mt-2">No. Pesanan Eksternal</p><p style="font-family:monospace">{{ $sale->external_order }}</p>@endif
<p class="text-muted small mb-0 mt-2">Tanggal</p><p>{{ $sale->order_date->format('d/m/Y H:i') }}</p>
<p class="text-muted small mb-0 mt-2">Metode Bayar</p><p>{{ $sale->payment_method ? ['cash'=>'Tunai','transfer'=>'Transfer Bank','qris'=>'QRIS'][$sale->payment_method] : '-' }}</p>
</div>
<div class="col-md-6">
<p class="text-muted small mb-0">Status Pesanan</p>
<form method="POST" action="{{ route('sales.updateStatus', $sale) }}">@csrf @method('PATCH')
<select name="order_status" class="form-select form-select-sm" style="width:auto;display:inline-block" onchange="this.form.submit()"><option value="pending" @if($sale->order_status=='pending')selected @endif>Menunggu</option><option value="processing" @if($sale->order_status=='processing')selected @endif>Diproses</option><option value="shipped" @if($sale->order_status=='shipped')selected @endif>Dikirim</option><option value="completed" @if($sale->order_status=='completed')selected @endif>Selesai</option><option value="cancelled" @if($sale->order_status=='cancelled')selected @endif>Dibatalkan</option><option value="returned" @if($sale->order_status=='returned')selected @endif>Diretur</option></select>
</form>
@if($sale->order_status !== 'cancelled' && $sale->order_status !== 'returned')
<form method="POST" action="{{ route('sales.updateStatus', $sale) }}" class="mt-3" onsubmit="return confirm('Batalkan transaksi ini?')">@csrf @method('PATCH')
<input type="hidden" name="order_status" value="cancelled">
<button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Batalkan Transaksi</button>
</form>
@endif
</div>
</div>
<hr>
<h6>Daftar Barang</h6>
@foreach($sale->items as $it)
<div class="d-flex justify-content-between border-bottom py-2"><div><p class="fw-medium mb-0">{{ $it->product_name }}</p>@if($it->variant_info)<p class="text-muted small mb-0">{{ $it->variant_info }}</p>@endif<p class="text-muted small">{{ $it->quantity }} x {{ $fmtRupiah($it->sell_price) }}</p></div><p class="fw-semibold">{{ $fmtRupiah($it->subtotal) }}</p></div>
@endforeach
<div class="mt-3">
<div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>{{ $fmtRupiah($sale->subtotal) }}</span></div>
@if($sale->discount>0)<div class="d-flex justify-content-between"><span class="text-muted">Diskon</span><span class="text-danger">−{{ $fmtRupiah($sale->discount) }}</span></div>@endif
@if($sale->additional_fee>0)<div class="d-flex justify-content-between"><span class="text-muted">Biaya Tambahan</span><span>+{{ $fmtRupiah($sale->additional_fee) }}</span></div>@endif
<div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span class="text-primary">{{ $fmtRupiah($sale->total) }}</span></div>
@if($sale->amount_paid)<div class="d-flex justify-content-between mt-1"><span class="text-muted">Dibayar</span><span>{{ $fmtRupiah($sale->amount_paid) }}</span></div>@endif
@if($sale->change !== null)<div class="d-flex justify-content-between"><span class="text-muted">Kembalian</span><span>{{ $fmtRupiah($sale->change) }}</span></div>@endif
</div>
@if($sale->note)<hr><p class="text-muted small mb-0">Catatan</p><p class="small">{{ $sale->note }}</p>@endif
</div></div>
@endsection
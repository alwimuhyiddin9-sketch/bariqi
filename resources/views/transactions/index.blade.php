@extends('layouts.app')
@section('title', 'Riwayat Transaksi — Bariqi.co')
@section('content')
@php
    $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
    $channelLabel = ['shopee'=>'Shopee','tokopedia'=>'Tokopedia','tiktok'=>'TikTok Shop','instagram'=>'Instagram','whatsapp'=>'WhatsApp','kasir'=>'Kasir','lainnya'=>'Lainnya'];
    $statusMap = ['pending'=>['Menunggu','bg-warning text-white'],'processing'=>['Diproses','bg-primary text-white'],'shipped'=>['Dikirim','bg-info text-white'],'completed'=>['Selesai','bg-success text-white'],'cancelled'=>['Dibatalkan','bg-danger text-white'],'returned'=>['Diretur','bg-secondary text-white']];
@endphp
<div class="page-header"><div><h1>Riwayat Transaksi</h1><p class="desc">Seluruh riwayat transaksi penjualan</p></div></div>
<form method="GET" class="d-flex gap-2 mb-3" style="max-width:900px">
    <input type="text" name="search" class="form-control" placeholder="Cari nomor transaksi atau pelanggan..." value="{{ request('search') }}">
    <select name="channel" class="form-select" style="width:140px"><option value="">Semua Saluran</option>@foreach($channelLabel as $v=>$l)<option value="{{ $v }}" @if(request('channel')==$v) selected @endif>{{ $l }}</option>@endforeach</select>
    <select name="order_status" class="form-select" style="width:180px"><option value="">Semua Status</option><option value="pending" @if(request('order_status')=='pending') selected @endif>Menunggu</option><option value="processing" @if(request('order_status')=='processing') selected @endif>Diproses</option><option value="shipped" @if(request('order_status')=='shipped') selected @endif>Dikirim</option><option value="completed" @if(request('order_status')=='completed') selected @endif>Selesai</option><option value="cancelled" @if(request('order_status')=='cancelled') selected @endif>Dibatalkan</option><option value="returned" @if(request('order_status')=='returned') selected @endif>Diretur</option></select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>
<div class="card"><div class="card-body">
<table class="table table-hover mb-0">
<thead><tr><th>No. Transaksi</th><th>Saluran</th><th>Tanggal</th><th class="text-end">Item</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
<tbody>
@foreach($sales as $s)
<tr>
<td><p class="fw-medium mb-0">{{ $s->invoice_number }}</p>@if($s->external_order)<p class="text-muted small mb-0" style="font-family:monospace">{{ $s->external_order }}</p>@endif</td>
<td><span class="badge bg-light text-primary">{{ $channelLabel[$s->channel] ?? $s->channel }}</span></td>
<td class="small text-muted">{{ $s->order_date->format('d/m/Y H:i') }}</td>
<td class="text-end small">{{ $s->items->count() }} ({{ $s->qty_total }})</td>
<td class="text-end fw-semibold">{{ $fmtRupiah($s->total) }}</td>
<td>@php $st = $statusMap[$s->order_status] ?? [$s->order_status,'bg-secondary text-white']; @endphp<span class="badge {{ $st[1] }}">{{ $st[0] }}</span></td>
<td class="text-end"><a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
</tr>
@endforeach
</tbody>
</table>
</div></div>
@endsection
@extends('layouts.app')
@section('title', 'Persediaan Barang — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); $fmtNum = fn($n) => number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header"><div><h1>Persediaan Barang</h1><p class="desc">Pantau stok dan nilai persediaan seluruh barang</p></div></div>

<div class="stat-grid">
    <div class="stat-card"><p class="title">Total Unit Stok</p><div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-boxes"></i></div><p class="value">{{ $fmtNum($totalStock) }}</p><p class="hint">Seluruh unit</p></div>
    <div class="stat-card"><p class="title">Nilai Persediaan</p><div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-wallet"></i></div><p class="value" style="color:#15803d">{{ $fmtRupiah($totalValue) }}</p><p class="hint">Total nilai modal</p></div>
    <div class="stat-card"><p class="title">Stok Menipis</p><div class="icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-exclamation-triangle"></i></div><p class="value" style="color:#d97706">{{ $fmtNum(count(array_filter($rows, fn($r)=>$r['status']!=='aman'))) }}</p><p class="hint">Perlu direstok</p></div>
</div>

<form method="GET" class="d-flex gap-2 mb-3" style="max-width:600px">
    <input type="text" name="search" class="form-control" placeholder="Cari nama atau SKU..." value="{{ request('search') }}">
    <select name="status" class="form-select" style="width:180px">
        <option value="">Semua Status</option>
        <option value="low" @if(request('status')=='low')selected @endif>Stok Menipis/Habis</option>
        <option value="ok" @if(request('status')=='ok')selected @endif>Stok Aman</option>
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card"><div class="card-body">
<table class="table table-hover mb-0">
<thead><tr><th>Barang</th><th>SKU / Varian</th><th class="text-end">Modal</th><th class="text-end">Stok</th><th class="text-end">Nilai</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
<tbody>
@foreach($rows as $r)
    <tr>
        <td class="fw-medium">{{ $r['product']->name }}</td>
        <td><p class="small mb-0" style="font-family:monospace">{{ $r['variant']?->sku ?? $r['product']->sku }}</p>@if($r['variant'] && $r['variant']->variant_info)<p class="text-muted small mb-0">{{ $r['variant']->variant_info }}</p>@endif</td>
        <td class="text-end small">{{ $fmtRupiah($r['variant']?->cost_price ?? $r['product']->cost_price) }}</td>
        <td class="text-end fw-semibold">{{ $fmtNum($r['variant']?->stock ?? $r['product']->stock) }}</td>
        <td class="text-end small">{{ $fmtRupiah($r['value']) }}</td>
        <td>@if($r['status']=='aman')<span class="badge bg-success">Aman</span>@elseif($r['status']=='menipis')<span class="badge bg-warning">Menipis</span>@else<span class="badge bg-danger">Habis</span>@endif</td>
        <td class="text-end">
            <button class="btn btn-sm btn-outline-success" onclick='openAdjust({{ $r["product"]->id }}, {{ $r["variant"]?->id ?? "null" }}, "{{ $r["product"]->name }}", "in")'><i class="bi bi-box-arrow-in-down"></i> Masuk</button>
            <button class="btn btn-sm btn-outline-warning" onclick='openAdjust({{ $r["product"]->id }}, {{ $r["variant"]?->id ?? "null" }}, "{{ $r["product"]->name }}", "adjustment")'><i class="bi bi-sliders"></i> Sesuaikan</button>
        </td>
    </tr>
@endforeach
</tbody>
</table>
</div></div>

<div class="modal fade" id="adjustModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('inventory.adjust') }}">@csrf
<div class="modal-header"><h5 class="modal-title" id="adjustTitle">Barang Masuk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<input type="hidden" name="product_id" id="adj_product"><input type="hidden" name="variant_id" id="adj_variant"><input type="hidden" name="type" id="adj_type">
<div class="mb-3"><label class="form-label">Barang</label><input type="text" class="form-control" id="adj_name" readonly></div>
<div class="mb-3"><label class="form-label">Jumlah</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="mb-3"><label class="form-label">Alasan</label><input type="text" name="reason" class="form-control" value="Pembelian stok baru" required></div>
<div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="movement_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
<div><label class="form-label">Referensi (opsional)</label><input type="text" name="reference" class="form-control" placeholder="No. invoice, dll"></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Catat</button></div>
</form></div></div></div>
<script>
function openAdjust(pid, vid, name, type){document.getElementById('adj_product').value=pid;document.getElementById('adj_variant').value=vid||'';document.getElementById('adj_type').value=type;document.getElementById('adj_name').value=name;document.getElementById('adjustTitle').textContent=type==='in'?'Barang Masuk':'Penyesuaian Stok';new bootstrap.Modal(document.getElementById('adjustModal')).show();}
</script>
@endsection

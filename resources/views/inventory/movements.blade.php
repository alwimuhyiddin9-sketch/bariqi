@extends('layouts.app')
@section('title', 'Riwayat Stok — Bariqi.co')
@section('content')
@php $fmtNum = fn($n) => number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header"><div><h1>Riwayat Stok</h1><p class="desc">Catatan seluruh perubahan stok barang</p></div></div>

<form method="GET" class="mb-3" style="max-width:400px">
    <select name="type" class="form-select" onchange="this.form.submit()">
        <option value="">Semua Jenis</option>
        <option value="in" @if(request('type')=='in')selected @endif>Barang Masuk</option>
        <option value="out" @if(request('type')=='out')selected @endif>Barang Keluar</option>
        <option value="adjustment" @if(request('type')=='adjustment')selected @endif>Penyesuaian</option>
        <option value="return" @if(request('type')=='return')selected @endif>Retur</option>
    </select>
</form>

<div class="card"><div class="card-body">
<table class="table table-hover mb-0">
<thead><tr><th>Waktu</th><th>Barang</th><th>Varian</th><th>Jenis</th><th class="text-end">Jumlah</th><th class="text-end">Sebelum</th><th class="text-end">Sesudah</th><th>Alasan</th><th>Referensi</th></tr></thead>
<tbody>
@foreach($movements as $m)
    @php $variantInfo = $m->variant_id && isset($variants[$m->variant_id]) ? $variants[$m->variant_id]->variant_info : null; @endphp
    <tr>
        <td class="small text-muted">{{ $m->movement_date->format('d/m/Y H:i') }}</td>
        <td class="fw-medium">{{ $m->product->name }}</td>
        <td class="small">{{ $variantInfo ?: '-' }}</td>
        <td>@php $colors=['in'=>'success','out'=>'danger','adjustment'=>'warning','return'=>'primary']; @endphp<span class="badge bg-{{ $colors[$m->type] ?? 'secondary' }}">{{ ['in'=>'Masuk','out'=>'Keluar','adjustment'=>'Adjust','return'=>'Retur'][$m->type] ?? $m->type }}</span></td>
        <td class="text-end fw-semibold" style="color:{{ $m->type=='out'?'#dc2626':'#1d4ed8' }}">{{$m->type=='out'?'−':'+'}}{{ $fmtNum($m->quantity) }}</td>
        <td class="text-end small text-muted">{{ $m->stock_before }}</td>
        <td class="text-end small">{{ $m->stock_after }}</td>
        <td class="small">{{ $m->reason }}</td>
        <td class="small text-muted" style="font-family:monospace">{{ $m->reference ?: '-' }}</td>
    </tr>
@endforeach
</tbody>
</table>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Laporan — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); $fmtNum = fn($n) => number_format($n ?? 0, 0, ',', '.'); $channelLabel=['shopee'=>'Shopee','tokopedia'=>'Tokopedia','tiktok'=>'TikTok Shop','instagram'=>'Instagram','whatsapp'=>'WhatsApp','kasir'=>'Kasir','lainnya'=>'Lainnya']; @endphp
<div class="page-header"><div><h1>Laporan Usaha</h1><p class="desc">Lihat dan unduh laporan dalam bentuk PDF</p></div><a href="{{ route('reports.pdf', $type) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-download"></i> Unduh PDF</a></div>

<form method="GET" class="d-flex gap-2 mb-3" style="max-width:800px">
<input type="hidden" name="type" value="{{ $type }}">
<select name="range" class="form-select" style="width:180px" onchange="this.form.submit()">
<option value="today" @if($preset=='today')selected @endif>Hari Ini</option>
<option value="week" @if($preset=='week')selected @endif>Minggu Ini</option>
<option value="month" @if($preset=='month')selected @endif>Bulan Ini</option>
<option value="year" @if($preset=='year')selected @endif>Tahun Ini</option>
<option value="custom" @if($preset=='custom')selected @endif>Rentang Khusus</option>
</select>
@if($preset=='custom')
<input type="date" name="from" class="form-control" value="{{ request('from') }}" onchange="this.form.submit()">
<input type="date" name="to" class="form-control" value="{{ request('to') }}" onchange="this.form.submit()">
@endif
</form>

<ul class="nav nav-tabs mb-3">
<li class="nav-item"><a class="nav-link {{ $type=='barang'?'active':'' }}" href="{{ route('reports.index', ['type'=>'barang']) }}">Barang</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='persediaan'?'active':'' }}" href="{{ route('reports.index', ['type'=>'persediaan']) }}">Persediaan</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='penjualan'?'active':'' }}" href="{{ route('reports.index', ['type'=>'penjualan']) }}">Penjualan</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='keuangan'?'active':'' }}" href="{{ route('reports.index', ['type'=>'keuangan']) }}">Keuangan</a></li>
</ul>

@if($type=='barang')
<div class="card"><div class="card-body">
<table class="table table-sm"><thead><tr><th>Nama</th><th>SKU</th><th>Kategori</th><th class="text-end">Modal</th><th class="text-end">Jual</th><th class="text-end">Stok</th><th>Status</th></tr></thead>
<tbody>@foreach($products as $p)<tr><td class="fw-medium">{{ $p->name }}</td><td style="font-family:monospace;font-size:12px">{{ $p->sku }}</td><td class="small">{{ $p->category->name }}</td><td class="text-end small">{{ $fmtRupiah($p->cost_price) }}</td><td class="text-end fw-semibold">{{ $fmtRupiah($p->sell_price) }}</td><td class="text-end">{{ $fmtNum($p->computed_stock) }}</td><td><span class="badge bg-{{ $p->status=='active'?'success':'secondary' }}">{{ $p->status=='active'?'Aktif':'Nonaktif' }}</span></td></tr>@endforeach</tbody>
</table>
</div></div>

@elseif($type=='persediaan')
<div class="stat-grid">
<div class="stat-card"><p class="title">Total Unit Stok</p><div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-boxes"></i></div><p class="value">{{ $fmtNum($totalStock) }}</p></div>
<div class="stat-card"><p class="title">Nilai Persediaan</p><div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-wallet"></i></div><p class="value" style="color:#15803d">{{ $fmtRupiah($totalValue) }}</p></div>
<div class="stat-card"><p class="title">Stok Menipis</p><div class="icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-exclamation-triangle"></i></div><p class="value" style="color:#d97706">{{ $fmtNum($lowCount) }}</p></div>
</div>
<div class="card"><div class="card-body">
<table class="table table-sm"><thead><tr><th>Nama</th><th>SKU</th><th>Varian</th><th class="text-end">Stok</th><th class="text-end">Min</th><th class="text-end">Nilai</th><th>Status</th></tr></thead>
<tbody>@foreach($rows as $r)<tr><td class="fw-medium">{{ $r['product']->name }}</td><td style="font-family:monospace;font-size:12px">{{ $r['variant']?->sku ?? $r['product']->sku }}</td><td class="small">{{ $r['variant']?->variant_info ?? '-' }}</td><td class="text-end">{{ $fmtNum($r['variant']?->stock ?? $r['product']->stock) }}</td><td class="text-end">{{ $r['min'] }}</td><td class="text-end">{{ $fmtRupiah($r['value']) }}</td><td><span class="badge bg-{{ $r['low']?'warning':'success' }}">{{ $r['low']?'Menipis':'Aman' }}</span></td></tr>@endforeach</tbody>
</table>
</div></div>

@elseif($type=='penjualan')
<div class="stat-grid">
<div class="stat-card"><p class="title">Total Omzet</p><div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-cart"></i></div><p class="value" style="color:#15803d">{{ $fmtRupiah($totalSales) }}</p></div>
<div class="stat-card"><p class="title">Jumlah Transaksi</p><div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-receipt"></i></div><p class="value">{{ $fmtNum($totalTrx) }}</p></div>
<div class="stat-card"><p class="title">Rata-rata/Transaksi</p><div class="icon" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-bar-chart"></i></div><p class="value">{{ $fmtRupiah($totalTrx?round($totalSales/$totalTrx):0) }}</p></div>
<div class="stat-card"><p class="title">Total Diskon</p><div class="icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-tag"></i></div><p class="value" style="color:#d97706">{{ $fmtRupiah($totalDiscount) }}</p></div>
</div>
<div class="row g-3">
<div class="col-lg-6"><div class="card"><div class="card-header"><h3>Penjualan per Saluran</h3></div><div class="card-body"><table class="table table-sm mb-0"><thead><tr><th>Saluran</th><th class="text-end">Transaksi</th><th class="text-end">Omzet</th></tr></thead><tbody>@foreach($byChannel as $ch=>$data)<tr><td><span class="badge bg-light text-primary">{{ $channelLabel[$ch] ?? $ch }}</span></td><td class="text-end">{{ $data['count'] }}</td><td class="text-end fw-semibold">{{ $fmtRupiah($data['total']) }}</td></tr>@endforeach</tbody></table></div></div></div>
<div class="col-lg-6"><div class="card"><div class="card-header"><h3>Barang Terlaris</h3></div><div class="card-body">@if(count($topProducts)>0)<table class="table table-sm mb-0"><thead><tr><th>Barang</th><th class="text-end">Terjual</th><th class="text-end">Omzet</th></tr></thead><tbody>@foreach($topProducts as $p)<tr><td class="fw-medium">{{ $p['name'] }}</td><td class="text-end">{{ $fmtNum($p['qty']) }}</td><td class="text-end">{{ $fmtRupiah($p['revenue']) }}</td></tr>@endforeach</tbody></table>@else<p class="text-muted text-center small py-4">Belum ada data</p>@endif</div></div></div>
</div>

@elseif($type=='keuangan')
<div class="card"><div class="card-header"><h3>Ringkasan Laba Rugi</h3></div><div class="card-body">
<div class="d-flex justify-content-between py-1"><span>Pendapatan Penjualan (Kotor)</span><span>{{ $fmtRupiah($pendapatan) }}</span></div>
<div class="d-flex justify-content-between py-1 text-danger"><span>Diskon</span><span>(−) {{ $fmtRupiah($diskon) }}</span></div>
<div class="d-flex justify-content-between py-1 text-success"><span>Biaya Tambahan</span><span>(+) {{ $fmtRupiah($biayaTambahan) }}</span></div>
<hr class="my-2">
<div class="d-flex justify-content-between py-1 fw-bold"><span>Penjualan Bersih</span><span>{{ $fmtRupiah($penjualanBersih) }}</span></div>
<div class="d-flex justify-content-between py-1 text-danger"><span>Harga Pokok Penjualan (HPP)</span><span>(−) {{ $fmtRupiah($hpp) }}</span></div>
<hr class="my-2">
<div class="d-flex justify-content-between py-1 fw-bold text-success"><span>Laba Kotor</span><span>{{ $fmtRupiah($labaKotor) }}</span></div>
<div class="d-flex justify-content-between py-1 text-danger"><span>Total Pengeluaran Operasional</span><span>(−) {{ $fmtRupiah($totalPengeluaran) }}</span></div>
<hr class="my-2">
<div class="d-flex justify-content-between py-2 fw-bold fs-5 text-success"><span>Laba Bersih</span><span>{{ $fmtRupiah($labaBersih) }}</span></div>
</div></div>
@endif
@endsection

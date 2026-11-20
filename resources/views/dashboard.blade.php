@extends('layouts.app')
@section('title', 'Beranda — Bariqi.co')
@section('content')
<div class="page-header">
    <div>
        <h1>Beranda</h1>
        <p class="desc">Ringkasan kondisi usaha Anda hari ini</p>
    </div>
</div>

@php
    $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
    $fmtNum = fn($n) => number_format($n ?? 0, 0, ',', '.');
    $channelLabel = ['shopee'=>'Shopee','tokopedia'=>'Tokopedia','tiktok'=>'TikTok Shop','instagram'=>'Instagram','whatsapp'=>'WhatsApp','kasir'=>'Kasir','lainnya'=>'Lainnya'];
@endphp

<div class="stat-grid">
    <div class="stat-card">
        <p class="title">Total Barang</p>
        <div class="icon" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-box"></i></div>
        <p class="value">{{ $fmtNum($totalProducts) }}</p>
        <p class="hint">{{ $fmtNum($totalVariants) }} varian</p>
    </div>
    <div class="stat-card">
        <p class="title">Total Stok</p>
        <div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-boxes"></i></div>
        <p class="value">{{ $fmtNum($totalStock) }}</p>
        <p class="hint">Seluruh unit persediaan</p>
    </div>
    <div class="stat-card">
        <p class="title">Nilai Persediaan</p>
        <div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-wallet"></i></div>
        <p class="value" style="color:#15803d">{{ $fmtRupiah($inventoryValue) }}</p>
        <p class="hint">Total nilai modal stok</p>
    </div>
    <div class="stat-card">
        <p class="title">Stok Menipis</p>
        <div class="icon" style="background:{{ count($lowStockItems)>0?'#fef3c7':'#f1f5f9'}};color:{{ count($lowStockItems)>0?'#d97706':'#475569'}}"><i class="bi bi-exclamation-triangle"></i></div>
        <p class="value" style="color:{{ count($lowStockItems)>0?'#d97706':'#0f172a'}}">{{ $fmtNum(count($lowStockItems)) }}</p>
        <p class="hint">Barang perlu direstok</p>
    </div>
    <div class="stat-card">
        <p class="title">Penjualan Hari Ini</p>
        <div class="icon" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-graph-up"></i></div>
        <p class="value">{{ $fmtRupiah($todayRevenue) }}</p>
        <p class="hint">Omzet semua saluran</p>
    </div>
    <div class="stat-card">
        <p class="title">Omzet Bulan Ini</p>
        <div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-cash-coin"></i></div>
        <p class="value" style="color:#15803d">{{ $fmtRupiah($monthRevenue) }}</p>
        <p class="hint">Total penjualan bulan berjalan</p>
    </div>
    <div class="stat-card">
        <p class="title">Estimasi Laba Kotor</p>
        <div class="icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-graph-up-arrow"></i></div>
        <p class="value" style="color:#15803d">{{ $fmtRupiah($monthProfit) }}</p>
        <p class="hint">Omzet dikurangi HPP</p>
    </div>
    <div class="stat-card">
        <p class="title">Total Varian</p>
        <div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-layers"></i></div>
        <p class="value">{{ $fmtNum($totalVariants) }}</p>
        <p class="hint">Varian warna & ukuran</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div><h3>Grafik Penjualan</h3><div class="desc">7 hari terakhir</div></div>
            </div>
            <div class="card-body">
                <canvas id="chart7" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><div><h3>Penjualan per Saluran</h3><div class="desc">Bulan ini</div></div></div>
            <div class="card-body">
                @if(count($byChannel)>0)
                    <canvas id="chartChannel" height="220"></canvas>
                @else
                    <p class="text-center text-muted small py-5">Belum ada penjualan bulan ini</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div><h3>Ringkasan Saluran Penjualan</h3><div class="desc">Bulan ini</div></div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border rounded-3 p-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary"><i class="bi bi-globe"></i></span>
                                <span class="fw-medium small">Online</span>
                            </div>
                            <p class="fw-bold mb-0 mt-2" style="font-size:18px">{{ $fmtRupiah($onlineTotal) }}</p>
                            <p class="text-muted small mb-0">{{ $fmtNum($onlineCount) }} transaksi</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success"><i class="bi bi-shop"></i></span>
                                <span class="fw-medium small">Offline (Kasir)</span>
                            </div>
                            <p class="fw-bold mb-0 mt-2" style="font-size:18px">{{ $fmtRupiah($offlineTotal) }}</p>
                            <p class="text-muted small mb-0">{{ $fmtNum($offlineCount) }} transaksi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><div><h3>Barang Terlaris</h3><div class="desc">12 bulan terakhir</div></div></div>
            <div class="card-body">
                @if(count($topProducts)>0)
                    @foreach($topProducts as $i=>$p)
                        <div class="d-flex align-items-center gap-2 py-2 {{ $loop->last?'':'border-bottom' }}">
                            <span class="badge bg-light text-primary">{{ $i+1 }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <p class="fw-medium small mb-0 text-truncate">{{ $p['name'] }}</p>
                                <p class="text-muted small mb-0">{{ $fmtNum($p['qty']) }} terjual</p>
                            </div>
                            <p class="fw-semibold small mb-0">{{ $fmtRupiah($p['revenue']) }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-muted small py-5">Belum ada data penjualan</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div><h3>Peringatan Stok Menipis</h3><div class="desc">{{ count($lowStockItems) }} barang perlu direstok</div></div>
                <a href="{{ route('inventory.index', ['status'=>'low']) }}" class="btn btn-sm btn-link text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body">
                @if(count($lowStockItems)>0)
                    @foreach(array_slice($lowStockItems, 0, 6) as $ls)
                        <div class="d-flex align-items-center gap-2 py-2 {{ $loop->last?'':'border-bottom' }}">
                            <span class="badge bg-warning-subtle text-warning"><i class="bi bi-exclamation-triangle"></i></span>
                            <div class="flex-grow-1 min-w-0">
                                <p class="fw-medium small mb-0 text-truncate">{{ $ls['name'] }}</p>
                                <p class="text-muted small mb-0 text-truncate">{{ $ls['sku'] }}@if($ls['variant_info']) · {{ $ls['variant_info'] }}@endif</p>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold mb-0 text-warning">{{ $ls['stock'] }}</p>
                                <p class="text-muted small mb-0">min. {{ $ls['min_stock'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-muted small py-5">Semua stok aman</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div><h3>Transaksi Terbaru</h3><div class="desc">5 transaksi terakhir</div></div>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body">
                @if(count($recentSales)>0)
                    @foreach($recentSales as $t)
                        <div class="d-flex align-items-center gap-2 py-2 {{ $loop->last?'':'border-bottom' }}">
                            @php $ch = $channelLabel[$t->channel] ?? $t->channel; @endphp
                            <span class="badge bg-primary text-white rounded-circle" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700">{{ strtoupper(substr($ch,0,1)) }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <p class="fw-medium small mb-0 text-truncate">{{ $t->invoice_number }}</p>
                                <p class="text-muted small mb-0 text-truncate">{{ $ch }} · {{ $t->order_date->format('d/m H:i') }}</p>
                            </div>
                            <div class="text-end">
                                <p class="fw-semibold small mb-0">{{ $fmtRupiah($t->total) }}</p>
                                <span class="badge bg-light">{{ $t->order_status }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-muted small py-5">Belum ada transaksi</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chart7'), {
    type: 'line',
    data: {
        labels: @json(collect($sales7Days)->pluck('label')),
        datasets: [{
            label: 'Omzet',
            data: @json(collect($sales7Days)->pluck('omzet')),
            borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,0.1)',
            fill: true, tension: 0.3, borderWidth: 2
        }]
    },
    options: { plugins:{ legend:{display:false} }, scales:{ y:{ ticks:{ callback:v=>'Rp '+(v/1000)+'rb' } } } }
});
@if(count($byChannel)>0)
new Chart(document.getElementById('chartChannel'), {
    type: 'doughnut',
    data: {
        labels: @json(collect($byChannel)->map(fn($c)=>$channelLabel[$c['channel']] ?? $c['channel'])),
        datasets: [{ data: @json(collect($byChannel)->pluck('total')), backgroundColor: ['#1d4ed8','#15803d','#d97706','#7c3aed','#0891b2','#dc2626','#64748b'] }]
    },
    options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } } }
});
@endif
</script>
@endsection

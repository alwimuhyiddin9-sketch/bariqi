<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Keuangan</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 18px; color: #1d4ed8; }
.header p { margin: 2px 0; font-size: 10px; color: #64748b; }
.title { font-size: 16px; font-weight: bold; margin: 16px 0; }
.table { width: 100%; }
.table .row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
.bold { font-weight: bold; font-size: 13px; margin: 6px 0; }
.success { color: #15803d; }
.danger { color: #dc2626; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $store?->name ?? 'Bariqi.co Store' }}</h1>
    <p>{{ $store?->address }}</p>
    <p>Telp: {{ $store?->phone }}</p>
</div>
<div class="title">LAPORAN KEUANGAN (LABA RUGI)</div>
<p>Periode: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
<p>Dibuat: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
<div class="table">
    <div class="row"><span>Pendapatan Penjualan</span><span>Rp {{ number_format($sales->where('order_status','!=','cancelled')->sum('subtotal'), 0, ',', '.') }}</span></div>
    <div class="row danger"><span>Diskon</span><span>(−) Rp {{ number_format($sales->where('order_status','!=','cancelled')->sum('discount'), 0, ',', '.') }}</span></div>
    <div class="row"><span>Biaya Tambahan</span><span>(+) Rp {{ number_format($sales->where('order_status','!=','cancelled')->sum('additional_fee'), 0, ',', '.') }}</span></div>
    @php $bersih = $sales->where('order_status','!=','cancelled')->sum('subtotal') - $sales->where('order_status','!=','cancelled')->sum('discount') + $sales->where('order_status','!=','cancelled')->sum('additional_fee'); @endphp
    <div class="row bold"><span>Penjualan Bersih</span><span>Rp {{ number_format($bersih, 0, ',', '.') }}</span></div>
    @php $hpp = $sales->where('order_status','!=','cancelled')->sum('cost_total'); @endphp
    <div class="row danger"><span>HPP</span><span>(−) Rp {{ number_format($hpp, 0, ',', '.') }}</span></div>
    <div class="row bold success"><span>Laba Kotor</span><span>Rp {{ number_format($bersih - $hpp, 0, ',', '.') }}</span></div>
    @php $exp = $expenses->sum('amount'); @endphp
    <div class="row danger"><span>Pengeluaran Operasional</span><span>(−) Rp {{ number_format($exp, 0, ',', '.') }}</span></div>
    <div class="row bold success" style="font-size:15px"><span>Laba Bersih</span><span>Rp {{ number_format(($bersih - $hpp) - $exp, 0, ',', '.') }}</span></div>
</div>
</body>
</html>

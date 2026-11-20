<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 18px; color: #1d4ed8; }
.header p { margin: 2px 0; font-size: 10px; color: #64748b; }
.title { font-size: 16px; font-weight: bold; margin: 16px 0; }
table { width: 100%; border-collapse: collapse; }
th { background: #f1f5f9; padding: 6px; text-align: left; font-size: 9px; }
td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
.text-end { text-align: right; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $store?->name ?? 'Bariqi.co Store' }}</h1>
    <p>{{ $store?->address }}</p>
    <p>Telp: {{ $store?->phone }}</p>
</div>
<div class="title">LAPORAN PENJUALAN</div>
<p>Periode: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
<p>Dibuat: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
@php $totalSales = $sales->where('order_status','!=','cancelled')->sum('total'); @endphp
<p><strong>Total Omzet: Rp {{ number_format($totalSales, 0, ',', '.') }}</strong> ({{ $sales->where('order_status','!=','cancelled')->count() }} transaksi)</p>
<table>
    <thead>
        <tr><th>No. Transaksi</th><th>Saluran</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr>
    </thead>
    <tbody>
        @foreach($sales as $s)
        <tr>
            <td>{{ $s->invoice_number }}</td>
            <td>{{ ['shopee'=>'Shopee','tokopedia'=>'Tokopedia','tiktok'=>'TikTok','instagram'=>'Instagram','whatsapp'=>'WhatsApp','kasir'=>'Kasir','lainnya'=>'Lainnya'][$s->channel] ?? $s->channel }}</td>
            <td>{{ $s->order_date->format('d/m/Y H:i') }}</td>
            <td class="text-end">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
            <td>{{ $s->order_status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>

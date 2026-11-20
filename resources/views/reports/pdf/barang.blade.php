<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Barang</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 18px; color: #1d4ed8; }
.header p { margin: 2px 0; font-size: 10px; color: #64748b; }
.title { font-size: 16px; font-weight: bold; margin: 16px 0; }
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
th { background: #f1f5f9; padding: 8px; text-align: left; font-size: 10px; border-bottom: 2px solid #e2e8f0; }
td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
.text-end { text-align: right; }
.footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $store?->name ?? 'Bariqi.co Store' }}</h1>
    <p>{{ $store?->address }}</p>
    <p>Telp: {{ $store?->phone }}</p>
</div>
<div class="title">LAPORAN DATA BARANG</div>
<p>Periode: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
<p>Dibuat: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
<table>
    <thead>
        <tr><th>Nama</th><th>SKU</th><th>Kategori</th><th class="text-end">Modal</th><th class="text-end">Jual</th><th class="text-end">Stok</th></tr>
    </thead>
    <tbody>
        @foreach($products as $p)
        <tr>
            <td>{{ $p->name }}</td>
            <td>{{ $p->sku }}</td>
            <td>{{ $p->category->name }}</td>
            <td class="text-end">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($p->computed_stock, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">Bariqi.co — Laporan dibuat secara otomatis pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
</body>
</html>

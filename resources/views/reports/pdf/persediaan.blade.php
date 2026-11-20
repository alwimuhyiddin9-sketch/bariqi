<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Persediaan</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 18px; color: #1d4ed8; }
.header p { margin: 2px 0; font-size: 10px; color: #64748b; }
.title { font-size: 16px; font-weight: bold; margin: 16px 0; }
table { width: 100%; border-collapse: collapse; }
th { background: #f1f5f9; padding: 6px; text-align: left; font-size: 9px; border-bottom: 2px solid #e2e8f0; }
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
<div class="title">LAPORAN PERSEDIAAN</div>
<p>Periode: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</p>
<p>Dibuat: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
<table>
    <thead>
        <tr><th>Nama</th><th>SKU</th><th>Varian</th><th class="text-end">Stok</th><th class="text-end">Nilai</th></tr>
    </thead>
    <tbody>
        @foreach($products as $p)
            @if($p->has_variants)
                @foreach($p->variants as $v)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $v->sku }}</td>
                    <td>{{ collect([$v->color,$v->size])->filter()->implode(' / ') ?: '-' }}</td>
                    <td class="text-end">{{ number_format($v->stock, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($v->stock * $v->cost_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->sku }}</td>
                    <td>-</td>
                    <td class="text-end">{{ number_format($p->stock, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($p->stock * $p->cost_price, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
</body>
</html>

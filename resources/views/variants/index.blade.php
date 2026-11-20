@extends('layouts.app')
@section('title', 'Varian Barang — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header"><div><h1>Varian Barang</h1><p class="desc">Kelola varian warna dan ukuran barang</p></div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#varModal" onclick="resetVar()"><i class="bi bi-plus-lg"></i> Tambah Varian</button></div>

<div class="card"><div class="card-body">
<table class="table table-hover mb-0">
<thead><tr><th>SKU</th><th>Barang</th><th>Warna</th><th>Ukuran</th><th class="text-end">Modal</th><th class="text-end">Jual</th><th class="text-end">Stok</th><th class="text-end">Aksi</th></tr></thead>
<tbody>
@foreach($variants as $v)
<tr>
<td style="font-family:monospace;font-size:12px">{{ $v->sku }}</td>
<td class="fw-medium">{{ $v->product->name }}</td>
<td>{{ $v->color ?: '-' }}</td>
<td>{{ $v->size ?: '-' }}</td>
<td class="text-end small">{{ $fmtRupiah($v->cost_price) }}</td>
<td class="text-end fw-semibold text-primary">{{ $fmtRupiah($v->sell_price) }}</td>
<td class="text-end">@if($v->stock==0)<span class="badge bg-danger">{{ $v->stock }}</span>@elseif($v->stock<=5)<span class="badge bg-warning">{{ $v->stock }}</span>@else<span class="badge bg-success">{{ $v->stock }}</span>@endif</td>
<td class="text-end"><button class="btn btn-sm btn-outline-primary" onclick='editVar({{ $v }})'><i class="bi bi-pencil"></i></button>
<form method="POST" action="{{ route('variants.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Hapus varian ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
</tr>
@endforeach
</tbody>
</table>
</div></div>

<div class="modal fade" id="varModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form id="varForm" method="POST" action="{{ route('variants.store') }}">@csrf
<div class="modal-header"><h5 class="modal-title" id="varTitle">Tambah Varian</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Barang Induk</label><select name="product_id" id="var_product" class="form-select" required>@foreach(\App\Models\Product::orderBy('name')->get() as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label">SKU Varian</label><input type="text" name="sku" class="form-control" required></div>
<div class="row g-2 mb-3"><div class="col-6"><label class="form-label">Warna</label><input type="text" name="color" class="form-control" placeholder="Hitam"></div><div class="col-6"><label class="form-label">Ukuran</label><input type="text" name="size" class="form-control" placeholder="M"></div></div>
<div class="row g-2 mb-3"><div class="col-6"><label class="form-label">Harga Modal (Rp)</label><input type="number" name="cost_price" class="form-control" value="0"></div><div class="col-6"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="sell_price" class="form-control" value="0"></div></div>
<div class="mb-3"><label class="form-label">Stok Awal</label><input type="number" name="stock" class="form-control" value="0"></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div></div>
<script>
function resetVar(){document.getElementById('varTitle').textContent='Tambah Varian';document.getElementById('varForm').action='{{ route("variants.store") }}';document.getElementById('varForm').reset();}
function editVar(v){const f=document.getElementById('varForm');document.getElementById('varTitle').textContent='Ubah Varian';f.action='{{ route("variants.update",":id") }}'.replace(':id',v.id);f.method='POST';f.innerHTML=f.innerHTML.replace('@csrf','@csrf<input type="hidden" name="_method" value="PUT">');f.querySelector('[name=sku]').value=v.sku;f.querySelector('[name=color]').value=v.color||'';f.querySelector('[name=size]').value=v.size||'';f.querySelector('[name=cost_price]').value=v.cost_price;f.querySelector('[name=sell_price]').value=v.sell_price;new bootstrap.Modal(document.getElementById('varModal')).show();}
</script>
@endsection

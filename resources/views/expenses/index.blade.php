@extends('layouts.app')
@section('title', 'Pengeluaran — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); $catLabel=['listrik'=>'Listrik','sewa'=>'Sewa','transportasi'=>'Transportasi','kemasan'=>'Kemasan','perlengkapan'=>'Perlengkapan Toko','lainnya'=>'Lainnya']; @endphp
<div class="page-header"><div><h1>Pengeluaran</h1><p class="desc">Catat pengeluaran operasional usaha</p></div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expModal" onclick="resetExp()"><i class="bi bi-plus-lg"></i> Tambah Pengeluaran</button></div>

<div class="stat-grid">
<div class="stat-card"><p class="title">Total Pengeluaran</p><div class="icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-graph-down"></i></div><p class="value" style="color:#dc2626">{{ $fmtRupiah($total) }}</p></div>
@foreach(array_slice(array_keys($catLabel),0,3) as $cat)<div class="stat-card"><p class="title">{{ $catLabel[$cat] }}</p><div class="icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-wallet"></i></div><p class="value">{{ $fmtRupiah($byCategory[$cat] ?? 0) }}</p></div>@endforeach
</div>

<form method="GET" class="d-flex gap-2 mb-3" style="max-width:600px">
<input type="text" name="search" class="form-control" placeholder="Cari pengeluaran..." value="{{ request('search') }}">
<select name="category" class="form-select" style="width:180px"><option value="">Semua Kategori</option>@foreach($catLabel as $v=>$l)<option value="{{ $v }}" @if(request('category')==$v)selected @endif>{{ $l }}</option>@endforeach</select>
<button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card"><div class="card-body">
<table class="table table-hover mb-0">
<thead><tr><th>Tanggal</th><th>Nama</th><th>Kategori</th><th class="text-end">Jumlah</th><th class="text-end">Aksi</th></tr></thead>
<tbody>
@foreach($expenses as $e)
<tr>
<td class="small">{{ $e->date->format('d/m/Y') }}</td>
<td class="fw-medium">{{ $e->name }}</td>
<td><span class="badge bg-light">{{ $catLabel[$e->category] ?? $e->category }}</span></td>
<td class="text-end fw-semibold text-danger">{{ $fmtRupiah($e->amount) }}</td>
<td class="text-end"><button class="btn btn-sm btn-outline-primary" onclick='editExp({{ $e }})'><i class="bi bi-pencil"></i></button>
<form method="POST" action="{{ route('expenses.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
</tr>
@endforeach
</tbody>
</table>
</div></div>

<div class="modal fade" id="expModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form id="expForm" method="POST" action="{{ route('expenses.store') }}">@csrf
<div class="modal-header"><h5 class="modal-title" id="expTitle">Tambah Pengeluaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">Nama Pengeluaran</label><input type="text" name="name" class="form-control" required></div>
<div class="row g-2 mb-3"><div class="col-6"><label class="form-label">Kategori</label><select name="category" class="form-select">@foreach($catLabel as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div><div class="col-6"><label class="form-label">Jumlah (Rp)</label><input type="number" name="amount" class="form-control" required min="1"></div></div>
<div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"></div>
<div><label class="form-label">Catatan (opsional)</label><textarea name="note" class="form-control" rows="2"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div></div>
<script>
function resetExp(){document.getElementById('expTitle').textContent='Tambah Pengeluaran';document.getElementById('expForm').action='{{ route("expenses.store") }}';document.getElementById('expForm').reset();}
function editExp(e){const f=document.getElementById('expForm');document.getElementById('expTitle').textContent='Ubah Pengeluaran';f.action='{{ route("expenses.update",":id") }}'.replace(':id',e.id);f.method='POST';f.innerHTML=f.innerHTML.replace('@csrf','@csrf<input type="hidden" name="_method" value="PUT">');f.querySelector('[name=name]').value=e.name;f.querySelector('[name=category]').value=e.category;f.querySelector('[name=amount]').value=e.amount;f.querySelector('[name=date]').value=e.date.split(' ')[0];f.querySelector('[name=note]').value=e.note||'';new bootstrap.Modal(document.getElementById('expModal')).show();}
</script>
@endsection

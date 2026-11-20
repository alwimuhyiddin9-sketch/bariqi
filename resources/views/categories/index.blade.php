@extends('layouts.app')
@section('title', 'Kategori Barang — Bariqi.co')
@section('content')
@php $fmtNum = fn($n) => number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header">
    <div><h1>Kategori Barang</h1><p class="desc">Kelompokkan barang berdasarkan kategori</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal" onclick="resetCat()"><i class="bi bi-plus-lg"></i> Tambah Kategori</button>
</div>

<form method="GET" class="mb-3" style="max-width:400px">
    <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="{{ request('search') }}">
</form>

<div class="row g-3">
@foreach($categories as $i=>$c)
    @php $colors = ['#1d4ed8','#15803d','#d97706','#7c3aed','#0891b2','#dc2626','#db2777','#4f46e5']; $col = $colors[$i % count($colors)]; @endphp
    <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card h-100" style="border-top:3px solid {{ $col }}">
            <div class="card-body">
                <div class="d-flex">
                    <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold me-3" style="width:44px;height:44px;background:{{ $col }}14;color:{{ $col }}">{{ strtoupper(substr($c->name,0,1)) }}</div>
                    <div class="flex-grow-1">
                        <p class="fw-semibold mb-0">{{ $c->name }}</p>
                        <a href="{{ route('products.index', ['category_id'=>$c->id]) }}" class="small text-muted text-decoration-none">{{ $fmtNum($c->products_count) }} barang &rarr;</a>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick='editCat({{ $c }})'><i class="bi bi-pencil"></i></button>
                </div>
                <p class="small text-muted mt-3 mb-0">{{ $c->description ?: 'Tanpa deskripsi' }}</p>
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="catForm" method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title" id="catTitle">Tambah Kategori</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama Kategori</label><input type="text" name="name" class="form-control" required></div>
                    <div><label class="form-label">Deskripsi (opsional)</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
<script>
function resetCat(){document.getElementById('catTitle').textContent='Tambah Kategori';document.getElementById('catForm').action='{{ route("categories.store") }}';document.getElementById('catForm').reset();}
function editCat(c){const f=document.getElementById('catForm');document.getElementById('catTitle').textContent='Ubah Kategori';f.action='{{ route("categories.update",":id") }}'.replace(':id',c.id);f.method='POST';f.innerHTML=f.innerHTML.replace('@csrf','@csrf<input type="hidden" name="_method" value="PUT">');f.querySelector('[name=name]').value=c.name;f.querySelector('[name=description]').value=c.description||'';new bootstrap.Modal(document.getElementById('catModal')).show();}
</script>
@endsection

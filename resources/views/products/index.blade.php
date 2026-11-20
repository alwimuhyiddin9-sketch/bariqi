@extends('layouts.app')
@section('title', 'Data Barang — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header">
    <div><h1>Data Barang</h1><p class="desc">Kelola seluruh data barang yang dijual</p></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()"><i class="bi bi-plus-lg"></i> Tambah Barang</button>
</div>

<form method="GET" class="d-flex gap-2 mb-3" style="max-width:600px">
    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode barang..." value="{{ request('search') }}">
    <select name="category_id" class="form-select" style="width:180px">
        <option value="">Semua Kategori</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @if(request('category_id')==$c->id)selected @endif>{{ $c->name }}</option>@endforeach
    </select>
    <select name="status" class="form-select" style="width:140px">
        <option value="">Semua Status</option>
        <option value="active" @if(request('status')=='active')selected @endif>Aktif</option>
        <option value="inactive" @if(request('status')=='inactive')selected @endif>Nonaktif</option>
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card">
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead><tr><th>Foto</th><th>Nama & Kode</th><th>Kategori</th><th class="text-end">Modal</th><th class="text-end">Jual</th><th class="text-end">Stok</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach($products as $p)
                @php $stock = $p->computed_stock; @endphp
                <tr>
                    <td>
                        @if($p->image)
                            <img src="{{ $p->image }}" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb">
                        @else
                            <div style="width:44px;height:44px;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center"><i class="bi bi-image text-muted" style="opacity:0.4"></i></div>
                        @endif
                    </td>
                    <td>
                        <p class="fw-medium mb-0">{{ $p->name }}</p>
                        <p class="text-muted small mb-0" style="font-family:monospace">{{ $p->sku }}</p>
                        @if($p->has_variants)<span class="badge bg-light text-primary"><i class="bi bi-layers"></i> {{ $p->variants->count() }} varian</span>@endif
                    </td>
                    <td class="small">{{ $p->category->name }}</td>
                    <td class="text-end small">{{ $fmtRupiah($p->cost_price) }}</td>
                    <td class="text-end fw-semibold text-primary">{{ $fmtRupiah($p->sell_price) }}</td>
                    <td class="text-end">
                        @if($stock==0)<span class="badge bg-danger">{{ $stock }}</span>
                        @elseif($stock<=$p->min_stock)<span class="badge bg-warning">{{ $stock }}</span>
                        @else<span class="badge bg-success">{{ $stock }}</span>@endif
                    </td>
                    <td>@if($p->status=='active')<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick='editProduct(@json($p->load("variants"))')'><i class="bi bi-pencil"></i></button>
                        <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Nonaktifkan barang ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit Barang -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Barang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="productForm" method="POST" action="{{ route('products.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Nama Barang</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Kode Barang / SKU</label><input type="text" name="sku" class="form-control" placeholder="Kosongkan untuk otomatis"></div>
                        <div class="col-md-6"><label class="form-label">Kategori</label><select name="category_id" class="form-select" required>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                        <div class="col-12"><label class="form-label">URL Foto (opsional)</label><input type="text" name="image" class="form-control" placeholder="https://..."></div>
                        <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="has_variants" id="hasVariants"><label class="form-check-label" for="hasVariants">Barang memiliki varian (warna/ukuran)</label></div></div>
                        <div class="col-md-4"><label class="form-label">Harga Modal (Rp)</label><input type="number" name="cost_price" class="form-control" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="sell_price" class="form-control" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Stok Awal</label><input type="number" name="stock" class="form-control" value="0"></div>
                        <div class="col-md-6"><label class="form-label">Batas Min. Stok</label><input type="number" name="min_stock" class="form-control" value="5"></div>
                        <div class="col-md-6"><label class="form-label">Satuan</label><input type="text" name="unit" class="form-control" value="pcs"></div>
                        <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select></div>
                        <div class="col-12"><label class="form-label">Deskripsi (opsional)</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Barang';
    document.getElementById('productForm').action = '{{ route('products.store') }}';
    document.getElementById('productForm').reset();
    document.getElementById('productForm').removeAttribute('enctype');
}
function editProduct(p) {
    const form = document.getElementById('productForm');
    document.getElementById('modalTitle').textContent = 'Ubah Barang';
    form.action = '{{ route("products.update", ":id") }}'.replace(':id', p.id);
    form.method = 'POST';
    form.innerHTML = form.innerHTML.replace('@csrf', '@csrf<input type="hidden" name="_method" value="PATCH">');
    form.querySelector('[name=name]').value = p.name;
    form.querySelector('[name=sku]').value = p.sku;
    form.querySelector('[name=category_id]').value = p.category_id;
    form.querySelector('[name=image]').value = p.image || '';
    form.querySelector('[name=cost_price]').value = p.cost_price;
    form.querySelector('[name=sell_price]').value = p.sell_price;
    form.querySelector('[name=min_stock]').value = p.min_stock;
    form.querySelector('[name=unit]').value = p.unit;
    form.querySelector('[name=status]').value = p.status;
    form.querySelector('[name=description]').value = p.description || '';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
@endsection

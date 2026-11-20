@extends('layouts.app')
@section('title', 'Pengaturan — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); @endphp
<div class="page-header"><div><h1>Pengaturan</h1><p class="desc">Kelola profil toko, administrator, dan preferensi</p></div></div>

<ul class="nav nav-tabs mb-3">
<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#toko">Toko</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#akun">Akun</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pref">Preferensi</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sistem">Sistem</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="toko">
<form method="POST" action="{{ route('settings.update') }}">@csrf @method('PATCH')
<div class="card"><div class="card-header"><h3>Profil Toko</h3></div><div class="card-body">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama Toko</label><input type="text" name="store[name]" class="form-control" value="{{ $store?->name }}"></div>
<div class="col-md-6"><label class="form-label">Nomor Telepon</label><input type="text" name="store[phone]" class="form-control" value="{{ $store?->phone }}"></div>
<div class="col-12"><label class="form-label">Alamat</label><textarea name="store[address]" class="form-control" rows="2">{{ $store?->address }}</textarea></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="store[email]" class="form-control" value="{{ $store?->email }}"></div>
<div class="col-md-6"><label class="form-label">URL Logo (opsional)</label><input type="text" name="store[logo]" class="form-control" value="{{ $store?->logo }}"></div>
</div>
</div><div class="card-footer"><button class="btn btn-primary">Simpan Profil Toko</button></div></div>
</form>
</div>

<div class="tab-pane fade" id="akun">
<form method="POST" action="{{ route('settings.update') }}">@csrf @method('PATCH')
<div class="card"><div class="card-header"><h3>Profil Administrator</h3></div><div class="card-body">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama Lengkap</label><input type="text" name="admin[name]" class="form-control" value="{{ $admin?->name }}"></div>
<div class="col-md-6"><label class="form-label">Nama Pengguna</label><input type="text" name="admin[username]" class="form-control" value="{{ $admin?->username }}"></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="admin[email]" class="form-control" value="{{ $admin?->email }}"></div>
<div class="col-md-6"><label class="form-label">Nomor Telepon</label><input type="text" name="admin[phone]" class="form-control" value="{{ $admin?->phone }}"></div>
<div class="col-12"><label class="form-label">Kata Sandi Baru</label><input type="password" name="admin[password]" class="form-control" placeholder="Kosongkan jika tidak diubah"><div class="form-text"><i class="bi bi-shield-check"></i> Kata sandi disimpan dalam bentuk hash. Disarankan minimal 8 karakter.</div></div>
</div>
</div><div class="card-footer"><button class="btn btn-primary">Simpan Profil Administrator</button></div></div>
</form>
</div>

<div class="tab-pane fade" id="pref">
<form method="POST" action="{{ route('settings.update') }}">@csrf @method('PATCH')
<div class="card"><div class="card-header"><h3>Preferensi Aplikasi</h3></div><div class="card-body">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Tema Tampilan</label><select name="app[theme]" class="form-select"><option value="light" @if($app?->theme=='light')selected @endif>Terang</option><option value="dark" @if($app?->theme=='dark')selected @endif>Gelap</option></select></div>
<div class="col-md-6"><label class="form-label">Format Tanggal</label><select name="app[date_format]" class="form-select"><option value="DD/MM/YYYY" @if($app?->date_format=='DD/MM/YYYY')selected @endif>DD/MM/YYYY</option><option value="MM/DD/YYYY" @if($app?->date_format=='MM/DD/YYYY')selected @endif>MM/DD/YYYY</option></select></div>
<div class="col-md-6"><label class="form-label">Batas Minimum Stok</label><input type="number" name="app[min_stock_limit]" class="form-control" value="{{ $app?->min_stock_limit ?? 5 }}"></div>
<div class="col-md-6"><label class="form-label">Awalan Nomor Transaksi</label><input type="text" name="app[trx_prefix]" class="form-control" value="{{ $app?->trx_prefix ?? 'TRX' }}"></div>
<div class="col-12"><label class="form-label">Catatan Kaki Struk</label><input type="text" name="app[footer_note]" class="form-control" value="{{ $app?->footer_note }}"></div>
</div>
</div><div class="card-footer"><button class="btn btn-primary">Simpan Preferensi</button></div></div>
</form>
</div>

<div class="tab-pane fade" id="sistem">
<div class="card"><div class="card-header"><h3>Tentang Aplikasi</h3></div><div class="card-body">
<p class="fw-bold fs-5">Bariqi.co</p><p class="text-muted">Sistem Manajemen Data Barang, Persediaan & Penjualan</p>
<div class="row g-2 mt-2">
<div class="col-md-4"><div class="border rounded p-2"><p class="text-muted small mb-0">Versi</p><p class="fw-medium mb-0">1.0.0</p></div></div>
<div class="col-md-4"><div class="border rounded p-2"><p class="text-muted small mb-0">Bahasa</p><p class="fw-medium mb-0">Bahasa Indonesia</p></div></div>
<div class="col-md-4"><div class="border rounded p-2"><p class="text-muted small mb-0">Mata Uang</p><p class="fw-medium mb-0">Rupiah (IDR)</p></div></div>
</div>
</div></div>
</div>
</div>
@endsection

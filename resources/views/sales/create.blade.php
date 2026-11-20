@extends('layouts.app')
@section('title', 'Catat Pesanan — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); @endphp

<div class="page-header">
    <div>
        <h1>Catat Pesanan Online</h1>
        <p class="desc">Buat pesanan baru dari saluran online</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="{{ route('sales.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h5>Pilih Barang</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="prodSearch" class="form-control" placeholder="Cari barang untuk ditambahkan..." oninput="filterProducts()">
                    </div>
                    <div id="prodList" style="max-height:400px;overflow-y:auto">
                        @foreach($products as $p)
                            <div class="border rounded p-2 mb-2 prod-item" data-name="{{ strtolower($p->name.' '.$p->sku) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="fw-medium mb-0">{{ $p->name }}</p>
                                        <p class="text-muted small mb-0" style="font-family:monospace">{{ $p->sku }} · {{ $fmtRupiah($p->sell_price) }}</p>
                                    </div>
                                    @if(!$p->has_variants)
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addItem({{ $p->id }}, null, {{ json_encode($p->name) }}, {{ $p->sell_price }})"><i class="bi bi-plus-lg"></i></button>
                                    @endif
                                </div>
                                @if($p->has_variants)
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @foreach($p->variants as $v)
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItem({{ $p->id }}, {{ $v->id }}, {{ json_encode($p->name.' ('.collect([$v->color,$v->size])->filter()->implode('/').')') }}, {{ $v->sell_price }})">
                                                {{ collect([$v->color,$v->size])->filter()->implode('/') }} · {{ $fmtRupiah($v->sell_price) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>Keranjang</h5>
                    <span id="cartBadge" class="badge bg-primary" style="display:none">0 item</span>
                </div>
                <div class="card-body">
                    <div id="cartList">
                        <p class="text-muted text-center py-3">Belum ada barang dipilih.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5>Detail Pesanan</h5></div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small">Saluran</label>
                        <select name="channel" class="form-select" required>
                            <option value="shopee">Shopee</option>
                            <option value="tokopedia">Tokopedia</option>
                            <option value="tiktok">TikTok Shop</option>
                            <option value="instagram">Instagram</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">No. Pesanan Marketplace (opsional)</label>
                        <input type="text" name="external_order" class="form-control" placeholder="SHP-123456">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nama Pelanggan (opsional)</label>
                        <input type="text" name="customer_name" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Tanggal Pesanan</label>
                        <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Status Bayar</label>
                            <select name="payment_status" class="form-select"><option value="unpaid">Belum Dibayar</option><option value="paid">Lunas</option></select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Status Pesanan</label>
                            <select name="order_status" class="form-select"><option value="pending">Menunggu</option><option value="processing">Diproses</option><option value="shipped">Dikirim</option><option value="completed">Selesai</option></select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small">Diskon (Rp)</label><input type="number" name="discount" class="form-control" value="0" min="0"></div>
                        <div class="col-6"><label class="form-label small">Biaya Tambahan</label><input type="number" name="additional_fee" class="form-control" value="0" min="0"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Catatan (opsional)</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="bg-light rounded p-2 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">Subtotal</span>
                            <span id="totalDisplay" class="fw-bold">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg"></i> Simpan Pesanan</button>
                </div>
            </div>
        </div>
    </div>
    <div id="cartContainer"></div>
</form>
@endsection

@section('scripts')
<script>
let cart=[], itemSeq=0;
function addItem(pid, vid, name, price){
    cart.push({id:++itemSeq, pid, vid, name, price, qty:1});
    renderCart();
}
function changeQty(id, delta){
    let i=cart.findIndex(x=>x.id==id);
    if(i<0) return;
    cart[i].qty+=delta;
    if(cart[i].qty<1) cart=cart.filter(x=>x.id!=id);
    renderCart();
}
function removeItem(id){
    cart=cart.filter(x=>x.id!=id);
    renderCart();
}
function fmt(n){return 'Rp '+n.toLocaleString('id-ID');}
function renderCart(){
    const list=document.getElementById('cartList');
    const cont=document.getElementById('cartContainer');
    const badge=document.getElementById('cartBadge');
    cont.innerHTML='';
    if(cart.length===0){
        list.innerHTML='<p class="text-muted text-center py-3">Belum ada barang dipilih.</p>';
        document.getElementById('totalDisplay').textContent='Rp 0';
        badge.style.display='none';
        return;
    }
    badge.style.display='inline-block';
    badge.textContent=cart.length+' item';
    list.innerHTML=cart.map(c=>`
        <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2">
            <div class="flex-grow-1">
                <p class="small fw-medium mb-0">${c.name}</p>
                <p class="text-muted small mb-0">${fmt(c.price)} / pcs</p>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" onclick="changeQty(${c.id},-1)" class="btn btn-outline-secondary btn-sm py-0 px-2">−</button>
                <span class="small fw-medium px-2">${c.qty}</span>
                <button type="button" onclick="changeQty(${c.id},1)" class="btn btn-outline-secondary btn-sm py-0 px-2">+</button>
            </div>
            <p class="small fw-semibold mb-0" style="width:80px;text-align:right">${fmt(c.price*c.qty)}</p>
            <button type="button" onclick="removeItem(${c.id})" class="btn btn-link text-danger p-0"><i class="bi bi-x"></i></button>
        </div>
    `).join('');
    let total=cart.reduce((s,c)=>s+c.price*c.qty,0);
    document.getElementById('totalDisplay').textContent=fmt(total);
    cart.forEach(c=>{
        const i=document.createElement('input');i.type='hidden';i.name='items['+c.id+'][product_id]';i.value=c.pid;cont.appendChild(i);
        const v=document.createElement('input');v.type='hidden';v.name='items['+c.id+'][variant_id]';v.value=c.vid||'';cont.appendChild(v);
        const q=document.createElement('input');q.type='hidden';q.name='items['+c.id+'][quantity]';q.value=c.qty;cont.appendChild(q);
    });
}
function filterProducts(){
    const q=document.getElementById('prodSearch').value.toLowerCase();
    document.querySelectorAll('.prod-item').forEach(el=>{
        el.style.display=el.dataset.name.includes(q)?'':'none';
    });
}
</script>
@endsection
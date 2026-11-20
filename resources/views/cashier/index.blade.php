@extends('layouts.app')
@section('title', 'Kasir — Bariqi.co')
@section('content')
@php $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.'); @endphp

<div class="page-header">
    <div><h1>Kasir</h1><p class="desc">Layani pembelian langsung di toko</p></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5>Pilih Barang</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="prodSearch" class="form-control" placeholder="Cari nama atau kode barang..." oninput="filterProducts()">
                </div>
                <div id="prodGrid" class="row g-2" style="max-height:500px;overflow-y:auto">
                    @foreach($products as $p)
                        @php $stock = $p->computed_stock; @endphp
                        <div class="col-6 col-md-4 prod-item" data-name="{{ strtolower($p->name.' '.$p->sku) }}">
                            <div class="card h-100" style="border:1px solid #e5e7eb">
                                <div style="height:90px;background:#f8fafc;display:flex;align-items:center;justify-content:center;position:relative">
                                    @if($p->image)
                                        <img src="{{ $p->image }}" style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        <i class="bi bi-image" style="opacity:0.3;font-size:24px;color:#94a3b8"></i>
                                    @endif
                                    <span class="badge @if($stock<=0)bg-danger@elseif($stock<=5)bg-warning@else bg-light text-dark @endif" style="position:absolute;top:4px;right:4px;font-size:10px">@if($stock<=0)Habis@else Stok {{ $stock }}@endif</span>
                                </div>
                                <div class="p-2">
                                    <p class="small fw-medium mb-0 text-truncate">{{ $p->name }}</p>
                                    <p class="text-muted mb-0" style="font-size:10px;font-family:monospace">{{ $p->sku }}</p>
                                    @if(!$p->has_variants)
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="fw-bold text-primary small">{{ $fmtRupiah($p->sell_price) }}</span>
                                            <button class="btn btn-sm btn-primary py-0 px-2" @if($stock<=0)disabled @endif onclick="addToCart({{ $p->id }}, null, {{ json_encode($p->name) }}, {{ $p->sell_price }}, {{ $stock }})"><i class="bi bi-plus"></i></button>
                                        </div>
                                    @else
                                        <div class="dropdown mt-1">
                                            <button class="btn btn-sm btn-primary w-100 py-0" style="font-size:11px" @if($stock<=0)disabled @endif data-bs-toggle="dropdown">Pilih Varian</button>
                                            <ul class="dropdown-menu">
                                                @foreach($p->variants as $v)
                                                    <li><button type="button" class="dropdown-item small" onclick="addToCart({{ $p->id }}, {{ $v->id }}, {{ json_encode($p->name.' ('.collect([$v->color,$v->size])->filter()->implode('/').')') }}, {{ $v->sell_price }}, {{ $v->stock }})">{{ collect([$v->color,$v->size])->filter()->implode('/') }} · {{ $fmtRupiah($v->sell_price) }} ({{ $v->stock }})</button></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card" style="position:sticky;top:72px">
            <div class="card-header d-flex justify-content-between">
                <h5>Keranjang</h5>
                <button id="clearBtn" class="btn btn-sm btn-link text-danger text-decoration-none" style="display:none" onclick="clearCart()">Kosongkan</button>
            </div>
            <div class="card-body">
                <div id="cartList" style="max-height:280px;overflow-y:auto">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-cart" style="font-size:32px;opacity:0.3"></i>
                        <p class="small mt-2">Keranjang masih kosong</p>
                        <p class="small text-muted">Pilih barang di sebelah kiri</p>
                    </div>
                </div>
                <div id="cartSummary" class="mt-3" style="display:none">
                    <div class="rounded p-3" style="background:#f8fafc">
                        <div class="d-flex justify-content-between mb-1"><span class="text-muted small">Subtotal</span><span id="subtotalDisplay" class="small fw-medium">Rp 0</span></div>
                        <div class="d-flex justify-content-between align-items-center mb-2"><span class="text-muted small">Diskon (Rp)</span><input type="number" id="discountInput" class="form-control form-control-sm text-end" style="width:100px" value="0" min="0" oninput="updateTotal()"></div>
                        <div class="d-flex justify-content-between fw-bold"><span>Total</span><span id="totalDisplay" class="text-primary">Rp 0</span></div>
                    </div>
                    <button class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#payModal" onclick="openPay()"><i class="bi bi-credit-card"></i> Proses Pembayaran · <span id="payBtnTotal">Rp 0</span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="checkoutForm" method="POST" action="{{ route('cashier.checkout') }}">
    @csrf
    <div id="itemsInput"></div>
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Proses Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small">Ringkasan</label><div id="paySummary" class="border rounded p-2 small" style="max-height:120px;overflow-y:auto"></div></div>
                    <div class="mb-3"><label class="form-label small">Total Bayar</label><input type="text" class="form-control fw-bold" id="payTotal" readonly></div>
                    <div class="mb-3">
                        <label class="form-label small">Metode Pembayaran</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary flex-grow-1 pay-method active" data-method="cash" onclick="selectMethod('cash')">Tunai</button>
                            <button type="button" class="btn btn-outline-primary flex-grow-1 pay-method" data-method="transfer" onclick="selectMethod('transfer')">Transfer</button>
                            <button type="button" class="btn btn-outline-primary flex-grow-1 pay-method" data-method="qris" onclick="selectMethod('qris')">QRIS</button>
                        </div>
                        <input type="hidden" name="payment_method" id="methodInput" value="cash">
                    </div>
                    <div id="cashSection" class="mb-3">
                        <label class="form-label small">Uang Diterima (Rp)</label>
                        <input type="text" id="amountPaid" class="form-control form-control-lg fw-bold text-end" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'');updateChange()">
                        <div class="d-flex gap-1 mt-2" id="quickButtons"></div>
                        <div class="rounded p-2 mt-2" style="background:#dcfce7"><div class="d-flex justify-content-between"><span class="text-success small">Kembalian</span><span class="fw-bold text-success" id="changeDisplay">Rp 0</span></div></div>
                    </div>
                    <div class="mb-3"><label class="form-label small">Catatan (opsional)</label><input type="text" name="note" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button></div>
            </div>
        </div>
    </div>
    <input type="hidden" name="discount" id="discountInputHidden" value="0">
</form>
@endsection

@section('scripts')
<script>
let cart=[], seq=0;
function addToCart(pid,vid,name,price,stock){
    let i=cart.find(x=>x.pid==pid&&x.vid==(vid||null));
    if(i){if(i.qty+1>stock){alert('Stok tidak mencukupi');return;}i.qty++;}else{cart.push({id:++seq,pid,vid:vid||null,name,price,stock,qty:1});}
    renderCart();
}
function changeQty(id,delta){
    let i=cart.find(x=>x.id==id);
    if(!i)return;
    i.qty+=delta;
    if(i.qty<1)cart=cart.filter(x=>x.id!=id);
    if(i.qty>i.stock){alert('Stok hanya tersisa '+i.stock);i.qty=i.stock;}
    renderCart();
}
function removeItem(id){cart=cart.filter(x=>x.id!=id);renderCart();}
function clearCart(){if(confirm('Kosongkan keranjang?')){cart=[];renderCart();}}
function renderCart(){
    const list=document.getElementById('cartList');
    const summary=document.getElementById('cartSummary');
    const clearBtn=document.getElementById('clearBtn');
    if(cart.length===0){
        list.innerHTML='<div class="text-center text-muted py-4"><i class="bi bi-cart" style="font-size:32px;opacity:0.3"></i><p class="small mt-2">Keranjang masih kosong</p></div>';
        summary.style.display='none';
        clearBtn.style.display='none';
        return;
    }
    clearBtn.style.display='inline-block';
    list.innerHTML=cart.map(c=>'<div class="d-flex align-items-center gap-2 border rounded p-2 mb-2"><div class="flex-grow-1"><p class="small fw-medium mb-0">'+c.name+'</p><p class="text-muted mb-0" style="font-size:11px">'+c.price.toLocaleString('id-ID')+' / pcs</p></div><div class="d-flex align-items-center gap-1"><button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="changeQty('+c.id+',-1)">-</button><span class="small fw-medium px-2">'+c.qty+'</span><button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="changeQty('+c.id+',1)" '+(c.qty>=c.stock?'disabled':'')+'>+</button></div><p class="small fw-semibold mb-0" style="width:80px;text-align:right">'+(c.price*c.qty).toLocaleString('id-ID')+'</p><button type="button" class="btn btn-link text-danger p-0" onclick="removeItem('+c.id+')"><i class="bi bi-x"></i></button></div>').join('');
    summary.style.display='block';
    updateTotal();
}
function updateTotal(){
    let sub=cart.reduce((s,c)=>s+c.price*c.qty,0);
    let disc=parseInt(document.getElementById('discountInput').value)||0;
    let total=Math.max(0,sub-disc);
    document.getElementById('subtotalDisplay').textContent='Rp '+sub.toLocaleString('id-ID');
    document.getElementById('totalDisplay').textContent='Rp '+total.toLocaleString('id-ID');
    document.getElementById('payBtnTotal').textContent='Rp '+total.toLocaleString('id-ID');
    document.getElementById('discountInputHidden').value=disc;
}
function openPay(){
    let sub=cart.reduce((s,c)=>s+c.price*c.qty,0);
    let disc=parseInt(document.getElementById('discountInput').value)||0;
    let total=Math.max(0,sub-disc);
    document.getElementById('payTotal').value='Rp '+total.toLocaleString('id-ID');
    document.getElementById('paySummary').innerHTML=cart.map(c=>'<div class="d-flex justify-content-between"><span class="text-truncate">'+c.name+' x'+c.qty+'</span><span>Rp '+(c.price*c.qty).toLocaleString('id-ID')+'</span></div>').join('');
    document.getElementById('itemsInput').innerHTML=cart.map(c=>'<input type="hidden" name="items['+c.id+'][product_id]" value="'+c.pid+'"><input type="hidden" name="items['+c.id+'][variant_id]" value="'+(c.vid||'')+'"><input type="hidden" name="items['+c.id+'][quantity]" value="'+c.qty+'">').join('');
    let qb=document.getElementById('quickButtons');
    let amounts=new Set();
    amounts.add(total);
    [50000,100000,200000].forEach(v=>{if(v>=total)amounts.add(v);});
    qb.innerHTML=Array.from(amounts).sort((a,b)=>a-b).slice(0,4).map(v=>'<button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" onclick="setAmount('+v+')">'+(v==total?'Uang Pas':'Rp '+v.toLocaleString('id-ID'))+'</button>').join('');
    setAmount(total);
}
function setAmount(v){document.getElementById('amountPaid').value=v;updateChange();}
function updateChange(){
    let total=parseInt(document.getElementById('payTotal').value.replace(/[^\d]/g,''))||0;
    let paid=parseInt(document.getElementById('amountPaid').value.replace(/\D/g,''))||0;
    let change=Math.max(0,paid-total);
    document.getElementById('changeDisplay').textContent='Rp '+change.toLocaleString('id-ID');
}
function selectMethod(m){
    document.querySelectorAll('.pay-method').forEach(b=>b.classList.toggle('active',b.dataset.method==m));
    document.getElementById('methodInput').value=m;
    document.getElementById('cashSection').style.display=m=='cash'?'block':'none';
}
function filterProducts(){
    let q=document.getElementById('prodSearch').value.toLowerCase();
    document.querySelectorAll('.prod-item').forEach(el=>{el.style.display=el.dataset.name.includes(q)?'':'none';});
}
</script>
@endsection
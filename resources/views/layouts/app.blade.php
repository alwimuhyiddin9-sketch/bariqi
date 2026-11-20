<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bariqi.co')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-dark: #1e3a8a;
            --navy: #0f172a;
            --bg: #f4f6f9;
            --card: #ffffff;
            --border: #e5e7eb;
            --text: #0f172a;
            --muted: #64748b;
            --success: #15803d;
            --warning: #d97706;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 14px; margin: 0; }
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 244px; background: #fff; border-right: 1px solid var(--border); display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .sidebar-brand { height: 88px; display: flex; align-items: center; padding: 0 20px; border-bottom: 1px solid var(--border); }
        .sidebar-brand .logo { font-size: 20px; font-weight: 700; color: var(--primary); }
        .sidebar-brand .logo .dot { color: var(--navy); }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px; }
        .sidebar-nav .group-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; padding: 8px 10px 4px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; color: #475569; text-decoration: none; font-size: 13.5px; font-weight: 500; margin-bottom: 2px; transition: all .15s; }
        .nav-item:hover { background: #f1f5f9; color: #0f172a; }
        .nav-item.active { background: var(--primary); color: #fff; }
        .nav-item i { font-size: 17px; }
        .sidebar-footer { border-top: 1px solid var(--border); padding: 12px; }
        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { height: 56px; background: #fff; border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 24px; gap: 12px; position: sticky; top: 0; z-index: 10; }
        .topbar .search { flex: 1; max-width: 400px; }
        .topbar .search input { border-radius: 8px; border: 1px solid var(--border); padding: 7px 12px 7px 36px; width: 100%; font-size: 13px; background: #f8fafc; }
        .topbar .clock { font-size: 13px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 4px 12px; border-radius: 8px; }
        .content { padding: 24px; flex: 1; }
        .content-inner { max-width: 1280px; margin: 0 auto; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 16px; flex-wrap: wrap; }
        .page-header h1 { font-size: 22px; font-weight: 700; margin: 0; }
        .page-header .desc { font-size: 13px; color: var(--muted); margin-top: 2px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 16px; }
        .card-header { padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: 15px; font-weight: 600; margin: 0; }
        .card-header .desc { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .card-body { padding: 20px; }
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 16px 20px; }
        .stat-card .title { font-size: 13px; color: var(--muted); margin: 0; }
        .stat-card .value { font-size: 22px; font-weight: 700; margin-top: 8px; }
        .stat-card .hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }
        .stat-card .icon { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; float: right; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .badge { font-size: 11px; font-weight: 500; padding: 3px 8px; border-radius: 6px; }
        .table { font-size: 13px; }
        .table th { font-weight: 600; color: var(--muted); border-bottom: 1px solid var(--border); padding: 10px 12px; }
        .table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        footer { text-align: center; padding: 10px 24px; border-top: 1px solid var(--border); color: #94a3b8; font-size: 11px; background: #fff; }
        @media (max-width: 991px) {
            .sidebar { display: none; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="logo">Bariqi<span class="dot">.co</span></span>
        </div>
        <nav class="sidebar-nav">
            @php $current = request()->route()->getName(); @endphp
            <div class="group-label">Menu Utama</div>
            <a class="nav-item {{ str_starts_with($current, 'dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid"></i> Beranda</a>
            <div class="group-label">Pengelolaan Barang</div>
            <a class="nav-item {{ str_starts_with($current, 'products') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-box"></i> Data Barang</a>
            <a class="nav-item {{ str_starts_with($current, 'categories') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Kategori Barang</a>
            <a class="nav-item {{ str_starts_with($current, 'variants') ? 'active' : '' }}" href="{{ route('variants.index') }}"><i class="bi bi-layers"></i> Varian Barang</a>
            <div class="group-label">Persediaan</div>
            <a class="nav-item {{ str_starts_with($current, 'inventory') ? 'active' : '' }}" href="{{ route('inventory.index') }}"><i class="bi bi-box-seam"></i> Persediaan Barang</a>
            <a class="nav-item {{ str_starts_with($current, 'stock-movements') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}"><i class="bi bi-clock-history"></i> Riwayat Stok</a>
            <div class="group-label">Penjualan</div>
            <a class="nav-item {{ str_starts_with($current, 'sales') && $current !== 'transactions.index' ? 'active' : '' }}" href="{{ route('sales.index') }}"><i class="bi bi-cart"></i> Penjualan Online</a>
            <a class="nav-item {{ str_starts_with($current, 'cashier') ? 'active' : '' }}" href="{{ route('cashier.index') }}"><i class="bi bi-calculator"></i> Kasir</a>
            <a class="nav-item {{ str_starts_with($current, 'transactions') ? 'active' : '' }}" href="{{ route('transactions.index') }}"><i class="bi bi-receipt"></i> Riwayat Transaksi</a>
            <div class="group-label">Pengelolaan Usaha</div>
            <a class="nav-item {{ str_starts_with($current, 'expenses') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><i class="bi bi-wallet"></i> Pengeluaran</a>
            <a class="nav-item {{ str_starts_with($current, 'reports') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart"></i> Laporan</a>
            <div class="group-label">Sistem</div>
            <a class="nav-item {{ str_starts_with($current, 'settings') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> Pengaturan</a>
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="nav-item w-100 text-danger border-0 bg-transparent"><i class="bi bi-box-arrow-right"></i> Keluar</button>
            </form>
        </div>
    </aside>
    <div class="main">
        <header class="topbar">
            <div class="search position-relative">
                <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                <input type="text" placeholder="Cari barang, transaksi...">
            </div>
            <div class="ms-auto clock" id="clock">{{ now()->format('H:i') }} · {{ now()->format('D d M') }}</div>
            <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm position-relative"><i class="bi bi-bell"></i></a>
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    {{ session('admin_name', 'Admin') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('settings.index') }}">Pengaturan</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.index') }}">Laporan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item text-danger">Keluar</button></form></li>
                </ul>
            </div>
        </header>
        <main class="content">
            <div class="content-inner">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @yield('content')
            </div>
        </main>
        <footer>&copy; {{ date('Y') }} Bariqi.co &middot; v1.0.0</footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
setInterval(() => {
    const d = new Date();
    const t = d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
    const dt = d.toLocaleDateString('id-ID', {weekday:'short', day:'numeric', month:'short'});
    const el = document.getElementById('clock');
    if (el) el.textContent = t + ' · ' + dt;
}, 30000);
</script>
@yield('scripts')
</body>
</html>

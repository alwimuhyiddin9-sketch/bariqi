<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Bariqi.co</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f1f5f9; font-family:'Segoe UI',system-ui,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:32px 16px; }
        .login-card { width:100%; max-width:400px; background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.05); }
        .logo-header { background:linear-gradient(135deg, #0f172a, #1e3a8a); padding:40px 24px; text-align:center; color:#fff; }
        .logo-header h1 { font-size:28px; font-weight:700; margin:0; }
        .logo-header h1 .dot { color:#60a5fa; }
        .logo-header p { color:#94a3b8; font-size:12px; margin-top:6px; }
        .form-body { padding:28px; }
        .form-body h2 { font-size:18px; font-weight:700; margin:0 0 4px; }
        .form-body .sub { color:#64748b; font-size:13px; margin-bottom:20px; }
        .form-control { height:42px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; }
        .form-control:focus { border-color:#1d4ed8; box-shadow:0 0 0 3px rgba(29,78,216,0.15); }
        .btn-primary { background:#1d4ed8; border:none; height:42px; font-weight:600; }
        .btn-primary:hover { background:#1e3a8a; }
        .demo-hint { margin-top:20px; padding:12px 14px; background:#f8fafc; border-radius:8px; font-size:12px; color:#64748b; text-align:center; }
        .demo-hint code { background:#fff; padding:2px 8px; border-radius:4px; border:1px solid #e5e7eb; color:#0f172a; font-weight:600; }
        .alert { font-size:13px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo-header">
        <h1>Bariqi<span class="dot">.co</span></h1>
        <p>Sistem Manajemen Data Barang, Persediaan & Penjualan</p>
    </div>
    <div class="form-body">
        <h2>Masuk</h2>
        <p class="sub">Gunakan kredensial administrator Anda untuk melanjutkan.</p>
        @if(session('error'))
            <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium small">Nama Pengguna</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', 'admin') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium small">Kata Sandi</label>
                <input type="password" name="password" class="form-control" value="{{ old('password', 'Bariqi2026!') }}" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk <i class="bi bi-arrow-right ms-1"></i></button>
        </form>
        <div class="demo-hint">
            Demo: <code>admin</code> / <code>Bariqi2026!</code>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

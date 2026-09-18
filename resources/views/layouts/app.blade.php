<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MC Almara')</title>
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}">
    <style>
        *{box-sizing:border-box}
        :root{--bg:#f5f7f6;--sidebar:#126b4f;--sidebar-dark:#0b503b;--sidebar-light:#16805e;--sidebar-soft:rgba(255,255,255,.09);--text:#0f172a;--muted:#64748b;--border:#e5e7eb;--card:#fff;--primary:#147957;--primary-light:#e7f5ef;--gold:#c7a968;--gold-light:#e0c47a;--success:#15803d;--danger:#b91c1c}
        html,body{margin:0;padding:0;min-height:100%}
        body{font-family:var(--ui-font-family);background:var(--ui-bg);color:var(--ui-text)}
        button,input,select,textarea{font:inherit} a{color:inherit}
        .app{min-height:100vh}.main{min-height:100vh;margin-left:275px;background:var(--ui-bg)}.content{padding:27px 20px 40px}
        .card{background:var(--ui-card-bg);border:1px solid var(--ui-border);border-radius:var(--ui-card-radius);overflow:hidden}
        .card-header{padding:16px 18px;border-bottom:1px solid var(--ui-border);display:flex;justify-content:space-between;align-items:center}
        .card-title{font-size:13px;font-weight:750}.card-link{font-size:11px;color:var(--ui-gold);text-decoration:none;font-weight:650}
        .table-wrapper{overflow-x:auto} table{width:100%;border-collapse:collapse} th{text-align:left;background:#fafafa;color:#6b7280;font-size:10px;text-transform:uppercase;letter-spacing:.5px;padding:10px 16px;border-bottom:1px solid var(--ui-border)} td{padding:13px 16px;font-size:12px;border-bottom:1px solid #f0f0f0} tr:last-child td{border-bottom:0}
        .primary-button{border:0;background:linear-gradient(135deg,var(--ui-primary),var(--ui-primary));color:#fff;border-radius:var(--ui-input-radius);padding:11px 16px;font-size:12px;font-weight:700;cursor:pointer}
        .primary-button:hover{background:linear-gradient(135deg,var(--ui-primary-dark),var(--ui-primary))}
        @media(max-width:800px){.main{margin-left:70px}.content{padding:20px 16px 30px}}
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    @include('components.sidebar')
    <main class="main">
        @include('components.topbar')
        @yield('content')
    </main>
</div>
<script src="{{ asset('js/transaction-workspace.js') }}"></script>
<script src="{{ asset('js/transaction-direction-stabilizer.js') }}"></script>
<script src="{{ asset('js/transaction-customer-create.js') }}"></script>
@stack('scripts')
</body>
</html>
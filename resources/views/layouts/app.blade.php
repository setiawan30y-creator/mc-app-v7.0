<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MC Almara')</title>
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/global-theme-compat.css') }}">
    <script src="{{ asset('js/theme-settings.js') }}"></script>
    <style>
        *{box-sizing:border-box}
        html,body{margin:0;padding:0;min-height:100%;background:var(--ui-page-bg);color:var(--ui-text)}
        body{font-family:var(--ui-font-family)}
        button,input,select,textarea{font:inherit} a{color:inherit}
        .app{min-height:100vh}.main{min-height:100vh;margin-left:275px;background:var(--ui-page-bg)}.content{padding:27px 20px 40px}
        .card{background:var(--ui-card-bg);border:1px solid var(--ui-card-border);border-radius:var(--ui-card-radius);overflow:hidden;box-shadow:var(--ui-card-shadow)}
        .card-header{padding:16px 18px;border-bottom:1px solid var(--ui-border);display:flex;justify-content:space-between;align-items:center;background:var(--ui-card-bg);color:var(--ui-text)}
        .card-title{font-size:13px;font-weight:750}.card-link{font-size:11px;color:var(--ui-gold);text-decoration:none;font-weight:650}
        .table-wrapper{overflow-x:auto} table{width:100%;border-collapse:collapse} th{text-align:left;background:var(--ui-table-header-bg);color:var(--ui-text-secondary);font-size:10px;text-transform:uppercase;letter-spacing:.5px;padding:10px 16px;border-bottom:1px solid var(--ui-table-border-strong)} td{padding:13px 16px;font-size:12px;border-bottom:1px solid var(--ui-table-border);color:var(--ui-text);background:var(--ui-surface)} tr:last-child td{border-bottom:0}
        .primary-button{border:0;background:var(--ui-primary);color:var(--ui-text-light);border-radius:var(--ui-button-radius);padding:11px 16px;font-size:12px;font-weight:700;cursor:pointer}
        .primary-button:hover{background:var(--ui-primary-dark)}
        @media(max-width:800px){.main{margin-left:70px}.content{padding:20px 16px 30px}}
    </style>
    @stack('styles')
    {{-- Load compatibility last so legacy/module CSS cannot override the Global UI tokens. --}}
    <link rel="stylesheet" href="{{ asset('css/global-theme-compat.css') }}?v={{ filemtime(public_path('css/global-theme-compat.css')) }}">
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
<script src="{{ asset('js/dashboard-data.js') }}"></script>
@stack('scripts')
</body>
</html>

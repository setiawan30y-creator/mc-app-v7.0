@extends('layouts.app')

@section('title', 'MC Almara — Dashboard')

@section('page-title', 'Dashboard')

@push('styles')
<style>
    .dashboard { display:flex; flex-direction:column; gap:18px; }
    .welcome-box { background:linear-gradient(135deg,#ffffff 0%,#f4fbf8 100%); border:1px solid #e5e7eb; border-radius:12px; padding:20px 22px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
    .welcome-title { font-size:19px; font-weight:800; color:#0f172a; margin-bottom:5px; }
    .welcome-text { font-size:11px; color:#64748b; }
    .welcome-badge { padding:8px 12px; border-radius:8px; background:#e7f5ef; color:#147957; font-size:10px; font-weight:750; white-space:nowrap; }
    .weather-card { border-radius:12px; padding:18px 20px; background:linear-gradient(135deg,#126b4f 0%,#16805e 55%,#0e6047 100%); color:#fff; display:flex; align-items:center; justify-content:space-between; position:relative; overflow:hidden; }
    .weather-card::after { content:""; position:absolute; width:170px; height:170px; right:-55px; top:-70px; border-radius:50%; background:rgba(255,255,255,.08); }
    .weather-left { display:flex; align-items:center; gap:15px; z-index:1; }
    .weather-icon { width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(255,255,255,.14); font-size:25px; }
    .weather-temp { font-size:25px; font-weight:800; line-height:1; }
    .weather-condition { margin-top:5px; font-size:11px; color:rgba(255,255,255,.82); }
    .weather-location { text-align:right; z-index:1; }
    .weather-location-title { font-size:11px; font-weight:750; }
    .weather-location-sub { margin-top:4px; font-size:9px; color:rgba(255,255,255,.7); }
    .finance-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:12px; }
    .finance-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:15px 15px 14px; min-width:0; transition:transform .15s ease,box-shadow .15s ease; }
    .finance-card:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(15,23,42,.07); }
    .finance-label { font-size:10px; color:#64748b; margin-bottom:9px; }
    .finance-value { font-size:16px; font-weight:800; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .finance-meta { margin-top:8px; display:flex; justify-content:space-between; align-items:center; font-size:9px; }
    .finance-positive { color:#15803d; font-weight:700; }
    .finance-negative { color:#b91c1c; font-weight:700; }
    .finance-neutral { color:#64748b; font-weight:650; }
    .finance-icon { width:27px; height:27px; border-radius:7px; display:flex; align-items:center; justify-content:center; background:#f3f4f6; font-size:12px; margin-bottom:12px; }
    .dashboard-main-grid { display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:16px; }
    .dashboard-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
    .dashboard-card-header { padding:15px 17px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
    .dashboard-card-title { font-size:12px; font-weight:800; color:#111827; }
    .dashboard-card-subtitle { margin-top:3px; font-size:9px; color:#9ca3af; }
    .dashboard-card-link { font-size:10px; font-weight:750; color:#b08a45; text-decoration:none; }
    .chart-toolbar { display:flex; align-items:center; gap:4px; }
    .chart-range { border:0; background:transparent; padding:5px 8px; border-radius:5px; color:#64748b; font-size:9px; font-weight:700; cursor:pointer; }
    .chart-range:hover { background:#f3f4f6; }
    .chart-range.active { background:#e7f5ef; color:#147957; }
    .chart-area { padding:16px 17px 12px; }
    .chart-legend { display:flex; gap:16px; margin-bottom:10px; }
    .legend-item { display:flex; align-items:center; gap:6px; font-size:9px; color:#64748b; }
    .legend-dot { width:7px; height:7px; border-radius:50%; }
    .legend-buy { background:#15803d; }
    .legend-sell { background:#b91c1c; }
    .chart-svg { width:100%; height:245px; display:block; }
    .side-stack { display:flex; flex-direction:column; gap:16px; }
    .summary-box { padding:17px; }
    .summary-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f0f0f0; }
    .summary-row:last-child { border-bottom:0; }
    .summary-label { font-size:10px; color:#64748b; }
    .summary-value { font-size:11px; font-weight:800; color:#111827; }
    .summary-total { margin-top:5px; padding:13px; border-radius:8px; background:#e7f5ef; }
    .summary-total-label { font-size:9px; color:#147957; }
    .summary-total-value { margin-top:4px; font-size:16px; font-weight:850; color:#075b43; }
    .transaction-table { width:100%; border-collapse:collapse; }
    .transaction-table th { text-align:left; background:#fafafa; color:#6b7280; font-size:9px; text-transform:uppercase; letter-spacing:.4px; padding:9px 14px; border-bottom:1px solid #e5e7eb; }
    .transaction-table td { padding:11px 14px; font-size:10px; border-bottom:1px solid #f1f1f1; color:#374151; }
    .transaction-table tr:last-child td { border-bottom:0; }
    .transaction-number { font-weight:800; color:#111827; }
    .transaction-customer { font-weight:700; color:#111827; }
    .transaction-type { display:inline-flex; padding:4px 7px; border-radius:5px; font-size:8px; font-weight:800; }
    .type-buy { background:#e7f5ef; color:#147957; }
    .type-sell { background:#fef2f2; color:#b91c1c; }
    .status-success { color:#15803d; font-weight:750; }
    .status-process { color:#b08a45; font-weight:750; }
    @media (max-width:1200px) { .finance-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:950px) { .dashboard-main-grid { grid-template-columns:1fr; } }
    @media (max-width:650px) { .finance-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .welcome-box { align-items:flex-start; flex-direction:column; } .weather-card { align-items:flex-start; flex-direction:column; gap:15px; } .weather-location { text-align:left; } .dashboard-card-header { flex-wrap:wrap; gap:10px; } }
</style>
@endpush

@section('content')
<div class="dashboard">
    <div class="welcome-box">
        <div>
            <div class="welcome-title">Selamat datang kembali, Yayan</div>
            <div class="welcome-text">Pantau operasional money changer Anda hari ini dalam satu dashboard.</div>
        </div>
        <div class="welcome-badge">● Operasional Aktif</div>
    </div>

    <div class="weather-card">
        <div class="weather-left">
            <div class="weather-icon">☁️</div>
            <div>
                <div class="weather-temp">28°</div>
                <div class="weather-condition">Cerah Berawan</div>
            </div>
        </div>
        <div class="weather-location">
            <div class="weather-location-title">📍 Jakarta</div>
            <div class="weather-location-sub">Indonesia · Hari ini</div>
        </div>
    </div>

    {{-- 7 FINANCE CARDS --}}
    <div class="finance-grid">
        <div class="finance-card">
            <div class="finance-icon">↓</div>
            <div class="finance-label">Pembelian</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-positive">Hari ini</span><span class="finance-neutral">0 transaksi</span></div>
        </div>

        <div class="finance-card">
            <div class="finance-icon">↑</div>
            <div class="finance-label">Penjualan</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-positive">Hari ini</span><span class="finance-neutral">0 transaksi</span></div>
        </div>

        <div class="finance-card">
            <div class="finance-icon">⇄</div>
            <div class="finance-label">Mutasi Bank</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-neutral">Saldo saat ini</span><span class="finance-neutral">Rp 0</span></div>
        </div>

        <div class="finance-card">
            <div class="finance-icon">−</div>
            <div class="finance-label">Pengeluaran</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-neutral">Cash out</span><span class="finance-neutral">Hari ini</span></div>
        </div>

        {{-- CASH RP --}}
        <div class="finance-card">
            <div class="finance-icon">Rp</div>
            <div class="finance-label">Cash Rp</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-positive">Saldo tersedia</span><span class="finance-neutral">Kas fisik</span></div>
        </div>

        <div class="finance-card">
            <div class="finance-icon">≈</div>
            <div class="finance-label">Selisih Rp</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-neutral">Closing</span><span class="finance-neutral">Hari ini</span></div>
        </div>

        <div class="finance-card">
            <div class="finance-icon">◆</div>
            <div class="finance-label">Profit</div>
            <div class="finance-value">Rp 0</div>
            <div class="finance-meta"><span class="finance-neutral">Hari ini</span><span class="finance-neutral">Belum dihitung</span></div>
        </div>
    </div>

    <div class="dashboard-main-grid">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <div>
                    <div class="dashboard-card-title">Pergerakan Kurs</div>
                    <div class="dashboard-card-subtitle">Kurs beli & jual</div>
                </div>
                <div class="chart-toolbar">
                    <button class="chart-range active">1D</button>
                    <button class="chart-range">5D</button>
                    <button class="chart-range">1M</button>
                    <button class="chart-range">5Y</button>
                    <button class="chart-range">Max</button>
                </div>
            </div>
            <div class="chart-area">
                <div class="chart-legend">
                    <div class="legend-item"><span class="legend-dot legend-buy"></span>Kurs Beli</div>
                    <div class="legend-item"><span class="legend-dot legend-sell"></span>Kurs Jual</div>
                </div>
                <svg class="chart-svg" viewBox="0 0 900 245" preserveAspectRatio="none">
                    <line x1="0" y1="30" x2="900" y2="30" stroke="#eef0f2" stroke-width="1" />
                    <line x1="0" y1="80" x2="900" y2="80" stroke="#eef0f2" stroke-width="1" />
                    <line x1="0" y1="130" x2="900" y2="130" stroke="#eef0f2" stroke-width="1" />
                    <line x1="0" y1="180" x2="900" y2="180" stroke="#eef0f2" stroke-width="1" />
                    <line x1="0" y1="230" x2="900" y2="230" stroke="#eef0f2" stroke-width="1" />
                    <polyline points="0,160 100,140 200,150 300,120 400,130 500,105 600,115 700,90 800,100 900,75" fill="none" stroke="#15803d" stroke-width="2.5" />
                    <polyline points="0,185 100,175 200,180 300,160 400,170 500,145 600,155 700,135 800,145 900,120" fill="none" stroke="#b91c1c" stroke-width="2.5" />
                </svg>
            </div>
        </div>

        <div class="side-stack">
            <div class="dashboard-card" data-dashboard-opening-panel="true">
                <div class="dashboard-card-header">
                    <div>
                        <div class="dashboard-card-title">Saldo Awal</div>
                        <div class="dashboard-card-subtitle">Saldo opening terakhir yang sudah finalized</div>
                    </div>
                </div>
                <div class="summary-box">
                    <div class="summary-row"><span class="summary-label">Tanggal</span><span class="summary-value" data-dashboard-opening="date">Belum ada</span></div>
                    <div class="summary-row"><span class="summary-label">Kas</span><span class="summary-value" data-dashboard-opening="cash">Rp 0</span></div>
                    <div class="summary-row"><span class="summary-label">Rekening</span><span class="summary-value" data-dashboard-opening="bank">Rp 0</span></div>
                    <div class="summary-row"><span class="summary-label">Valas</span><span class="summary-value" data-dashboard-opening="forex">Rp 0</span></div>
                    <div class="summary-total"><div class="summary-total-label">Total Saldo Awal</div><div class="summary-total-value" data-dashboard-opening="gross">Rp 0</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/dashboard-data.js') }}" defer></script>
@endpush

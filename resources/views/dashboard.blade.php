@extends('layouts.app')

@section('title', 'MC Almara — Dashboard')

@section('page-title', 'Dashboard')

@push('styles')
<style>

    /* =====================================================
       DASHBOARD
       ===================================================== */

    .dashboard {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* =====================================================
       WELCOME
       ===================================================== */

    .welcome-box {
        background: linear-gradient(
            135deg,
            #ffffff 0%,
            #f4fbf8 100%
        );

        border: 1px solid #e5e7eb;
        border-radius: 12px;

        padding: 20px 22px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 20px;
    }

    .welcome-title {
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 5px;
    }

    .welcome-text {
        font-size: 11px;
        color: #64748b;
    }

    .welcome-badge {
        padding: 8px 12px;
        border-radius: 8px;

        background: #e7f5ef;
        color: #147957;

        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }


    /* =====================================================
       WEATHER
       ===================================================== */

    .weather-card {

        border-radius: 12px;

        padding: 18px 20px;

        background:
            linear-gradient(
                135deg,
                #126b4f 0%,
                #16805e 55%,
                #0e6047 100%
            );

        color: #ffffff;

        display: flex;
        align-items: center;
        justify-content: space-between;

        position: relative;
        overflow: hidden;
    }

    .weather-card::after {

        content: "";

        position: absolute;

        width: 170px;
        height: 170px;

        right: -55px;
        top: -70px;

        border-radius: 50%;

        background: rgba(255,255,255,.08);
    }

    .weather-left {
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 1;
    }

    .weather-icon {

        width: 50px;
        height: 50px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 12px;

        background: rgba(255,255,255,.14);

        font-size: 25px;
    }

    .weather-temp {
        font-size: 25px;
        font-weight: 800;
        line-height: 1;
    }

    .weather-condition {
        margin-top: 5px;
        font-size: 11px;
        color: rgba(255,255,255,.82);
    }

    .weather-location {

        text-align: right;
        z-index: 1;
    }

    .weather-location-title {

        font-size: 11px;
        font-weight: 750;
    }

    .weather-location-sub {

        margin-top: 4px;

        font-size: 9px;

        color: rgba(255,255,255,.7);
    }


    /* =====================================================
       FINANCE CARDS
       ===================================================== */

    .finance-grid {

        display: grid;

        grid-template-columns:
            repeat(6, minmax(0, 1fr));

        gap: 12px;
    }

    .finance-card {

        background: #ffffff;

        border: 1px solid #e5e7eb;

        border-radius: 10px;

        padding: 15px 15px 14px;

        min-width: 0;

        transition:
            transform .15s ease,
            box-shadow .15s ease;
    }

    .finance-card:hover {

        transform: translateY(-2px);

        box-shadow:
            0 8px 22px rgba(15,23,42,.07);
    }

    .finance-label {

        font-size: 10px;

        color: #64748b;

        margin-bottom: 9px;
    }

    .finance-value {

        font-size: 16px;

        font-weight: 800;

        color: #0f172a;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .finance-meta {

        margin-top: 8px;

        display: flex;

        justify-content: space-between;

        align-items: center;

        font-size: 9px;
    }

    .finance-positive {
        color: #15803d;
        font-weight: 700;
    }

    .finance-negative {
        color: #b91c1c;
        font-weight: 700;
    }

    .finance-neutral {
        color: #64748b;
        font-weight: 650;
    }

    .finance-icon {

        width: 27px;
        height: 27px;

        border-radius: 7px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #f3f4f6;

        font-size: 12px;

        margin-bottom: 12px;
    }


    /* =====================================================
       MAIN GRID
       ===================================================== */

    .dashboard-main-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1fr)
            300px;

        gap: 16px;
    }


    /* =====================================================
       CARD
       ===================================================== */

    .dashboard-card {

        background: #ffffff;

        border: 1px solid #e5e7eb;

        border-radius: 10px;

        overflow: hidden;
    }

    .dashboard-card-header {

        padding: 15px 17px;

        border-bottom: 1px solid #e5e7eb;

        display: flex;

        justify-content: space-between;

        align-items: center;
    }

    .dashboard-card-title {

        font-size: 12px;

        font-weight: 800;

        color: #111827;
    }

    .dashboard-card-subtitle {

        margin-top: 3px;

        font-size: 9px;

        color: #9ca3af;
    }

    .dashboard-card-link {

        font-size: 10px;

        font-weight: 750;

        color: #b08a45;

        text-decoration: none;
    }


    /* =====================================================
       CHART
       ===================================================== */

    .chart-toolbar {

        display: flex;

        align-items: center;

        gap: 4px;
    }

    .chart-range {

        border: 0;

        background: transparent;

        padding: 5px 8px;

        border-radius: 5px;

        color: #64748b;

        font-size: 9px;

        font-weight: 700;

        cursor: pointer;
    }

    .chart-range:hover {

        background: #f3f4f6;

    }

    .chart-range.active {

        background: #e7f5ef;

        color: #147957;

    }

    .chart-area {

        padding: 16px 17px 12px;

    }

    .chart-legend {

        display: flex;

        gap: 16px;

        margin-bottom: 10px;
    }

    .legend-item {

        display: flex;

        align-items: center;

        gap: 6px;

        font-size: 9px;

        color: #64748b;
    }

    .legend-dot {

        width: 7px;

        height: 7px;

        border-radius: 50%;
    }

    .legend-buy {
        background: #15803d;
    }

    .legend-sell {
        background: #b91c1c;
    }

    .chart-svg {

        width: 100%;

        height: 245px;

        display: block;
    }


    /* =====================================================
       RIGHT COLUMN
       ===================================================== */

    .side-stack {

        display: flex;

        flex-direction: column;

        gap: 16px;
    }

    .summary-box {

        padding: 17px;
    }

    .summary-row {

        display: flex;

        justify-content: space-between;

        align-items: center;

        padding: 10px 0;

        border-bottom: 1px solid #f0f0f0;
    }

    .summary-row:last-child {

        border-bottom: 0;

    }

    .summary-label {

        font-size: 10px;

        color: #64748b;
    }

    .summary-value {

        font-size: 11px;

        font-weight: 800;

        color: #111827;
    }

    .summary-total {

        margin-top: 5px;

        padding: 13px;

        border-radius: 8px;

        background: #e7f5ef;
    }

    .summary-total-label {

        font-size: 9px;

        color: #147957;
    }

    .summary-total-value {

        margin-top: 4px;

        font-size: 16px;

        font-weight: 850;

        color: #075b43;
    }


    /* =====================================================
       TRANSACTIONS
       ===================================================== */

    .transaction-table {

        width: 100%;

        border-collapse: collapse;
    }

    .transaction-table th {

        text-align: left;

        background: #fafafa;

        color: #6b7280;

        font-size: 9px;

        text-transform: uppercase;

        letter-spacing: .4px;

        padding: 9px 14px;

        border-bottom: 1px solid #e5e7eb;
    }

    .transaction-table td {

        padding: 11px 14px;

        font-size: 10px;

        border-bottom: 1px solid #f1f1f1;

        color: #374151;
    }

    .transaction-table tr:last-child td {

        border-bottom: 0;

    }

    .transaction-number {

        font-weight: 800;

        color: #111827;
    }

    .transaction-customer {

        font-weight: 700;

        color: #111827;
    }

    .transaction-type {

        display: inline-flex;

        padding: 4px 7px;

        border-radius: 5px;

        font-size: 8px;

        font-weight: 800;
    }

    .type-buy {

        background: #e7f5ef;

        color: #147957;
    }

    .type-sell {

        background: #fef2f2;

        color: #b91c1c;
    }

    .status-success {

        color: #15803d;

        font-weight: 750;
    }

    .status-process {

        color: #b08a45;

        font-weight: 750;
    }


    /* =====================================================
       RESPONSIVE
       ===================================================== */

    @media (max-width: 1200px) {

        .finance-grid {

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

        }

    }

    @media (max-width: 950px) {

        .dashboard-main-grid {

            grid-template-columns: 1fr;

        }

    }

    @media (max-width: 650px) {

        .finance-grid {

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

        }

        .welcome-box {

            align-items: flex-start;

            flex-direction: column;

        }

        .weather-card {

            align-items: flex-start;

            flex-direction: column;

            gap: 15px;

        }

        .weather-location {

            text-align: left;

        }

        .dashboard-card-header {

            flex-wrap: wrap;

            gap: 10px;

        }

    }

</style>
@endpush


@section('content')

<div class="dashboard">


        {{-- =================================================
             WELCOME
        ================================================== --}}

        <div class="welcome-box">

            <div>

                <div class="welcome-title">
                    Selamat datang kembali, Yayan
                </div>

                <div class="welcome-text">
                    Pantau operasional money changer Anda hari ini
                    dalam satu dashboard.
                </div>

            </div>

            <div class="welcome-badge">
                ● Operasional Aktif
            </div>

        </div>


        {{-- =================================================
             WEATHER + LOCATION
        ================================================== --}}

        <div class="weather-card">

            <div class="weather-left">

                <div class="weather-icon">
                    ☁️
                </div>

                <div>

                    <div class="weather-temp">
                        28°
                    </div>

                    <div class="weather-condition">
                        Cerah Berawan
                    </div>

                </div>

            </div>


            <div class="weather-location">

                <div class="weather-location-title">
                    📍 Jakarta
                </div>

                <div class="weather-location-sub">
                    Indonesia · Hari ini
                </div>

            </div>

        </div>


        {{-- =================================================
             6 FINANCE CARDS
        ================================================== --}}

        <div class="finance-grid">


            {{-- PEMBELIAN --}}

            <div class="finance-card">

                <div class="finance-icon">
                    ↓
                </div>

                <div class="finance-label">
                    Pembelian
                </div>

                <div class="finance-value">
                    Rp 425.650.000
                </div>

                <div class="finance-meta">

                    <span class="finance-positive">
                        ↑ 12,4%
                    </span>

                    <span class="finance-neutral">
                        Hari ini
                    </span>

                </div>

            </div>


            {{-- PENJUALAN --}}

            <div class="finance-card">

                <div class="finance-icon">
                    ↑
                </div>

                <div class="finance-label">
                    Penjualan
                </div>

                <div class="finance-value">
                    Rp 512.340.000
                </div>

                <div class="finance-meta">

                    <span class="finance-positive">
                        ↑ 18,7%
                    </span>

                    <span class="finance-neutral">
                        Hari ini
                    </span>

                </div>

            </div>


            {{-- MUTASI BANK --}}

            <div class="finance-card">

                <div class="finance-icon">
                    ⇄
                </div>

                <div class="finance-label">
                    Mutasi Bank
                </div>

                <div class="finance-value">
                    Rp 937.125.000
                </div>

                <div class="finance-meta">

                    <span class="finance-positive">
                        ↑ 8,2%
                    </span>

                    <span class="finance-neutral">
                        30 transaksi
                    </span>

                </div>

            </div>


            {{-- PENGELUARAN --}}

            <div class="finance-card">

                <div class="finance-icon">
                    −
                </div>

                <div class="finance-label">
                    Pengeluaran
                </div>

                <div class="finance-value">
                    Rp 28.450.000
                </div>

                <div class="finance-meta">

                    <span class="finance-negative">
                        ↑ 3,1%
                    </span>

                    <span class="finance-neutral">
                        Hari ini
                    </span>

                </div>

            </div>


            {{-- SELISIH --}}

            <div class="finance-card">

                <div class="finance-icon">
                    ≈
                </div>

                <div class="finance-label">
                    Selisih Rp
                </div>

                <div class="finance-value">
                    Rp 3.240.000
                </div>

                <div class="finance-meta">

                    <span class="finance-positive">
                        Normal
                    </span>

                    <span class="finance-neutral">
                        Hari ini
                    </span>

                </div>

            </div>


            {{-- PROFIT --}}

            <div class="finance-card">

                <div class="finance-icon">
                    ◆
                </div>

                <div class="finance-label">
                    Profit
                </div>

                <div class="finance-value">
                    Rp 86.690.000
                </div>

                <div class="finance-meta">

                    <span class="finance-positive">
                        ↑ 14,8%
                    </span>

                    <span class="finance-neutral">
                        Hari ini
                    </span>

                </div>

            </div>

        </div>


        {{-- =================================================
             CHART + SUMMARY

             CARD MUTASI BANK BESAR SUDAH DIHAPUS
        ================================================== --}}

        <div class="dashboard-main-grid">


            {{-- =================================================
                 RATE CHART
            ================================================== --}}

            <div class="dashboard-card">

                <div class="dashboard-card-header">

                    <div>

                        <div class="dashboard-card-title">
                            Pergerakan Kurs
                        </div>

                        <div class="dashboard-card-subtitle">
                            Kurs beli & jual
                        </div>

                    </div>


                    <div class="chart-toolbar">

                        <button class="chart-range active">
                            1D
                        </button>

                        <button class="chart-range">
                            5D
                        </button>

                        <button class="chart-range">
                            1M
                        </button>

                        <button class="chart-range">
                            5Y
                        </button>

                        <button class="chart-range">
                            Max
                        </button>

                    </div>

                </div>


                <div class="chart-area">

                    <div class="chart-legend">

                        <div class="legend-item">

                            <span
                                class="legend-dot legend-buy">
                            </span>

                            Kurs Beli

                        </div>


                        <div class="legend-item">

                            <span
                                class="legend-dot legend-sell">
                            </span>

                            Kurs Jual

                        </div>

                    </div>


                    <svg
                        class="chart-svg"
                        viewBox="0 0 900 245"
                        preserveAspectRatio="none"
                    >

                        {{-- GRID --}}

                        <line
                            x1="0"
                            y1="30"
                            x2="900"
                            y2="30"
                            stroke="#eef0f2"
                            stroke-width="1"
                        />

                        <line
                            x1="0"
                            y1="80"
                            x2="900"
                            y2="80"
                            stroke="#eef0f2"
                            stroke-width="1"
                        />

                        <line
                            x1="0"
                            y1="130"
                            x2="900"
                            y2="130"
                            stroke="#eef0f2"
                            stroke-width="1"
                        />

                        <line
                            x1="0"
                            y1="180"
                            x2="900"
                            y2="180"
                            stroke="#eef0f2"
                            stroke-width="1"
                        />

                        <line
                            x1="0"
                            y1="230"
                            x2="900"
                            y2="230"
                            stroke="#eef0f2"
                            stroke-width="1"
                        />


                        {{-- KURS JUAL --}}

                        <polyline
                            points="
                                0,120
                                70,112
                                140,118
                                210,95
                                280,102
                                350,84
                                420,91
                                490,72
                                560,80
                                630,64
                                700,70
                                770,51
                                840,59
                                900,42
                            "
                            fill="none"
                            stroke="#b91c1c"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />


                        {{-- KURS BELI --}}

                        <polyline
                            points="
                                0,165
                                70,158
                                140,163
                                210,143
                                280,149
                                350,130
                                420,138
                                490,117
                                560,125
                                630,109
                                700,115
                                770,98
                                840,105
                                900,90
                            "
                            fill="none"
                            stroke="#15803d"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </div>

            </div>


            {{-- =================================================
                 RINGKASAN
            ================================================== --}}

            <div class="side-stack">


                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div>

                            <div class="dashboard-card-title">
                                Ringkasan Hari Ini
                            </div>

                            <div class="dashboard-card-subtitle">
                                Posisi operasional
                            </div>

                        </div>

                    </div>


                    <div class="summary-box">

                        <div class="summary-row">
                            <span class="summary-label">Saldo Awal Kas</span>
                            <span class="summary-value" data-dashboard-opening="cash">Rp 0</span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Saldo Awal Rekening</span>
                            <span class="summary-value" data-dashboard-opening="bank">Rp 0</span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Saldo Awal Valas</span>
                            <span class="summary-value" data-dashboard-opening="forex">Rp 0</span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Tanggal Saldo Awal</span>
                            <span class="summary-value" data-dashboard-opening="date">Belum ada</span>
                        </div>

                        <div class="summary-total">
                            <div class="summary-total-label">Total Saldo Awal</div>
                            <div class="summary-total-value" data-dashboard-opening="gross">Rp 0</div>
                        </div>

                    </div>

                </div>


            </div>

        </div>


        {{-- =================================================
             TRANSAKSI TERBARU
        ================================================== --}}

        <div class="dashboard-card">

            <div class="dashboard-card-header">

                <div>

                    <div class="dashboard-card-title">
                        Transaksi Terbaru
                    </div>

                    <div class="dashboard-card-subtitle">
                        Aktivitas transaksi terakhir
                    </div>

                </div>

                <a href="#" class="dashboard-card-link">
                    Lihat semua →
                </a>

            </div>


            <div class="table-wrapper">

                <table class="transaction-table">

                    <thead>

                        <tr>

                            <th>
                                No. Transaksi
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Jenis
                            </th>

                            <th>
                                Mata Uang
                            </th>

                            <th>
                                Nominal
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <td>
                                <span class="transaction-number">
                                    MC-20260911-0042
                                </span>
                            </td>

                            <td>
                                <span class="transaction-customer">
                                    Andi Wijaya
                                </span>
                            </td>

                            <td>
                                <span class="transaction-type type-buy">
                                    BELI
                                </span>
                            </td>

                            <td>
                                USD
                            </td>

                            <td>
                                5,000
                            </td>

                            <td>
                                Rp 80.750.000
                            </td>

                            <td>
                                <span class="status-success">
                                    Selesai
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <span class="transaction-number">
                                    MC-20260911-0041
                                </span>
                            </td>

                            <td>
                                <span class="transaction-customer">
                                    Budi Santoso
                                </span>
                            </td>

                            <td>
                                <span class="transaction-type type-sell">
                                    JUAL
                                </span>
                            </td>

                            <td>
                                SGD
                            </td>

                            <td>
                                10,000
                            </td>

                            <td>
                                Rp 119.500.000
                            </td>

                            <td>
                                <span class="status-success">
                                    Selesai
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <span class="transaction-number">
                                    MC-20260911-0040
                                </span>
                            </td>

                            <td>
                                <span class="transaction-customer">
                                    Siti Rahma
                                </span>
                            </td>

                            <td>
                                <span class="transaction-type type-buy">
                                    BELI
                                </span>
                            </td>

                            <td>
                                EUR
                            </td>

                            <td>
                                3,000
                            </td>

                            <td>
                                Rp 54.300.000
                            </td>

                            <td>
                                <span class="status-process">
                                    Diproses
                                </span>
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <span class="transaction-number">
                                    MC-20260911-0039
                                </span>
                            </td>

                            <td>
                                <span class="transaction-customer">
                                    Rudi Hartono
                                </span>
                            </td>

                            <td>
                                <span class="transaction-type type-sell">
                                    JUAL
                                </span>
                            </td>

                            <td>
                                JPY
                            </td>

                            <td>
                                1,000,000
                            </td>

                            <td>
                                Rp 108.200.000
                            </td>

                            <td>
                                <span class="status-success">
                                    Selesai
                                </span>
                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


        {{-- =================================================
             KURS TERKINI
        ================================================== --}}

        <div class="dashboard-card">

            <div class="dashboard-card-header">

                <div>

                    <div class="dashboard-card-title">
                        Kurs Mata Uang
                    </div>

                    <div class="dashboard-card-subtitle">
                        Kurs internal hari ini
                    </div>

                </div>

                <a href="#" class="dashboard-card-link">
                    Kelola kurs →
                </a>

            </div>


            <div class="table-wrapper">

                <table class="transaction-table">

                    <thead>

                        <tr>

                            <th>
                                Mata Uang
                            </th>

                            <th>
                                Nama
                            </th>

                            <th>
                                Kurs Beli
                            </th>

                            <th>
                                Kurs Jual
                            </th>

                            <th>
                                Update
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <tr>

                            <td>
                                <strong>USD</strong>
                            </td>

                            <td>
                                US Dollar
                            </td>

                            <td>
                                Rp 16.050
                            </td>

                            <td>
                                Rp 16.150
                            </td>

                            <td>
                                07:42
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <strong>SGD</strong>
                            </td>

                            <td>
                                Singapore Dollar
                            </td>

                            <td>
                                Rp 12.850
                            </td>

                            <td>
                                Rp 13.050
                            </td>

                            <td>
                                07:42
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <strong>EUR</strong>
                            </td>

                            <td>
                                Euro
                            </td>

                            <td>
                                Rp 18.050
                            </td>

                            <td>
                                Rp 18.300
                            </td>

                            <td>
                                07:42
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <strong>JPY</strong>
                            </td>

                            <td>
                                Japanese Yen
                            </td>

                            <td>
                                Rp 108
                            </td>

                            <td>
                                Rp 112
                            </td>

                            <td>
                                07:42
                            </td>

                        </tr>


                        <tr>

                            <td>
                                <strong>AUD</strong>
                            </td>

                            <td>
                                Australian Dollar
                            </td>

                            <td>
                                Rp 10.350
                            </td>

                            <td>
                                Rp 10.600
                            </td>

                            <td>
                                07:42
                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </div>


</div>

@endsection


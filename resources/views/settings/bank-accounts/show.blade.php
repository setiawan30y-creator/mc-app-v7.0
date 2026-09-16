@extends('layouts.app')

@section('content')

<style>
    .almara-bank-detail {
        max-width: 1180px;
        margin: 0 auto;
        padding: 30px 24px 50px;
    }

    .almara-bank-breadcrumb {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 18px;
        color: #718096;
        font-size: 13px;
    }

    .almara-bank-breadcrumb a {
        color: #49658a;
        text-decoration: none;
    }

    .almara-bank-breadcrumb a:hover {
        color: #087f70;
    }

    .almara-bank-breadcrumb .separator {
        color: #a0aec0;
    }

    .almara-bank-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .almara-bank-title {
        margin: 0;
        color: #102c3a;
        font-size: 36px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.8px;
    }

    .almara-bank-subtitle {
        margin: 8px 0 0;
        color: #60758a;
        font-size: 17px;
    }

    .almara-bank-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .almara-bank-btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
    }

    .almara-bank-btn-primary {
        color: #fff;
        background: #087f70;
        border: 1px solid #087f70;
    }

    .almara-bank-btn-primary:hover {
        color: #fff;
        background: #066e62;
    }

    .almara-bank-btn-secondary {
        color: #34495a;
        background: #fff;
        border: 1px solid #ccd8e0;
    }

    .almara-bank-btn-secondary:hover {
        color: #34495a;
        background: #f5f8fa;
    }

    .almara-bank-main {
        display: grid;
        grid-template-columns: 1.7fr 1fr;
        gap: 22px;
    }

    .almara-bank-card {
        background: #fff;
        border: 1px solid #e1e8ed;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(24,55,73,.07);
        overflow: hidden;
    }

    .almara-bank-card-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px 26px;
        border-bottom: 1px solid #edf1f4;
    }

    .almara-bank-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        border-radius: 11px;
        background: #eaf3f2;
        color: #086d61;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 29px;
    }

    .almara-bank-card-title {
        margin: 0;
        color: #142c3a;
        font-size: 21px;
        font-weight: 700;
    }

    .almara-bank-card-subtitle {
        margin: 4px 0 0;
        color: #708296;
        font-size: 13px;
    }

    .almara-bank-body {
        padding: 26px;
    }

    .almara-bank-info {
        display: grid;
        grid-template-columns: 190px 1fr;
        border-top: 1px solid #edf1f4;
    }

    .almara-bank-info-row {
        display: contents;
    }

    .almara-bank-info-label,
    .almara-bank-info-value {
        padding: 15px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .almara-bank-info-label {
        color: #718296;
        font-size: 13px;
        font-weight: 600;
    }

    .almara-bank-info-value {
        color: #25394a;
        font-size: 15px;
        font-weight: 600;
    }

    .almara-bank-account-number {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 6px;
        background: #f4f7f9;
        color: #263c4c;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        letter-spacing: .5px;
    }

    .almara-bank-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .almara-bank-status.active {
        color: #08735f;
        background: #e7f8f2;
    }

    .almara-bank-status.inactive {
        color: #8a4d18;
        background: #fff3df;
    }

    .almara-bank-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .almara-bank-balance-card {
        background: #f7fafb;
        border: 1px solid #e1e8ed;
        border-radius: 12px;
        padding: 21px;
    }

    .almara-bank-balance-label {
        color: #718296;
        font-size: 13px;
        font-weight: 600;
    }

    .almara-bank-balance {
        margin-top: 8px;
        color: #087f70;
        font-size: 30px;
        line-height: 1.2;
        font-weight: 750;
        letter-spacing: -.5px;
        word-break: break-word;
    }

    .almara-bank-balance-currency {
        margin-top: 6px;
        color: #718296;
        font-size: 13px;
    }

    .almara-bank-note {
        margin-top: 20px;
        padding: 15px 16px;
        border-radius: 9px;
        background: #ecfaf6;
        border: 1px solid #bde7dc;
        color: #08735f;
        font-size: 13px;
        line-height: 1.55;
    }

    .almara-bank-note strong {
        display: block;
        margin-bottom: 3px;
    }

    .almara-bank-back {
        margin-top: 20px;
    }

    @media (max-width: 900px) {
        .almara-bank-main {
            grid-template-columns: 1fr;
        }

        .almara-bank-header {
            flex-direction: column;
        }
    }

    @media (max-width: 600px) {
        .almara-bank-detail {
            padding: 24px 16px 40px;
        }

        .almara-bank-title {
            font-size: 28px;
        }

        .almara-bank-info {
            grid-template-columns: 1fr;
        }

        .almara-bank-info-label {
            padding-bottom: 5px;
            border-bottom: 0;
        }

        .almara-bank-info-value {
            padding-top: 0;
        }

        .almara-bank-actions {
            width: 100%;
        }

        .almara-bank-actions .almara-bank-btn {
            flex: 1;
        }
    }
</style>

<div class="almara-bank-detail">

    <div class="almara-bank-breadcrumb">
        <a href="{{ route('settings.bank-accounts.index') }}">Pengaturan</a>
        <span class="separator">›</span>
        <a href="{{ route('settings.bank-accounts.index') }}">Rekening Bank</a>
        <span class="separator">›</span>
        <span>Detail</span>
    </div>

    <div class="almara-bank-header">

        <div>
            <h1 class="almara-bank-title">
                Detail Rekening Bank
            </h1>

            <p class="almara-bank-subtitle">
                Informasi lengkap rekening bank yang terdaftar.
            </p>
        </div>

        <div class="almara-bank-actions">

            <a
                href="{{ route('settings.bank-accounts.edit', $account->id) }}"
                class="almara-bank-btn almara-bank-btn-primary"
            >
                ✎ Edit Rekening
            </a>

            <a
                href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}"
                class="almara-bank-btn almara-bank-btn-secondary"
            >
                ⇄ Mutasi Bank
            </a>

        </div>

    </div>

    <div class="almara-bank-main">

        {{-- Informasi --}}
        <div class="almara-bank-card">

            <div class="almara-bank-card-header">

                <div class="almara-bank-icon">
                    🏦
                </div>

                <div>
                    <h2 class="almara-bank-card-title">
                        {{ $account->bank_name }}
                    </h2>

                    <p class="almara-bank-card-subtitle">
                        {{ $account->account_number }}
                    </p>
                </div>

            </div>

            <div class="almara-bank-body">

                <div class="almara-bank-info">

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Nama Bank
                        </div>

                        <div class="almara-bank-info-value">
                            {{ $account->bank_name }}

                            @if($account->bank_code)
                                <span style="color:#718296;font-weight:500;">
                                    — {{ $account->bank_code }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Nama Pemilik
                        </div>

                        <div class="almara-bank-info-value">
                            {{ $account->account_name }}
                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Nomor Rekening
                        </div>

                        <div class="almara-bank-info-value">
                            <span class="almara-bank-account-number">
                                {{ $account->account_number }}
                            </span>
                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Mata Uang
                        </div>

                        <div class="almara-bank-info-value">
                            {{ $account->currency?->code ?? '-' }}
                            @if($account->currency?->name)
                                <span style="color:#718296;font-weight:500;">
                                    — {{ $account->currency->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Status
                        </div>

                        <div class="almara-bank-info-value">

                            @if($account->is_active)

                                <span class="almara-bank-status active">
                                    <span class="almara-bank-status-dot"></span>
                                    Aktif
                                </span>

                            @else

                                <span class="almara-bank-status inactive">
                                    <span class="almara-bank-status-dot"></span>
                                    Tidak Aktif
                                </span>

                            @endif

                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Saldo Awal
                        </div>

                        <div class="almara-bank-info-value">
                            {{ number_format((float)$account->opening_balance, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="almara-bank-info-row">
                        <div class="almara-bank-info-label">
                            Catatan
                        </div>

                        <div class="almara-bank-info-value">
                            {{ $account->notes ?: '-' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- Saldo --}}
        <div>

            <div class="almara-bank-card">

                <div class="almara-bank-card-header">

                    <div class="almara-bank-icon">
                        ◉
                    </div>

                    <div>
                        <h2 class="almara-bank-card-title">
                            Saldo Rekening
                        </h2>

                        <p class="almara-bank-card-subtitle">
                            Saldo hasil perhitungan mutasi.
                        </p>
                    </div>

                </div>

                <div class="almara-bank-body">

                    <div class="almara-bank-balance-card">

                        <div class="almara-bank-balance-label">
                            Saldo Berjalan
                        </div>

                        <div class="almara-bank-balance">
                            {{ number_format((float)$calculatedBalance, 2, ',', '.') }}
                        </div>

                        <div class="almara-bank-balance-currency">
                            {{ $account->currency?->code ?? '-' }}
                        </div>

                    </div>

                    <div class="almara-bank-note">

                        <strong>Informasi</strong>

                        Saldo berjalan dihitung dari saldo awal ditambah
                        credit dan dikurangi debit pada seluruh mutasi rekening.

                    </div>

                </div>

            </div>

            <div class="almara-bank-back">

                <a
                    href="{{ route('settings.bank-accounts.index') }}"
                    class="almara-bank-btn almara-bank-btn-secondary"
                >
                    ← Kembali ke Rekening Bank
                </a>

            </div>

        </div>

    </div>

</div>

@endsection

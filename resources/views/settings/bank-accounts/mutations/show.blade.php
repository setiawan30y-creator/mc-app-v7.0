@extends('layouts.app')

@section('content')

<style>
    .almara-mutation-detail {
        max-width: 1100px;
        margin: 0 auto;
        padding: 30px 24px 50px;
    }

    .almara-mutation-breadcrumb {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 18px;
        color: #718096;
        font-size: 13px;
    }

    .almara-mutation-breadcrumb a {
        color: #49658a;
        text-decoration: none;
    }

    .almara-mutation-separator {
        color: #a0aec0;
    }

    .almara-mutation-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 25px;
    }

    .almara-mutation-title {
        margin: 0;
        color: #102c3a;
        font-size: 36px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -.8px;
    }

    .almara-mutation-subtitle {
        margin: 8px 0 0;
        color: #60758a;
        font-size: 16px;
    }

    .almara-mutation-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .almara-mutation-btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 17px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .almara-mutation-btn-secondary {
        color: #34495a;
        background: #fff;
        border-color: #ccd8e0;
    }

    .almara-mutation-btn-primary {
        color: #fff;
        background: #087f70;
        border-color: #087f70;
    }

    .almara-mutation-btn-danger {
        color: #a92f2f;
        background: #fff5f5;
        border-color: #efc2c2;
    }

    .almara-mutation-card {
        background: #fff;
        border: 1px solid #e1e8ed;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(24,55,73,.07);
        overflow: hidden;
    }

    .almara-mutation-card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 24px 28px;
        background: #f7fafb;
        border-bottom: 1px solid #e8edf1;
    }

    .almara-mutation-icon {
        width: 55px;
        height: 55px;
        flex: 0 0 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #eaf3f2;
        color: #087f70;
        font-size: 27px;
    }

    .almara-mutation-card-title {
        margin: 0;
        color: #172d3d;
        font-size: 21px;
        font-weight: 700;
    }

    .almara-mutation-card-subtitle {
        margin: 4px 0 0;
        color: #718296;
        font-size: 13px;
    }

    .almara-mutation-body {
        padding: 28px;
    }

    .almara-mutation-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 35px;
    }

    .almara-mutation-info {
        padding: 16px 0;
        border-bottom: 1px solid #edf1f4;
    }

    .almara-mutation-info-label {
        margin-bottom: 5px;
        color: #718296;
        font-size: 12px;
        font-weight: 650;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .almara-mutation-info-value {
        color: #263b4a;
        font-size: 15px;
        font-weight: 600;
        word-break: break-word;
    }

    .almara-mutation-amount {
        font-size: 22px;
        font-weight: 750;
    }

    .almara-mutation-amount.debit {
        color: #b44444;
    }

    .almara-mutation-amount.credit {
        color: #08735f;
    }

    .almara-mutation-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .almara-status-unmatched {
        color: #966019;
        background: #fff4df;
    }

    .almara-status-matched {
        color: #08735f;
        background: #e7f8f2;
    }

    .almara-status-manual {
        color: #4b62a0;
        background: #edf1ff;
    }

    .almara-status-ignored {
        color: #718096;
        background: #eef1f3;
    }

    .almara-mutation-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .almara-mutation-description {
        margin-top: 25px;
        padding: 18px;
        border-radius: 10px;
        background: #f7fafb;
        border: 1px solid #e1e8ed;
        color: #405567;
        line-height: 1.6;
        font-size: 14px;
    }

    .almara-mutation-description-title {
        margin-bottom: 7px;
        color: #172d3d;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .almara-mutation-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 28px;
        background: #fafcfd;
        border-top: 1px solid #e8edf1;
    }

    @media (max-width: 800px) {
        .almara-mutation-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .almara-mutation-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .almara-mutation-detail {
            padding: 24px 16px 40px;
        }

        .almara-mutation-title {
            font-size: 28px;
        }

        .almara-mutation-card-header,
        .almara-mutation-body {
            padding: 20px;
        }

        .almara-mutation-footer {
            padding: 16px 20px;
        }
    }
</style>

@php
    $statusClass = match($mutation->reconciliation_status) {
        'matched' => 'almara-status-matched',
        'manual' => 'almara-status-manual',
        'ignored' => 'almara-status-ignored',
        default => 'almara-status-unmatched',
    };

    $statusLabel = match($mutation->reconciliation_status) {
        'matched' => 'Matched',
        'manual' => 'Manual',
        'ignored' => 'Ignored',
        default => 'Unmatched',
    };
@endphp

<div class="almara-mutation-detail">

    <div class="almara-mutation-breadcrumb">
        <a href="{{ route('settings.bank-accounts.index') }}">Pengaturan</a>
        <span class="almara-mutation-separator">›</span>
        <a href="{{ route('settings.bank-accounts.show', $account->id) }}">
            {{ $account->bank_name }}
        </a>
        <span class="almara-mutation-separator">›</span>
        <a href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}">
            Mutasi Bank
        </a>
        <span class="almara-mutation-separator">›</span>
        <span>Detail</span>
    </div>

    <div class="almara-mutation-header">

        <div>
            <h1 class="almara-mutation-title">
                Detail Mutasi
            </h1>

            <p class="almara-mutation-subtitle">
                {{ $account->bank_name }} · {{ $account->account_number }}
            </p>
        </div>

        <div class="almara-mutation-actions">

            <a
                href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}"
                class="almara-mutation-btn almara-mutation-btn-secondary"
            >
                ← Kembali
            </a>

        </div>

    </div>

    <div class="almara-mutation-card">

        <div class="almara-mutation-card-header">

            <div class="almara-mutation-icon">
                ⇄
            </div>

            <div>

                <h2 class="almara-mutation-card-title">
                    {{ $account->bank_name }}
                </h2>

                <p class="almara-mutation-card-subtitle">
                    {{ $account->account_name }}
                    ·
                    {{ $account->account_number }}
                    ·
                    {{ $account->currency?->code ?? '-' }}
                </p>

            </div>

        </div>

        <div class="almara-mutation-body">

            <div class="almara-mutation-grid">

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Tanggal Transaksi
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ $mutation->transaction_date?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Value Date
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ $mutation->value_date?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Referensi
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ $mutation->reference ?: '-' }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        External ID
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ $mutation->external_id ?: '-' }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Debit
                    </div>

                    <div class="almara-mutation-info-value">
                        <span class="almara-mutation-amount debit">
                            {{ number_format((float)$mutation->debit, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Credit
                    </div>

                    <div class="almara-mutation-info-value">
                        <span class="almara-mutation-amount credit">
                            {{ number_format((float)$mutation->credit, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Saldo dari Bank
                    </div>

                    <div class="almara-mutation-info-value">
                        @if($mutation->balance !== null)
                            {{ number_format((float)$mutation->balance, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Saldo Hasil Perhitungan
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ number_format((float)$calculatedBalance, 2, ',', '.') }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Source
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ ucfirst(str_replace('_', ' ', $mutation->source)) }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Rekonsiliasi
                    </div>

                    <div class="almara-mutation-info-value">

                        <span class="almara-mutation-status {{ $statusClass }}">
                            <span class="almara-mutation-status-dot"></span>
                            {{ $statusLabel }}
                        </span>

                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Transaksi MC
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ $mutation->matched_transaction_id ?: '-' }}
                    </div>
                </div>

                <div class="almara-mutation-info">
                    <div class="almara-mutation-info-label">
                        Net Amount
                    </div>

                    <div class="almara-mutation-info-value">
                        {{ number_format((float)$mutation->net_amount, 2, ',', '.') }}
                    </div>
                </div>

            </div>

            <div class="almara-mutation-description">

                <div class="almara-mutation-description-title">
                    Keterangan
                </div>

                {{ $mutation->description ?: 'Tidak ada keterangan.' }}

            </div>

            @if($mutation->notes)

                <div class="almara-mutation-description">

                    <div class="almara-mutation-description-title">
                        Catatan Internal
                    </div>

                    {{ $mutation->notes }}

                </div>

            @endif

        </div>

        <div class="almara-mutation-footer">

            <a
                href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}"
                class="almara-mutation-btn almara-mutation-btn-secondary"
            >
                ← Daftar Mutasi
            </a>

            @if($mutation->reconciliation_status !== 'ignored')

                <form
                    method="POST"
                    action="{{ route('settings.bank-accounts.mutations.ignore', [
                        'bankAccount' => $account->id,
                        'mutation' => $mutation->id
                    ]) }}"
                    onsubmit="return confirm('Tandai mutasi ini sebagai ignored?');"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="almara-mutation-btn almara-mutation-btn-danger"
                    >
                        Tandai Ignored
                    </button>
                </form>

            @endif

        </div>

    </div>

</div>

@endsection

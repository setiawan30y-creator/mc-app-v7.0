@extends('layouts.app')

@section('content')

<style>
    .almara-mutation-page {
        max-width: 1250px;
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

    .almara-mutation-breadcrumb a:hover {
        color: #087f70;
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

    .almara-mutation-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .almara-mutation-btn {
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
        border: 1px solid transparent;
        cursor: pointer;
        transition: .18s ease;
    }

    .almara-mutation-btn-primary {
        color: #fff;
        background: #087f70;
        border-color: #087f70;
        box-shadow: 0 4px 10px rgba(8,127,112,.15);
    }

    .almara-mutation-btn-primary:hover {
        color: #fff;
        background: #066e62;
        border-color: #066e62;
        transform: translateY(-1px);
    }

    .almara-mutation-btn-secondary {
        color: #34495a;
        background: #fff;
        border-color: #ccd8e0;
    }

    .almara-mutation-btn-secondary:hover {
        color: #34495a;
        background: #f5f8fa;
    }

    .almara-mutation-btn-small {
        min-height: 36px;
        padding: 0 12px;
        font-size: 12px;
    }

    .almara-mutation-card {
        background: #fff;
        border: 1px solid #e1e8ed;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(24,55,73,.07);
        overflow: hidden;
    }

    .almara-mutation-account {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px 24px;
        background: #f7fafb;
        border-bottom: 1px solid #e8edf1;
    }

    .almara-mutation-bank-icon {
        width: 50px;
        height: 50px;
        flex: 0 0 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eaf3f2;
        color: #086d61;
        font-size: 25px;
    }

    .almara-mutation-account-name {
        color: #172d3d;
        font-size: 17px;
        font-weight: 700;
    }

    .almara-mutation-account-number {
        margin-top: 3px;
        color: #718296;
        font-size: 13px;
    }

    .almara-mutation-account-currency {
        margin-left: auto;
        padding: 7px 11px;
        border-radius: 7px;
        background: #fff;
        border: 1px solid #dce5eb;
        color: #08735f;
        font-size: 13px;
        font-weight: 700;
    }

    .almara-mutation-table-wrap {
        overflow-x: auto;
    }

    .almara-mutation-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .almara-mutation-table th {
        padding: 14px 17px;
        background: #fafcfd;
        border-bottom: 1px solid #e5ebef;
        color: #718296;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
    }

    .almara-mutation-table td {
        padding: 16px 17px;
        border-bottom: 1px solid #edf1f4;
        color: #304556;
        font-size: 14px;
        vertical-align: middle;
    }

    .almara-mutation-table tbody tr:hover {
        background: #fbfdfd;
    }

    .almara-mutation-date {
        color: #263b4a;
        font-weight: 650;
        white-space: nowrap;
    }

    .almara-mutation-time {
        margin-top: 3px;
        color: #8998a6;
        font-size: 12px;
    }

    .almara-mutation-reference {
        color: #263b4a;
        font-weight: 600;
    }

    .almara-mutation-description {
        max-width: 260px;
        margin-top: 4px;
        color: #718296;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .almara-mutation-debit {
        color: #b44444;
        font-weight: 700;
        white-space: nowrap;
        text-align: right;
    }

    .almara-mutation-credit {
        color: #08735f;
        font-weight: 700;
        white-space: nowrap;
        text-align: right;
    }

    .almara-mutation-balance {
        color: #263b4a;
        font-weight: 700;
        white-space: nowrap;
        text-align: right;
    }

    .almara-mutation-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .almara-mutation-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
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

    .almara-mutation-empty {
        padding: 65px 25px;
        text-align: center;
    }

    .almara-mutation-empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .almara-mutation-empty h3 {
        margin: 0;
        color: #263b4a;
        font-size: 19px;
    }

    .almara-mutation-empty p {
        margin: 7px 0 20px;
        color: #718296;
        font-size: 14px;
    }

    .almara-mutation-alert {
        margin-bottom: 20px;
        padding: 13px 16px;
        border-radius: 9px;
        font-size: 14px;
    }

    .almara-mutation-alert-success {
        color: #08735f;
        background: #ecfaf6;
        border: 1px solid #bde7dc;
    }

    @media (max-width: 800px) {
        .almara-mutation-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .almara-mutation-account {
            align-items: flex-start;
        }

        .almara-mutation-account-currency {
            margin-left: auto;
        }
    }

    @media (max-width: 600px) {
        .almara-mutation-page {
            padding: 24px 16px 40px;
        }

        .almara-mutation-title {
            font-size: 28px;
        }

        .almara-mutation-header-actions {
            width: 100%;
        }

        .almara-mutation-header-actions .almara-mutation-btn {
            flex: 1;
        }
    }
</style>

<div class="almara-mutation-page">

    <div class="almara-mutation-breadcrumb">
        <a href="{{ route('settings.bank-accounts.index') }}">Pengaturan</a>
        <span class="almara-mutation-separator">›</span>
        <a href="{{ route('settings.bank-accounts.show', $account->id) }}">
            Rekening Bank
        </a>
        <span class="almara-mutation-separator">›</span>
        <span>Mutasi Bank</span>
    </div>

    <div class="almara-mutation-header">

        <div>
            <h1 class="almara-mutation-title">
                Mutasi Bank
            </h1>

            <p class="almara-mutation-subtitle">
                Riwayat transaksi rekening {{ $account->bank_name }}.
            </p>
        </div>

        <div class="almara-mutation-header-actions">

            <a
                href="{{ route('settings.bank-accounts.show', $account->id) }}"
                class="almara-mutation-btn almara-mutation-btn-secondary"
            >
                ← Detail Rekening
            </a>

            <a
                href="{{ route('settings.bank-accounts.mutations.create', $account->id) }}"
                class="almara-mutation-btn almara-mutation-btn-primary"
            >
                ＋ Tambah Mutasi
            </a>

        </div>

    </div>

    @if(session('success'))
        <div class="almara-mutation-alert almara-mutation-alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="almara-mutation-card">

        <div class="almara-mutation-account">

            <div class="almara-mutation-bank-icon">
                🏦
            </div>

            <div>
                <div class="almara-mutation-account-name">
                    {{ $account->bank_name }}
                </div>

                <div class="almara-mutation-account-number">
                    {{ $account->account_name }}
                    ·
                    {{ $account->account_number }}
                </div>
            </div>

            <div class="almara-mutation-account-currency">
                {{ $account->currency?->code ?? '-' }}
            </div>

        </div>

        @if($mutations->isEmpty())

            <div class="almara-mutation-empty">

                <div class="almara-mutation-empty-icon">
                    ⇄
                </div>

                <h3>Belum ada mutasi bank</h3>

                <p>
                    Belum ada transaksi mutasi yang tercatat pada rekening ini.
                </p>

                <a
                    href="{{ route('settings.bank-accounts.mutations.create', $account->id) }}"
                    class="almara-mutation-btn almara-mutation-btn-primary"
                >
                    ＋ Tambah Mutasi Pertama
                </a>

            </div>

        @else

            <div class="almara-mutation-table-wrap">

                <table class="almara-mutation-table">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Referensi / Keterangan</th>
                            <th style="text-align:right;">Debit</th>
                            <th style="text-align:right;">Credit</th>
                            <th style="text-align:right;">Saldo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($mutations as $mutation)

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

                        <tr>

                            <td>
                                <div class="almara-mutation-date">
                                    {{ $mutation->transaction_date?->format('d/m/Y') }}
                                </div>

                                <div class="almara-mutation-time">
                                    {{ $mutation->transaction_date?->format('H:i') }}
                                </div>
                            </td>

                            <td>

                                <div class="almara-mutation-reference">
                                    {{ $mutation->reference ?: '-' }}
                                </div>

                                @if($mutation->description)
                                    <div class="almara-mutation-description">
                                        {{ $mutation->description }}
                                    </div>
                                @endif

                            </td>

                            <td class="almara-mutation-debit">
                                @if((float)$mutation->debit > 0)
                                    {{ number_format((float)$mutation->debit, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="almara-mutation-credit">
                                @if((float)$mutation->credit > 0)
                                    {{ number_format((float)$mutation->credit, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="almara-mutation-balance">

                                @if($mutation->balance !== null)
                                    {{ number_format((float)$mutation->balance, 2, ',', '.') }}
                                @else
                                    {{ number_format((float)$mutation->net_amount, 2, ',', '.') }}
                                @endif

                            </td>

                            <td>

                                <span class="almara-mutation-status {{ $statusClass }}">
                                    <span class="almara-mutation-status-dot"></span>
                                    {{ $statusLabel }}
                                </span>

                            </td>

                            <td>

                                <a
                                    href="{{ route('settings.bank-accounts.mutations.show', [
                                        'bankAccount' => $account->id,
                                        'mutation' => $mutation->id
                                    ]) }}"
                                    class="almara-mutation-btn almara-mutation-btn-secondary almara-mutation-btn-small"
                                >
                                    Detail
                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection

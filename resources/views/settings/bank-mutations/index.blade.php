@extends('layouts.app')

@section('content')
<style>
    .bank-mutation-page {
        padding: 24px;
    }

    .bank-mutation-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 22px;
    }

    .bank-mutation-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #111827;
    }

    .bank-mutation-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    /*
    |--------------------------------------------------------------------------
    | TOP GRID
    |--------------------------------------------------------------------------
    | Ringkasan dan Filter berdampingan di desktop.
    | Akan menjadi satu kolom di layar kecil.
    */
    .mutation-top-grid {
        display: grid;
        grid-template-columns: minmax(300px, 0.9fr) minmax(520px, 1.6fr);
        gap: 20px;
        align-items: stretch;
        margin-bottom: 20px;
    }

    .mutation-top-grid > .bank-mutation-card {
        margin-bottom: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */
    .bank-mutation-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .bank-mutation-card-header {
        padding: 17px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .bank-mutation-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 750;
        color: #111827;
    }

    .bank-mutation-card-body {
        padding: 20px;
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */
    .mutation-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .mutation-summary-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 13px 14px;
        background: #f9fafb;
    }

    .mutation-summary-code {
        font-size: 13px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
    }

    .mutation-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 6px;
        font-size: 12px;
    }

    .mutation-summary-label {
        color: #6b7280;
    }

    .mutation-summary-value {
        font-weight: 750;
        color: #111827;
        text-align: right;
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .filter-field.search-field {
        grid-column: span 2;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 750;
        color: #374151;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 9px 12px;
        background: #fff;
        color: #111827;
        outline: none;
        box-sizing: border-box;
        font-size: 13px;
    }

    .filter-input:focus,
    .filter-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .10);
    }

    .filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        height: 100%;
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 9px 15px;
        border-radius: 10px;
        border: 1px solid transparent;
        text-decoration: none;
        font-size: 13px;
        font-weight: 750;
        cursor: pointer;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .btn-primary {
        background: #111827;
        color: #fff;
    }

    .btn-primary:hover {
        background: #1f2937;
    }

    .btn-secondary {
        background: #fff;
        color: #374151;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE FILTER
    |--------------------------------------------------------------------------
    */
    .active-filter-wrapper {
        margin-top: 15px;
        padding-top: 13px;
        border-top: 1px solid #f1f5f9;
    }

    .active-filter-label {
        display: inline-block;
        margin-right: 6px;
        margin-bottom: 6px;
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
    }

    .active-filter {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 11px;
        font-weight: 750;
        margin-right: 5px;
        margin-bottom: 6px;
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    .table-card {
        margin-bottom: 20px;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .mutation-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1120px;
    }

    .mutation-table th {
        padding: 12px 14px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .mutation-table td {
        padding: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
        font-size: 13px;
        color: #374151;
    }

    .mutation-table tbody tr:hover {
        background: #fafafa;
    }

    .mutation-date {
        font-weight: 750;
        color: #111827;
        white-space: nowrap;
    }

    .mutation-time {
        margin-top: 3px;
        color: #9ca3af;
        font-size: 11px;
    }

    .bank-name {
        font-weight: 750;
        color: #111827;
    }

    .account-number {
        margin-top: 3px;
        color: #6b7280;
        font-size: 12px;
    }

    .description {
        max-width: 300px;
        color: #374151;
    }

    .reference {
        margin-top: 4px;
        color: #9ca3af;
        font-size: 11px;
    }

    /*
    |--------------------------------------------------------------------------
    | AMOUNT
    |--------------------------------------------------------------------------
    */
    .amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 750;
    }

    .amount-credit {
        color: #047857;
    }

    .amount-debit {
        color: #b91c1c;
    }

    .amount-zero {
        color: #9ca3af;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */
    .currency-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 11px;
        font-weight: 800;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-unmatched {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-matched {
        background: #ecfdf5;
        color: #047857;
    }

    .status-manual {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-ignored {
        background: #f3f4f6;
        color: #6b7280;
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL
    |--------------------------------------------------------------------------
    */
    .detail-link {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        color: #374151;
        text-decoration: none;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .detail-link:hover {
        background: #f9fafb;
    }

    .source-text {
        color: #6b7280;
        font-size: 11px;
        margin-top: 4px;
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */
    .empty-state {
        padding: 50px 20px;
        text-align: center;
        color: #6b7280;
    }

    .empty-state-title {
        margin-bottom: 6px;
        color: #374151;
        font-weight: 750;
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    .pagination-wrapper {
        padding: 18px 20px;
        border-top: 1px solid #e5e7eb;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */
    @media (max-width: 1100px) {
        .mutation-top-grid {
            grid-template-columns: 1fr;
        }

        .mutation-top-grid > .bank-mutation-card {
            margin-bottom: 0;
        }

        .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-field.search-field {
            grid-column: span 2;
        }
    }

    @media (max-width: 640px) {
        .bank-mutation-page {
            padding: 14px;
        }

        .bank-mutation-header {
            flex-direction: column;
        }

        .bank-mutation-header .btn {
            width: 100%;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-field.search-field {
            grid-column: span 1;
        }

        .filter-actions {
            align-items: stretch;
        }

        .filter-actions .btn {
            flex: 1;
        }
    }
</style>

<div class="bank-mutation-page">

    {{-- HEADER --}}
    <div class="bank-mutation-header">

        <div>
            <h1 class="bank-mutation-title">
                Mutasi Bank
            </h1>

            <p class="bank-mutation-subtitle">
                Monitoring seluruh mutasi rekening bank pada cabang aktif.
            </p>
        </div>

        <a
            href="{{ route('settings.bank-accounts.index') }}"
            class="btn btn-secondary"
        >
            ← Rekening Bank
        </a>

    </div>


    {{-- ================================================================
         RINGKASAN + FILTER
         ================================================================ --}}
    <div class="mutation-top-grid">

        {{-- SUMMARY --}}
        @if($summary->count())

            <div class="bank-mutation-card">

                <div class="bank-mutation-card-header">
                    <h2 class="bank-mutation-card-title">
                        Ringkasan Mutasi
                    </h2>
                </div>

                <div class="bank-mutation-card-body">

                    <div class="mutation-summary-grid">

                        @foreach($summary as $item)

                            <div class="mutation-summary-item">

                                <div class="mutation-summary-code">
                                    {{ $item->currency_code ?? '-' }}
                                </div>

                                <div class="mutation-summary-row">
                                    <span class="mutation-summary-label">
                                        Jumlah mutasi
                                    </span>

                                    <span class="mutation-summary-value">
                                        {{ number_format((int) $item->mutation_count, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mutation-summary-row">
                                    <span class="mutation-summary-label">
                                        Credit
                                    </span>

                                    <span class="mutation-summary-value">
                                        {{ number_format((float) $item->total_credit, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mutation-summary-row">
                                    <span class="mutation-summary-label">
                                        Debit
                                    </span>

                                    <span class="mutation-summary-value">
                                        {{ number_format((float) $item->total_debit, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mutation-summary-row">
                                    <span class="mutation-summary-label">
                                        Net
                                    </span>

                                    <span class="mutation-summary-value">
                                        {{ number_format(
                                            (float) $item->total_credit - (float) $item->total_debit,
                                            2,
                                            ',',
                                            '.'
                                        ) }}
                                    </span>
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif


        {{-- FILTER --}}
        <div class="bank-mutation-card">

            <div class="bank-mutation-card-header">
                <h2 class="bank-mutation-card-title">
                    Filter Mutasi
                </h2>
            </div>

            <div class="bank-mutation-card-body">

                <form
                    method="GET"
                    action="{{ route('settings.bank-mutations.index') }}"
                >

                    <div class="filter-grid">

                        {{-- REKENING --}}
                        <div class="filter-field">

                            <label
                                class="filter-label"
                                for="bank_account_id"
                            >
                                Rekening Bank
                            </label>

                            <select
                                id="bank_account_id"
                                name="bank_account_id"
                                class="filter-select"
                            >

                                <option value="">
                                    Semua rekening
                                </option>

                                @foreach($accounts as $account)

                                    <option
                                        value="{{ $account->id }}"
                                        @selected(
                                            ($filters['bank_account_id'] ?? '') == $account->id
                                        )
                                    >
                                        {{ $account->bank_name }}
                                        — {{ $account->account_number }}
                                        — {{ $account->currency?->code ?? '-' }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- STATUS --}}
                        <div class="filter-field">

                            <label
                                class="filter-label"
                                for="reconciliation_status"
                            >
                                Status Rekonsiliasi
                            </label>

                            <select
                                id="reconciliation_status"
                                name="reconciliation_status"
                                class="filter-select"
                            >

                                <option value="">
                                    Semua status
                                </option>

                                <option
                                    value="unmatched"
                                    @selected(
                                        ($filters['reconciliation_status'] ?? '') === 'unmatched'
                                    )
                                >
                                    Unmatched
                                </option>

                                <option
                                    value="matched"
                                    @selected(
                                        ($filters['reconciliation_status'] ?? '') === 'matched'
                                    )
                                >
                                    Matched
                                </option>

                                <option
                                    value="manual"
                                    @selected(
                                        ($filters['reconciliation_status'] ?? '') === 'manual'
                                    )
                                >
                                    Manual
                                </option>

                                <option
                                    value="ignored"
                                    @selected(
                                        ($filters['reconciliation_status'] ?? '') === 'ignored'
                                    )
                                >
                                    Ignored
                                </option>

                            </select>

                        </div>


                        {{-- DATE FROM --}}
                        <div class="filter-field">

                            <label
                                class="filter-label"
                                for="date_from"
                            >
                                Dari Tanggal
                            </label>

                            <input
                                type="date"
                                id="date_from"
                                name="date_from"
                                class="filter-input"
                                value="{{ $filters['date_from'] ?? '' }}"
                            >

                        </div>


                        {{-- DATE TO --}}
                        <div class="filter-field">

                            <label
                                class="filter-label"
                                for="date_to"
                            >
                                Sampai Tanggal
                            </label>

                            <input
                                type="date"
                                id="date_to"
                                name="date_to"
                                class="filter-input"
                                value="{{ $filters['date_to'] ?? '' }}"
                            >

                        </div>


                        {{-- SEARCH --}}
                        <div class="filter-field search-field">

                            <label
                                class="filter-label"
                                for="search"
                            >
                                Pencarian
                            </label>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                class="filter-input"
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Reference, deskripsi, external ID..."
                            >

                        </div>


                        {{-- ACTION --}}
                        <div class="filter-field">

                            <div class="filter-actions">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Terapkan Filter
                                </button>

                                <a
                                    href="{{ route('settings.bank-mutations.index') }}"
                                    class="btn btn-secondary"
                                >
                                    Reset
                                </a>

                            </div>

                        </div>

                    </div>

                </form>


                {{-- ACTIVE FILTER --}}
                @if(
                    ($filters['bank_account_id'] ?? '') ||
                    ($filters['reconciliation_status'] ?? '') ||
                    ($filters['date_from'] ?? '') ||
                    ($filters['date_to'] ?? '') ||
                    ($filters['search'] ?? '')
                )

                    <div class="active-filter-wrapper">

                        <span class="active-filter-label">
                            Filter aktif:
                        </span>

                        @if($filters['bank_account_id'] ?? '')
                            <span class="active-filter">
                                Rekening dipilih
                            </span>
                        @endif

                        @if($filters['reconciliation_status'] ?? '')
                            <span class="active-filter">
                                Status:
                                {{ ucfirst($filters['reconciliation_status']) }}
                            </span>
                        @endif

                        @if($filters['date_from'] ?? '')
                            <span class="active-filter">
                                Dari:
                                {{ $filters['date_from'] }}
                            </span>
                        @endif

                        @if($filters['date_to'] ?? '')
                            <span class="active-filter">
                                Sampai:
                                {{ $filters['date_to'] }}
                            </span>
                        @endif

                        @if($filters['search'] ?? '')
                            <span class="active-filter">
                                Cari:
                                {{ $filters['search'] }}
                            </span>
                        @endif

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- ================================================================
         DAFTAR MUTASI
         ================================================================ --}}
    <div class="bank-mutation-card table-card">

        <div class="bank-mutation-card-header">

            <h2 class="bank-mutation-card-title">
                Daftar Mutasi
            </h2>

        </div>


        @if($mutations->count())

            <div class="table-wrapper">

                <table class="mutation-table">

                    <thead>

                        <tr>
                            <th>Tanggal</th>
                            <th>Bank / Rekening</th>
                            <th>Deskripsi</th>
                            <th>Currency</th>
                            <th style="text-align:right;">
                                Debit
                            </th>
                            <th style="text-align:right;">
                                Credit
                            </th>
                            <th style="text-align:right;">
                                Balance
                            </th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($mutations as $mutation)

                            @php
                                $status = $mutation->reconciliation_status ?? 'unmatched';

                                $statusClass = match($status) {
                                    'matched' => 'status-matched',
                                    'manual' => 'status-manual',
                                    'ignored' => 'status-ignored',
                                    default => 'status-unmatched',
                                };
                            @endphp


                            <tr>

                                {{-- DATE --}}
                                <td>

                                    <div class="mutation-date">
                                        {{ optional($mutation->transaction_date)->format('d/m/Y') }}
                                    </div>

                                    <div class="mutation-time">
                                        {{ optional($mutation->transaction_date)->format('H:i') }}
                                    </div>

                                </td>


                                {{-- BANK --}}
                                <td>

                                    <div class="bank-name">
                                        {{ $mutation->bankAccount?->bank_name ?? '-' }}
                                    </div>

                                    <div class="account-number">
                                        {{ $mutation->bankAccount?->account_number ?? '-' }}
                                    </div>

                                </td>


                                {{-- DESCRIPTION --}}
                                <td>

                                    <div class="description">
                                        {{ $mutation->description ?: '-' }}
                                    </div>

                                    @if($mutation->reference)

                                        <div class="reference">
                                            Ref: {{ $mutation->reference }}
                                        </div>

                                    @endif

                                </td>


                                {{-- CURRENCY --}}
                                <td>

                                    <span class="currency-badge">
                                        {{ $mutation->bankAccount?->currency?->code ?? '-' }}
                                    </span>

                                </td>


                                {{-- DEBIT --}}
                                <td class="amount">

                                    @if((float) $mutation->debit > 0)

                                        <span class="amount-debit">
                                            {{ number_format(
                                                (float) $mutation->debit,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        <span class="amount-zero">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- CREDIT --}}
                                <td class="amount">

                                    @if((float) $mutation->credit > 0)

                                        <span class="amount-credit">
                                            {{ number_format(
                                                (float) $mutation->credit,
                                                2,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        <span class="amount-zero">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- BALANCE --}}
                                <td class="amount">

                                    @if($mutation->balance !== null)

                                        {{ number_format(
                                            (float) $mutation->balance,
                                            2,
                                            ',',
                                            '.'
                                        ) }}

                                    @else

                                        <span class="amount-zero">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- STATUS --}}
                                <td>

                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst($status) }}
                                    </span>

                                </td>


                                {{-- SOURCE --}}
                                <td>

                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $mutation->source ?? 'manual'
                                        )
                                    ) }}

                                    @if($mutation->external_id)

                                        <div class="source-text">
                                            ID: {{ $mutation->external_id }}
                                        </div>

                                    @endif

                                </td>


                                {{-- ACTION --}}
                                <td>

                                    <a
                                        href="{{ route(
                                            'settings.bank-accounts.mutations.show',
                                            [
                                                'bankAccount' => $mutation->bank_account_id,
                                                'mutation' => $mutation->id,
                                            ]
                                        ) }}"
                                        class="detail-link"
                                    >
                                        Detail
                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if($mutations->hasPages())

                <div class="pagination-wrapper">

                    {{ $mutations->withQueryString()->links() }}

                </div>

            @endif


        @else

            <div class="empty-state">

                <div class="empty-state-title">
                    Belum ada mutasi bank.
                </div>

                <div>
                    Tidak ada data yang sesuai dengan filter yang dipilih.
                </div>

            </div>

        @endif

    </div>

</div>
@endsection
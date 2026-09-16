@extends('layouts.app')

@section('content')

<style>
    .teller-page {
        padding: 24px;
        background: #f5f7fb;
        min-height: calc(100vh - 70px);
    }

    .teller-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .teller-title {
        font-size: 26px;
        font-weight: 700;
        margin: 0;
        color: #182230;
    }

    .teller-subtitle {
        margin-top: 4px;
        color: #6b7280;
        font-size: 14px;
    }

    .teller-primary-btn {
        border: 0;
        border-radius: 10px;
        padding: 11px 18px;
        font-weight: 600;
        background: #111827;
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .teller-primary-btn:hover {
        color: white;
        opacity: .9;
    }

    .teller-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .teller-stat {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
    }

    .teller-stat-label {
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .teller-stat-value {
        font-size: 25px;
        font-weight: 700;
        color: #111827;
    }

    .teller-stat-note {
        margin-top: 5px;
        font-size: 12px;
        color: #9ca3af;
    }

    .teller-filter {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
    }

    .teller-filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .teller-filter .form-control,
    .teller-filter .form-select {
        min-height: 42px;
        border-radius: 9px;
        border-color: #dfe3e8;
    }

    .teller-filter .form-control:focus,
    .teller-filter .form-select:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, .15);
    }

    .teller-table-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
    }

    .teller-table-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f3;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .teller-table-title {
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .teller-table-count {
        color: #6b7280;
        font-size: 12px;
    }

    .teller-table {
        margin: 0;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .teller-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 7px 12px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .teller-table tbody td {
        padding: 7px 12px;
        border-bottom: 1px solid #f0f2f5;
        vertical-align: middle;
        color: #334155;
        font-size: 12px;
    }

    .teller-table tbody tr:hover {
        background: #fafbfc;
    }

    .teller-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .transaction-no {
        font-weight: 700;
        color: #111827;
        font-size: 12px;
    }

    .transaction-id {
        font-size: 9px;
        color: #9ca3af;
        margin-top: 3px;
        word-break: break-all;
    }

    .customer-name {
        font-weight: 600;
        color: #1f2937;
    }

    .customer-number {
        font-size: 9px;
        color: #94a3b8;
        margin-top: 3px;
    }

    .item-row {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 2px;
    }

    .item-row:last-child {
        margin-bottom: 0;
    }

    .direction-badge {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 5px;
        border-radius: 5px;
        min-width: 36px;
        text-align: center;
    }

    .direction-buy {
        background: #dcfce7;
        color: #166534;
    }

    .direction-sell {
        background: #fee2e2;
        color: #991b1b;
    }

    .currency-code {
        font-weight: 700;
        color: #1e293b;
    }

    .quantity {
        color: #475569;
    }

    .settlement-line {
        margin-bottom: 2px;
    }

    .settlement-line:last-child {
        margin-bottom: 0;
    }

    .settlement-label {
        color: #94a3b8;
        font-size: 9px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .settlement-value {
        font-weight: 600;
        color: #334155;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 7px;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-paid {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-cancelled {
        background: #f1f5f9;
        color: #475569;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-draft {
        background: #f1f5f9;
        color: #475569;
    }

    .settlement-status {
        font-size: 9px;
        color: #94a3b8;
        margin-top: 5px;
    }

    .teller-action {
        border: 1px solid #dbe1e8;
        background: white;
        color: #334155;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 9px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .teller-action:hover {
        background: #f8fafc;
        color: #111827;
    }

    .teller-empty {
        padding: 70px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .teller-empty-title {
        font-weight: 600;
        color: #64748b;
        margin-bottom: 2px;
    }

    .teller-pagination {
        padding: 16px 20px;
        border-top: 1px solid #edf0f3;
    }

    @media (max-width: 1100px) {
        .teller-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .teller-page {
            padding: 14px;
        }

        .teller-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .teller-stat-grid {
            grid-template-columns: 1fr 1fr;
        }

        .teller-table-card {
            overflow-x: auto;
        }

        .teller-table {
            min-width: 1000px;
        }
    }

    @media (max-width: 480px) {
        .teller-stat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="teller-page">

    {{-- HEADER --}}
    <div class="teller-header">

        <div>
            <h1 class="teller-title">Teller</h1>
            <div class="teller-subtitle">
                Operasional transaksi Money Changer
            </div>
        </div>

        <a href="#" class="teller-primary-btn">
            <span>+</span>
            <span>Transaksi Baru</span>
        </a>

    </div>


    {{-- KPI --}}
    @php
        $todayTransactions = $transactions->total();

        $pendingCount = $transactions->getCollection()
            ->where('status', 'pending_payment')
            ->count();

        $paidCount = $transactions->getCollection()
            ->where('status', 'paid')
            ->count();

        $completedCount = $transactions->getCollection()
            ->where('status', 'completed')
            ->count();
    @endphp

    <div class="teller-stat-grid">

        <div class="teller-stat">
            <div class="teller-stat-label">
                Transaksi
            </div>

            <div class="teller-stat-value">
                {{ number_format($todayTransactions, 0, ',', '.') }}
            </div>

            <div class="teller-stat-note">
                Hasil pencarian aktif
            </div>
        </div>

        <div class="teller-stat">
            <div class="teller-stat-label">
                Pending Payment
            </div>

            <div class="teller-stat-value">
                {{ number_format($pendingCount, 0, ',', '.') }}
            </div>

            <div class="teller-stat-note">
                Menunggu pembayaran
            </div>
        </div>

        <div class="teller-stat">
            <div class="teller-stat-label">
                Paid
            </div>

            <div class="teller-stat-value">
                {{ number_format($paidCount, 0, ',', '.') }}
            </div>

            <div class="teller-stat-note">
                Pembayaran terkonfirmasi
            </div>
        </div>

        <div class="teller-stat">
            <div class="teller-stat-label">
                Completed
            </div>

            <div class="teller-stat-value">
                {{ number_format($completedCount, 0, ',', '.') }}
            </div>

            <div class="teller-stat-note">
                Transaksi selesai
            </div>
        </div>

    </div>


    {{-- FILTER --}}
    <div class="teller-filter">

        <form method="GET" action="{{ route('teller.index') }}">

            <div class="row g-3 align-items-end">

                <div class="col-lg-5">

                    <div class="teller-filter-label">
                        Cari Transaksi / Customer
                    </div>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Nomor transaksi, nama, customer, nomor HP..."
                        value="{{ request('search') }}"
                    >

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="teller-filter-label">
                        Status
                    </div>

                    <select name="status" class="form-select">

                        <option value="">
                            Semua Status
                        </option>

                        <option value="draft"
                            {{ request('status') === 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>

                        <option value="pending_payment"
                            {{ request('status') === 'pending_payment' ? 'selected' : '' }}>
                            Pending Payment
                        </option>

                        <option value="paid"
                            {{ request('status') === 'paid' ? 'selected' : '' }}>
                            Paid
                        </option>

                        <option value="completed"
                            {{ request('status') === 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>

                        <option value="cancelled"
                            {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>

                        <option value="rejected"
                            {{ request('status') === 'rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>

                    </select>

                </div>

                <div class="col-lg-2 col-md-4">

                    <div class="teller-filter-label">
                        Tanggal
                    </div>

                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="{{ request('date') }}"
                    >

                </div>

                <div class="col-lg-3 col-md-4">

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-dark flex-fill"
                            style="min-height:42px;border-radius:9px;"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('teller.index') }}"
                            class="btn btn-outline-secondary"
                            style="min-height:42px;border-radius:9px;"
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>


    {{-- TRANSACTION TABLE --}}
    <div class="teller-table-card">

        <div class="teller-table-header">

            <div>

                <h5 class="teller-table-title">
                    Transaksi
                </h5>

                <div class="teller-table-count">
                    Menampilkan {{ $transactions->count() }}
                    dari {{ $transactions->total() }} transaksi
                </div>

            </div>

        </div>


        <div class="table-responsive">

            <table class="teller-table">

                <thead>

                    <tr>

                        <th style="width:180px;">
                            Transaksi
                        </th>

                        <th style="width:100px;">
                            Waktu
                        </th>

                        <th style="width:190px;">
                            Customer
                        </th>

                        <th style="width:210px;">
                            Item
                        </th>

                        <th style="width:180px;">
                            Settlement
                        </th>

                        <th style="width:130px;">
                            Status
                        </th>

                        <th style="width:80px;">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($transactions as $transaction)

                    <tr>

                        {{-- TRANSACTION --}}
                        <td>

                            <div class="transaction-no">
                                {{ $transaction->transaction_no }}
                            </div>

                            <div class="transaction-id">
                                {{ $transaction->id }}
                            </div>

                        </td>


                        {{-- TIME --}}
                        <td>

                            <div>
                                {{ optional($transaction->transaction_date)->format('d/m/Y') }}
                            </div>

                            <div class="transaction-id">
                                {{ optional($transaction->created_at)->format('H:i') }}
                            </div>

                        </td>


                        {{-- CUSTOMER --}}
                        <td>

                            @if($transaction->customer)

                                <div class="customer-name">
                                    {{ $transaction->customer->display_name ?: $transaction->customer->full_name }}
                                </div>

                                <div class="customer-number">
                                    {{ $transaction->customer->customer_number ?? '-' }}
                                </div>

                            @else

                                <div class="customer-name">
                                    Walk-in
                                </div>

                                <div class="customer-number">
                                    Tanpa customer
                                </div>

                            @endif

                        </td>


                        {{-- ITEMS --}}
                        <td>

                            @forelse($transaction->items as $item)

                                <div class="item-row">

                                    <span class="direction-badge
                                        {{ $item->direction === 'buy'
                                            ? 'direction-buy'
                                            : 'direction-sell' }}">

                                        {{ strtoupper($item->direction) }}

                                    </span>

                                    <span class="currency-code">
                                        {{ $item->currency->code ?? '-' }}
                                    </span>

                                    <span class="quantity">
                                        {{ number_format((float) $item->quantity, 4, ',', '.') }}
                                    </span>

                                </div>

                            @empty

                                <span class="transaction-id">
                                    Belum ada item
                                </span>

                            @endforelse

                        </td>


                        {{-- SETTLEMENT --}}
                        <td>

                            @forelse($transaction->settlements as $settlement)

                                <div class="settlement-line">

                                    <div class="settlement-label">

                                        {{ $settlement->direction === 'customer_pays'
                                            ? 'Customer Bayar'
                                            : 'Customer Terima'
                                        }}

                                    </div>

                                    <div class="settlement-value">

                                        {{ number_format(
                                            (float) $settlement->amount,
                                            2,
                                            ',',
                                            '.'
                                        ) }}

                                        {{ $settlement->currency->code ?? '' }}

                                    </div>

                                </div>

                            @empty

                                <span class="status-badge status-pending">
                                    Belum dibuat
                                </span>

                            @endforelse

                        </td>


                        {{-- STATUS --}}
                        <td>

                            @php

                                $statusClass = match ($transaction->status) {

                                    'completed' => 'status-completed',

                                    'paid' => 'status-paid',

                                    'pending_payment' => 'status-pending',

                                    'cancelled' => 'status-cancelled',

                                    'rejected' => 'status-rejected',

                                    default => 'status-draft',

                                };

                            @endphp

                            <span class="status-badge {{ $statusClass }}">

                                {{ ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $transaction->status
                                    )
                                ) }}

                            </span>

                            <div class="settlement-status">

                                Settlement:
                                {{ ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $transaction->settlement_status ?? 'pending'
                                    )
                                ) }}

                            </div>

                        </td>


                        {{-- ACTION --}}
                        <td>

                            <a
                                href="#"
                                class="teller-action"
                            >
                                Detail
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7">

                            <div class="teller-empty">

                                <div class="teller-empty-title">
                                    Belum ada transaksi
                                </div>

                                <div>
                                    Transaksi yang dibuat teller akan muncul di sini.
                                </div>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}
        @if($transactions->hasPages())

            <div class="teller-pagination">

                {{ $transactions->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
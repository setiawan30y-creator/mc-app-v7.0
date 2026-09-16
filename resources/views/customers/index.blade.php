@extends('layouts.app')

@section('title', 'Master Data Nasabah')
@section('page-title', 'Master Data Nasabah')

@section('content')
<style>
    .customer-page {
    font-size: 13px;
    width: 100%;
    }

    .customer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .customer-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #123b2a;
    }

    .customer-subtitle {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 12px;
    }

    .customer-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .customer-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 7px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .customer-btn:hover {
        background: #f9fafb;
    }

    .customer-btn-primary {
        background: #087443;
        border-color: #087443;
        color: #fff;
    }

    .customer-btn-primary:hover {
        background: #066238;
        color: #fff;
    }

    .customer-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        box-shadow: 0 2px 8px rgba(0,0,0,.035);
        overflow: hidden;
    }

    .customer-filter {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafcfb;
    }

    .customer-filter-form {
        display: flex;
        align-items: end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .customer-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .customer-field label {
        font-size: 11px;
        font-weight: 700;
        color: #4b5563;
    }

    .customer-input,
    .customer-select {
        height: 34px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 9px;
        background: #fff;
        color: #111827;
        font-size: 12px;
        outline: none;
    }

    .customer-input {
        width: 300px;
    }
.customer-search-field {
    flex: 1 1 280px;
}

.customer-city-input {
    width: 160px;
}

.customer-filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    margin-left: auto;
    white-space: nowrap;
}

.customer-add-btn {
    white-space: nowrap;
}
    .customer-select {
        width: 150px;
    }

    .customer-input:focus,
    .customer-select:focus {
        border-color: #087443;
        box-shadow: 0 0 0 2px rgba(8,116,67,.08);
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .customer-table {
        width: max-content;
        min-width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
        font-size: 12px;
    }

    .customer-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px 9px;
        background: #edf5f0;
        color: #16452f;
        border-bottom: 1px solid #cfded5;
        border-right: 1px solid #dce7e1;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
    }

    .customer-table td {
        padding: 7px 9px;
        border-bottom: 1px solid #edf0ee;
        border-right: 1px solid #f0f2f1;
        color: #374151;
        vertical-align: middle;
    }

    .customer-table tbody tr:hover {
        background: #f8fbf9;
    }

    .customer-table td.number {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .customer-table td.center {
        text-align: center;
    }

    .customer-name {
        font-weight: 700;
        color: #17251e;
    }

    .customer-muted {
        color: #9ca3af;
    }

    .customer-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 6px;
        border-radius: 999px;
        background: #eef5f1;
        color: #17603d;
        font-size: 10px;
        font-weight: 700;
    }

    /* =========================================================
       CUSTOMER RISK
       ========================================================= */
    .customer-risk-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 72px;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .customer-risk-dot {
        width: 6px;
        height: 6px;
        flex: 0 0 6px;
        border-radius: 50%;
    }

    .customer-risk-low {
        color: #176b4d;
        background: #e9f7ef;
        border: 1px solid #c9e8d5;
    }

    .customer-risk-low .customer-risk-dot {
        background: #21a366;
    }

    .customer-risk-medium {
        color: #8a620d;
        background: #fff7df;
        border: 1px solid #efdca8;
    }

    .customer-risk-medium .customer-risk-dot {
        background: #c58a18;
    }

    .customer-risk-high {
        color: #a23830;
        background: #fff0ee;
        border: 1px solid #efcbc7;
    }

    .customer-risk-high .customer-risk-dot {
        background: #c24a3d;
    }

    .customer-risk-neutral {
        color: #6b7280;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }

    .customer-risk-score {
        margin-left: 2px;
        font-size: 9px;
        font-weight: 900;
    }

    .customer-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        color: #6b7280;
        font-size: 11px;
    }

    .customer-pagination {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .customer-pagination a,
    .customer-pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 27px;
        height: 27px;
        padding: 0 7px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        font-size: 11px;
    }

    .customer-pagination .active {
        background: #087443;
        border-color: #087443;
        color: #fff;
    }

    .customer-alert {
        margin-bottom: 12px;
        padding: 10px 12px;
        border-radius: 7px;
        font-size: 12px;
    }

    .customer-alert-success {
        background: #ecfdf3;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .customer-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    @media (max-width: 800px) {
        .customer-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .customer-input {
            width: 100%;
        }

        .customer-select {
            width: 140px;
        }

        .customer-filter-form {
            align-items: stretch;
        }
    }

    .customer-filter-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 7px;
    }

    .customer-filter-column {
        width: 180px;
    }

    .customer-filter-operator {
        width: 150px;
    }

    .customer-filter-value {
        flex: 1;
        min-width: 220px;
        height: 34px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 9px;
        background: #fff;
        color: #111827;
        font-size: 12px;
        outline: none;
    }

    .customer-filter-column,
    .customer-filter-operator {
        height: 34px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 9px;
        background: #fff;
        color: #111827;
        font-size: 12px;
        outline: none;
    }

    .customer-filter-column:focus,
    .customer-filter-operator:focus,
    .customer-filter-value:focus {
        border-color: #087443;
        box-shadow: 0 0 0 2px rgba(8,116,67,.08);
    }

    .customer-filter-remove {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 6px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }

    .customer-filter-remove:hover {
        background: #fee2e2;
    }

    .customer-filter-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
    }

    .customer-add-btn {
        margin-left: auto;
        white-space: nowrap;
    }


    .customer-row-actions {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .customer-row-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 28px;
        padding: 3px 7px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 10px;
        font-weight: 700;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
    }

    .customer-row-preview:hover {
        background: #f3f4f6;
    }

    .customer-row-wa {
        background: #eaf7ef;
        border-color: #b9dfc8;
        color: #176b4d;
    }

    .customer-row-wa:hover {
        background: #dff2e7;
    }

    .customer-filter-actions {
        flex-wrap: wrap;
    }

/* =========================================================
   CUSTOMER TABLE - STICKY ACTION FRAME
   ========================================================= */

.customer-table-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    position: relative;
    border-radius: 8px;
}

/* Kolom aksi selalu menempel di kanan */
.customer-action-column {
    position: sticky !important;
    right: 0;
    z-index: 20;
    min-width: 132px;
    width: 132px;
    max-width: 132px;
    background: #ffffff !important;
    border-left: 2px solid #d8e9e1 !important;
    box-shadow: -8px 0 16px rgba(22, 61, 48, 0.08);
}

/* Header aksi */
.customer-action-header {
    position: sticky !important;
    right: 0;
    z-index: 30;
    min-width: 132px;
    width: 132px;
    max-width: 132px;
    background: linear-gradient(180deg, #f1f8f5 0%, #e7f2ed 100%) !important;
    border-left: 2px solid #c9dfd5 !important;
    box-shadow: -8px 0 16px rgba(22, 61, 48, 0.08);
    text-align: center !important;
}

/* Frame tombol */
.customer-action-frame {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 44px;
    padding: 5px 7px;
}

/* Tombol aksi */
.customer-action-btn {
    width: 34px;
    height: 34px;
    min-width: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid transparent;
    text-decoration: none;
    cursor: pointer;
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        filter 0.15s ease;
    position: relative;
}

/* Icon SVG */
.customer-action-btn svg {
    width: 17px;
    height: 17px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Edit */
.customer-action-edit {
    background: linear-gradient(135deg, #198754, #27a66f);
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(25, 135, 84, 0.22);
}

.customer-action-edit:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    box-shadow: 0 5px 12px rgba(25, 135, 84, 0.30);
}

/* Preview */
.customer-action-preview {
    background: linear-gradient(135deg, #5367d9, #7355d8);
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(83, 103, 217, 0.22);
}

.customer-action-preview:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    box-shadow: 0 5px 12px rgba(83, 103, 217, 0.30);
}

/* WhatsApp */
.customer-action-wa {
    background: linear-gradient(135deg, #16a765, #20c978);
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(22, 167, 101, 0.22);
}

.customer-action-wa:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    box-shadow: 0 5px 12px rgba(22, 167, 101, 0.30);
}

/* Tooltip */
.customer-action-btn::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 7px);
    left: 50%;
    transform: translateX(-50%) translateY(3px);
    background: #163d30;
    color: #ffffff;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    padding: 5px 8px;
    border-radius: 5px;
    opacity: 0;
    pointer-events: none;
    transition: all 0.15s ease;
    z-index: 100;
}

.customer-action-btn:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Supaya baris di belakang frame tidak terlihat */
.customer-action-column::before {
    content: "";
    position: absolute;
    inset: 0;
    background: #ffffff;
    z-index: -1;
}

/* Header icon */
.customer-action-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 5px;
    vertical-align: middle;
}

.customer-action-header-icon svg {
    width: 16px;
    height: 16px;
    stroke: #176b4d;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Mobile */
@media (max-width: 768px) {
    .customer-action-column,
    .customer-action-header {
        min-width: 124px;
        width: 124px;
        max-width: 124px;
    }

    .customer-action-frame {
        gap: 4px;
        padding: 5px;
    }

    .customer-action-btn {
        width: 32px;
        height: 32px;
        min-width: 32px;
    }

    .customer-action-btn svg {
        width: 16px;
        height: 16px;
    }
}

</style>

<div class="customer-page">

    @if(session('success'))
        <div class="customer-alert customer-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="customer-alert customer-alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="customer-card">

        {{-- FILTER --}}
        <div class="customer-filter">

            @php
                $filterColumns = [
                    'id_nasabah'      => 'ID_Nasabah',
                    'idpjk'           => 'IDPJK',
                    'customer_number' => 'Kode Nasabah',
                    'full_name'      => 'Nama',
                    'tempat_lahir'   => 'Tempat_Lahir',
                    'birth_date'     => 'Tanggal_Lahir',
                    'address'       => 'Alamat',
                    'warga_negara'  => 'Warga_Negara',
                    'jenis_kelamin' => 'Jenis_Kelamin',
                    'pekerjaan'     => 'Pekerjaan',
                    'phone'         => 'No_HP',
                    'no_rekening'   => 'No_Rekening',
                    'jenis_id'      => 'Jenis ID',
                    'no_ktp'        => 'No_KTP',
                    'selain_ktp'    => 'Selain_KTP',
                    'no_cif'        => 'No_CIF',
                    'npwp'          => 'NPWP',
                    'local_id'      => 'Local_ID',
                    'tgl_daftar'    => 'Tgl_Daftar',
                ];

                $filterOperators = [
                    'contains'    => 'Mengandung',
                    'equals'      => 'Sama dengan',
                    'starts_with' => 'Diawali',
                    'ends_with'   => 'Diakhiri',
                ];

                $activeFilters = request('filters', []);

                if (!is_array($activeFilters) || empty($activeFilters)) {
                    $activeFilters = [
                        [
                            'column'   => 'full_name',
                            'operator' => 'contains',
                            'value'    => '',
                        ],
                    ];
                }
            @endphp

            <form method="GET" action="{{ route('customers.index') }}" id="customerFilterForm">

                <div id="customerFilterRows">

                    @foreach($activeFilters as $index => $filter)
                        <div class="customer-filter-row">

                            <select
                                name="filters[{{ $index }}][column]"
                                class="customer-filter-column"
                            >
                                @foreach($filterColumns as $column => $label)
                                    <option
                                        value="{{ $column }}"
                                        @selected(($filter['column'] ?? 'full_name') === $column)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <select
                                name="filters[{{ $index }}][operator]"
                                class="customer-filter-operator"
                            >
                                @foreach($filterOperators as $operator => $label)
                                    <option
                                        value="{{ $operator }}"
                                        @selected(($filter['operator'] ?? 'contains') === $operator)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <input
                                type="text"
                                name="filters[{{ $index }}][value]"
                                class="customer-filter-value"
                                value="{{ $filter['value'] ?? '' }}"
                                placeholder="Masukkan nilai..."
                            >

                            <button
                                type="button"
                                class="customer-filter-remove"
                                title="Hapus filter"
                                onclick="removeCustomerFilter(this)"
                            >
                                ×
                            </button>

                        </div>
                    @endforeach

                </div>

                <div class="customer-filter-actions">

                    <button
                        type="button"
                        class="customer-btn"
                        id="addCustomerFilter"
                    >
                        + Tambah Filter
                    </button>

                    <a href="{{ route('customers.export.excel', request()->query()) }}" class="customer-btn">
                        Excel
                    </a>

                    <a href="{{ route('customers.export.pdf', request()->query()) }}" class="customer-btn">
                        PDF
                    </a>

                    @if(auth()->user()->hasPermission('customer.create'))
                        <button
                            type="button"
                            class="customer-btn"
                            id="customerImportButton"
                        >
                            Import Excel
                        </button>

                        <form
                            method="POST"
                            action="{{ route('customers.import.excel') }}"
                            enctype="multipart/form-data"
                            id="customerImportForm"
                            style="display:none;"
                        >
                            @csrf
                            <input
                                type="file"
                                name="excel_file"
                                id="customerImportFile"
                                accept=".xlsx,.xls,.csv"
                            >
                        </form>
                    @endif

                    <button
                        type="submit"
                        class="customer-btn customer-btn-primary"
                    >
                        Cari
                    </button>

                    @if(request()->filled('filters'))
                        <a
                            href="{{ route('customers.index') }}"
                            class="customer-btn"
                        >
                            Reset
                        </a>
                    @endif

                    <a
                        href="{{ route('customers.whatsapp.all', request()->query()) }}"
                        class="customer-btn"
                    >
                        WA Semua
                    </a>

                    @if(auth()->user()->hasPermission('customer.create'))
                        <a
                            href="{{ route('customers.create') }}"
                            class="customer-btn customer-btn-primary customer-add-btn"
                        >
                            + Tambah Nasabah
                        </a>
                    @endif

                </div>

            </form>

        </div>

        {{-- TABLE --}}
        <div class="table-wrapper">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>ID_Nasabah</th>
                        <th>IDPJK</th>
                        <th>Kode Nasabah</th>
                        <th>Nama</th>
                        <th>Tempat_Lahir</th>
                        <th>Tanggal_Lahir</th>
                        <th>Alamat</th>
                        <th>Warga_Negara</th>
                        <th>Jenis_Kelamin</th>
                        <th>Pekerjaan</th>
                        <th>Risiko</th>
                        <th>No_HP</th>
                        <th>No_Rekening</th>
                        <th>Jenis ID</th>
                        <th>No_KTP</th>
                        <th>Selain_KTP</th>
                        <th>No_CIF</th>
                        <th>NPWP</th>
                        <th>Local_ID</th>
                        <th>Tgl_Daftar</th>
                        <th class="customer-action-header">
                            <span class="customer-action-header-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="13" rx="2"></rect>
                                    <path d="M8 21h8"></path>
                                    <path d="M12 17v4"></path>
                                </svg>
                            </span>
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('customers.edit', $customer) }}" title="Edit Customer" style="display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:5px;background:#e8f3ed;color:#176b4d;font-weight:700;text-decoration:none;font-size:11px;">
                                    {{ $customer->id_nasabah ?: '-' }}
                                </a>
                            </td>
                            <td>{{ $customer->idpjk ?: '-' }}</td>
                            <td>{{ $customer->customer_number ?: '-' }}</td>
                            <td><span class="customer-name">{{ $customer->full_name ?: '-' }}</span></td>
                            <td>{{ $customer->tempat_lahir ?: '-' }}</td>
                            <td>{{ $customer->birth_date?->format('d M Y') ?: '-' }}</td>
                            <td>{{ $customer->address ?: '-' }}</td>
                            <td class="center">
                                @if($customer->warga_negara)
                                    <span class="customer-badge">{{ strtoupper($customer->warga_negara) }}</span>
                                @else
                                    <span class="customer-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ match($customer->jenis_kelamin) { 'L' => 'Laki-Laki', 'P' => 'Perempuan', default => $customer->jenis_kelamin ?: '-' } }}
                            </td>
                            <td>{{ $customer->pekerjaan ?: '-' }}</td>
                            <td class="center">
                                @if($customer->overall_risk)
                                    @php
                                        $riskLevel = strtolower((string) $customer->overall_risk->risk_level);
                                        $riskClass = match($riskLevel) {
                                            'high' => 'high',
                                            'medium' => 'medium',
                                            default => 'low',
                                        };
                                    @endphp

                                    <span class="customer-risk-badge customer-risk-{{ $riskClass }}">
                                        <span class="customer-risk-dot"></span>
                                        {{ ucfirst($riskLevel) }}
                                        <strong class="customer-risk-score">
                                            {{ $customer->overall_risk->risk_score }}
                                        </strong>
                                    </span>
                                @else
                                    <span class="customer-risk-badge customer-risk-neutral">
                                        Belum dinilai
                                    </span>
                                @endif
                            </td>
                            <td>{{ $customer->phone ?: '-' }}</td>
                            <td>{{ $customer->no_rekening ?: '-' }}</td>
                            <td>{{ $customer->jenis_id_label }}</td>
                            <td>{{ $customer->no_ktp ?: '-' }}</td>
                            <td>{{ $customer->selain_ktp ?: '-' }}</td>
                            <td>{{ $customer->no_cif ?: '-' }}</td>
                            <td>{{ $customer->npwp ?: '-' }}</td>
                            <td>{{ $customer->local_id ?: '-' }}</td>
                            <td>{{ $customer->tgl_daftar?->format('d/m/Y') ?: '-' }}</td>
                            <td class="customer-action-column">
                                <div class="customer-action-frame">
                                    <a href="{{ route('customers.edit', $customer) }}" class="customer-action-btn customer-action-edit" data-tooltip="Edit Customer" title="Edit Customer" aria-label="Edit Customer">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('customers.preview', $customer) }}" class="customer-action-btn customer-action-preview" data-tooltip="Preview Customer" title="Preview Customer" aria-label="Preview Customer">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    @if($customer->phone)
                                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', preg_replace('/^0/', '62', $customer->phone)) }}" target="_blank" rel="noopener" class="customer-action-btn customer-action-wa" data-tooltip="WhatsApp" title="WhatsApp Customer" aria-label="WhatsApp Customer">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4.1A8 8 0 1 1 20 11.5Z"></path>
                                                <path d="M9 8.5c.2-.4.5-.5.8-.5h.5c.2 0 .4.1.5.4l.7 1.7c.1.3.1.5-.1.7l-.6.7c.5 1 1.3 1.8 2.3 2.3l.7-.6c.2-.2.4-.2.7-.1l1.7.7c.3.1.4.3.4.5v.5c0 .3-.1.6-.5.8-.4.2-1 .3-1.5.1-2.7-.8-4.8-2.9-5.6-5.6-.2-.5-.1-1.1.1-1.5Z"></path>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="customer-action-btn" style="background:#eef2f0;color:#a0aaa5;cursor:not-allowed;" data-tooltip="No. HP tidak tersedia" title="No. HP tidak tersedia">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 3l18 18"></path>
                                                <path d="M8.5 8.5A8 8 0 0 0 4 12s3.5 7 10 7c1.4 0 2.6-.3 3.7-.8"></path>
                                                <path d="M14.5 5.3A8.7 8.7 0 0 0 14 5c-6.5 0-10 7-10 7s1.2 2.4 3.3 4.2"></path>
                                                <path d="M12 9a3 3 0 0 1 3 3"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="21" style="text-align:center;padding:30px;">
                                <div style="font-weight:700;color:#374151;">Belum ada data nasabah</div>
                                <div style="margin-top:4px;color:#9ca3af;">Silakan tambahkan nasabah baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="customer-footer">

            <div>
                Menampilkan
                <strong>{{ $customers->firstItem() ?? 0 }}</strong>
                -
                <strong>{{ $customers->lastItem() ?? 0 }}</strong>
                dari
                <strong>{{ $customers->total() }}</strong>
                nasabah
            </div>

            @if($customers->hasPages())
                <div class="customer-pagination">
                    @if($customers->onFirstPage())
                        <span>‹</span>
                    @else
                        <a href="{{ $customers->previousPageUrl() }}">‹</a>
                    @endif

                    @foreach($customers->getUrlRange(
                        max(1, $customers->currentPage() - 2),
                        min($customers->lastPage(), $customers->currentPage() + 2)
                    ) as $page => $url)

                        @if($page == $customers->currentPage())
                            <span class="active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif

                    @endforeach

                    @if($customers->hasMorePages())
                        <a href="{{ $customers->nextPageUrl() }}">›</a>
                    @else
                        <span>›</span>
                    @endif
                </div>
            @endif

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterRows = document.getElementById('customerFilterRows');
    const addButton = document.getElementById('addCustomerFilter');

    if (!filterRows || !addButton) {
        return;
    }

    let filterIndex = {{ count($activeFilters) }};

    const columns = @json($filterColumns);
    const operators = @json($filterOperators);

    addButton.addEventListener('click', function () {

        const row = document.createElement('div');

        row.className = 'customer-filter-row';

        let columnOptions = '';

        Object.entries(columns).forEach(([value, label]) => {
            columnOptions += `
                <option value="${value}">${label}</option>
            `;
        });

        let operatorOptions = '';

        Object.entries(operators).forEach(([value, label]) => {
            operatorOptions += `
                <option value="${value}">${label}</option>
            `;
        });

        row.innerHTML = `
            <select
                name="filters[${filterIndex}][column]"
                class="customer-filter-column"
            >
                ${columnOptions}
            </select>

            <select
                name="filters[${filterIndex}][operator]"
                class="customer-filter-operator"
            >
                ${operatorOptions}
            </select>

            <input
                type="text"
                name="filters[${filterIndex}][value]"
                class="customer-filter-value"
                placeholder="Masukkan nilai..."
            >

            <button
                type="button"
                class="customer-filter-remove"
                title="Hapus filter"
                onclick="removeCustomerFilter(this)"
            >
                ×
            </button>
        `;

        filterRows.appendChild(row);
        filterIndex++;
    });

});

function removeCustomerFilter(button) {

    const row = button.closest('.customer-filter-row');

    if (!row) {
        return;
    }

    const rows = document.querySelectorAll('.customer-filter-row');

    if (rows.length <= 1) {
        const valueInput = row.querySelector('.customer-filter-value');

        if (valueInput) {
            valueInput.value = '';
        }

        return;
    }

    row.remove();
}
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const importButton = document.getElementById('customerImportButton');
    const importFile = document.getElementById('customerImportFile');
    const importForm = document.getElementById('customerImportForm');

    if (importButton && importFile && importForm) {
        importButton.addEventListener('click', function () {
            importFile.click();
        });

        importFile.addEventListener('change', function () {
            if (!this.files.length) {
                return;
            }

            if (confirm('Import file Excel ini ke Master Data Nasabah?')) {
                importForm.submit();
            } else {
                this.value = '';
            }
        });
    }
});
</script>

@endsection



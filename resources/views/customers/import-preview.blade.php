@extends('layouts.app')

@section('content')

<style>
    .import-page {
        max-width: 1250px;
        margin: 0 auto;
        padding: 20px;
    }

    .import-header {
        margin-bottom: 18px;
    }

    .import-header h1 {
        margin: 0;
        font-size: 23px;
        font-weight: 700;
    }

    .import-header p {
        margin: 5px 0 0;
        color: #6b7280;
        font-size: 12px;
    }

    .summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 16px;
    }

    .summary-card {
        border: 1px solid #e2e8e5;
        border-radius: 8px;
        background: #fff;
        padding: 13px 15px;
    }

    .summary-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
    }

    .summary-value {
        margin-top: 3px;
        font-size: 21px;
        font-weight: 700;
    }

    .summary-valid {
        color: #166534;
    }

    .summary-duplicate {
        color: #b45309;
    }

    .summary-error {
        color: #b91c1c;
    }

    .import-card {
        background: #fff;
        border: 1px solid #e2e8e5;
        border-radius: 9px;
        overflow: hidden;
    }

    .import-card-header {
        padding: 12px 15px;
        background: #f8faf9;
        border-bottom: 1px solid #e5e7eb;
    }

    .import-card-header h2 {
        margin: 0;
        font-size: 14px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        white-space: nowrap;
    }

    .preview-table th {
        background: #f1f5f3;
        color: #374151;
        text-align: left;
        padding: 8px 9px;
        border-bottom: 1px solid #dfe7e3;
        font-size: 10px;
    }

    .preview-table td {
        padding: 7px 9px;
        border-bottom: 1px solid #edf0ef;
        vertical-align: middle;
    }

    .preview-table tbody tr:hover {
        background: #fafcfb;
    }

    .status {
        display: inline-block;
        padding: 3px 7px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 700;
    }

    .status-valid {
        background: #dcfce7;
        color: #166534;
    }

    .status-duplicate {
        background: #fef3c7;
        color: #92400e;
    }

    .status-error {
        background: #fee2e2;
        color: #991b1b;
    }

    .error-text {
        color: #b91c1c;
        font-size: 10px;
    }

    .duplicate-text {
        color: #92400e;
        font-size: 10px;
    }

    .actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 15px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
    }

    .actions-left,
    .actions-right {
        display: flex;
        gap: 8px;
    }

    .btn {
        border: 0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-primary {
        background: #166534;
        color: #fff;
    }

    .btn-disabled {
        background: #d1d5db;
        color: #6b7280;
        cursor: not-allowed;
    }

    @media (max-width: 800px) {
        .summary {
            grid-template-columns: repeat(2, 1fr);
        }

        .actions {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .actions-left,
        .actions-right {
            justify-content: stretch;
        }

        .btn {
            flex: 1;
            text-align: center;
        }
    }
</style>

<div class="import-page">

    <div class="import-header">
        <h1>Preview Import Nasabah</h1>
        <p>
            Data di bawah belum disimpan ke database. Periksa hasil validasi sebelum melanjutkan.
        </p>
    </div>

    <div class="summary">

        <div class="summary-card">
            <div class="summary-label">Total Data</div>
            <div class="summary-value">{{ number_format($total) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Valid</div>
            <div class="summary-value summary-valid">
                {{ number_format($validCount) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Duplikat</div>
            <div class="summary-value summary-duplicate">
                {{ number_format($duplicateCount) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Error</div>
            <div class="summary-value summary-error">
                {{ number_format($errorCount) }}
            </div>
        </div>

    </div>

    <div class="import-card">

        <div class="import-card-header">
            <h2>Hasil Pemeriksaan Data</h2>
        </div>

        <div class="table-wrap">

            <table class="preview-table">

                <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Status</th>
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>No KTP</th>
                        <th>No CIF</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($rows as $item)

                    <tr>

                        <td>{{ $item['row'] }}</td>

                        <td>

                            @if($item['status'] === 'valid')
                                <span class="status status-valid">
                                    VALID
                                </span>
                            @elseif($item['status'] === 'duplicate')
                                <span class="status status-duplicate">
                                    DUPLIKAT
                                </span>
                            @else
                                <span class="status status-error">
                                    ERROR
                                </span>
                            @endif

                        </td>

                        <td>
                            {{ $item['name'] ?: '-' }}
                        </td>

                        <td>
                            {{ $item['phone'] ?: '-' }}
                        </td>

                        <td>
                            {{ $item['ktp'] ?: '-' }}
                        </td>

                        <td>
                            {{ $item['cif'] ?: '-' }}
                        </td>

                        <td>

                            @if(!empty($item['errors']))

                                <div class="error-text">
                                    {{ implode(', ', $item['errors']) }}
                                </div>

                            @elseif($item['duplicate'])

                                <div class="duplicate-text">
                                    Sudah ada:
                                    {{ $item['duplicate']['customer_number'] }}
                                    -
                                    {{ $item['duplicate']['name'] }}
                                </div>

                            @else

                                <span style="color:#6b7280;">
                                    Siap diimport
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;">
                            Tidak ada data.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="actions">

            <div class="actions-left">

                <a
                    href="{{ route('customers.import') }}"
                    class="btn btn-secondary"
                >
                    ← Kembali
                </a>

            </div>

            <div class="actions-right">

                @if($validCount > 0)

                    <button
                        type="button"
                        class="btn btn-primary"
                        disabled
                        title="Tahap konfirmasi import akan diaktifkan berikutnya"
                    >
                        Import {{ number_format($validCount) }} Data
                    </button>

                @else

                    <button
                        type="button"
                        class="btn btn-disabled"
                        disabled
                    >
                        Tidak Ada Data Valid
                    </button>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection

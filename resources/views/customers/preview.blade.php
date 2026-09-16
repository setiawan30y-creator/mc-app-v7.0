@extends('layouts.app')

@section('title', 'Preview Nasabah')
@section('page-title', 'Preview Nasabah')

@section('content')
<style>
    .customer-preview {
        width: 100%;
        margin-left: 20px;
        margin-right: 20px;
    }

    .customer-preview-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }

    .customer-preview-head {
        padding: 18px 20px;
        background: #f4f9f6;
        border-bottom: 1px solid #dfe9e3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .customer-preview-name {
        margin: 0;
        font-size: 20px;
        color: #123b2a;
    }

    .customer-preview-subtitle {
        margin: 4px 0 0;
        font-size: 12px;
        color: #6b7280;
    }

    .customer-preview-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .customer-preview-btn {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 7px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }

    .customer-preview-btn.primary {
        background: #087443;
        border-color: #087443;
        color: #fff;
    }

    .customer-preview-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-preview-item {
        padding: 12px 16px;
        border-bottom: 1px solid #edf0ee;
        border-right: 1px solid #edf0ee;
    }

    .customer-preview-label {
        font-size: 10px;
        font-weight: 800;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .customer-preview-value {
        font-size: 13px;
        color: #17251e;
        word-break: break-word;
    }

    @media (max-width: 800px) {
        .customer-preview-grid {
            grid-template-columns: 1fr;
        }

        .customer-preview {
            margin-left: 0;
            margin-right: 0;
        }
    }
</style>

<div class="customer-preview">
    <div class="customer-preview-card">

        <div class="customer-preview-head">
            <div>
                <h1 class="customer-preview-name">{{ $customer->full_name ?: '-' }}</h1>
                <p class="customer-preview-subtitle">
                    {{ $customer->id_nasabah ?: '-' }} · {{ $customer->no_cif ?: '-' }}
                </p>
            </div>

            <div class="customer-preview-actions">
                <a href="{{ route('customers.index') }}" class="customer-preview-btn">
                    Kembali
                </a>

                <a href="{{ route('customers.edit', $customer) }}" class="customer-preview-btn primary">
                    Edit
                </a>

                @php
                    $waNumber = preg_replace('/\D+/', '', (string) $customer->phone);
                    if (str_starts_with($waNumber, '0')) {
                        $waNumber = '62' . substr($waNumber, 1);
                    }
                @endphp

                @if($waNumber)
                    <a
                        href="https://wa.me/{{ $waNumber }}"
                        target="_blank"
                        rel="noopener"
                        class="customer-preview-btn"
                    >
                        WhatsApp
                    </a>
                @endif
            </div>
        </div>

        <div class="customer-preview-grid">
            @php
                $fields = [
                    'ID Nasabah' => $customer->id_nasabah,
                    'IDPJK' => $customer->idpjk,
                    'Kode Nasabah' => $customer->customer_number,
                    'Nama' => $customer->full_name,
                    'Tempat Lahir' => $customer->tempat_lahir,
                    'Tanggal Lahir' => $customer\->birth_date?->format('d M Y'),
                    'Alamat' => $customer->address,
                    'Warga Negara' => $customer->warga_negara,
                    'Jenis Kelamin' => $customer->jenis_kelamin,
                    'Pekerjaan' => $customer->pekerjaan,
                    'No HP' => $customer->phone,
                    'No Rekening' => $customer->no_rekening,
                    'Jenis ID' => $customer->jenis_id_label,
                    'No KTP' => $customer->no_ktp,
                    'Selain KTP' => $customer->selain_ktp,
                    'No CIF' => $customer->no_cif,
                    'NPWP' => $customer->npwp,
                    'Local ID' => $customer->local_id,
                    'Tgl Daftar' => $customer->tgl_daftar?->format('d/m/Y'),
                    'Tipe Nasabah' => $customer->tipe_label,
                    'Status' => $customer->status,
                    'Status KYC' => $customer->kyc_status,
                ];
            @endphp

            @foreach($fields as $label => $value)
                <div class="customer-preview-item">
                    <div class="customer-preview-label">{{ $label }}</div>
                    <div class="customer-preview-value">{{ $value ?: '-' }}</div>
                </div>
            @endforeach

            <div class="customer-preview-item">
                <div class="customer-preview-label">Dokumen</div>
                <div class="customer-preview-value">
                    {{ $customer->document_path ? 'Tersedia' : 'Tidak ada dokumen' }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

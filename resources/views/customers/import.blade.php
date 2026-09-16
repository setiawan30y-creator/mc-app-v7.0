@extends('layouts.app')

@section('content')

<style>
    .import-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
    }

    .import-header {
        margin-bottom: 20px;
    }

    .import-header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }

    .import-header p {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 13px;
    }

    .import-card {
        background: #fff;
        border: 1px solid #dfe7e3;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .import-card-header {
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8faf9;
    }

    .import-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .import-card-header p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 12px;
    }

    .import-card-body {
        padding: 20px;
    }

    .upload-box {
        border: 2px dashed #b9cfc5;
        border-radius: 10px;
        padding: 35px 20px;
        text-align: center;
        background: #fbfdfc;
    }

    .upload-icon {
        font-size: 38px;
        margin-bottom: 10px;
    }

    .upload-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .upload-desc {
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 18px;
    }

    .file-input {
        display: none;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 7px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background: #166534;
        color: #fff;
    }

    .btn-secondary {
        background: #eef2f0;
        color: #374151;
    }

    .btn-outline {
        background: #fff;
        color: #166534;
        border: 1px solid #a9c2b6;
    }

    .selected-file {
        display: none;
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 7px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        font-size: 12px;
    }

    .selected-file.show {
        display: block;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .info-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        background: #fafafa;
    }

    .info-label {
        font-size: 10px;
        color: #6b7280;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .info-value {
        font-size: 13px;
        font-weight: 600;
    }

    .column-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 7px 20px;
        font-size: 12px;
        color: #374151;
    }

    .column-list span::before {
        content: "✓";
        color: #15803d;
        font-weight: 700;
        margin-right: 6px;
    }

    .actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 18px;
    }

    .actions-left,
    .actions-right {
        display: flex;
        gap: 8px;
    }

    @media (max-width: 800px) {
        .info-grid,
        .column-list {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
            align-items: stretch;
        }

        .actions-left,
        .actions-right {
            width: 100%;
        }

        .actions .btn {
            flex: 1;
        }
    }
</style>

<div class="import-page">

    <div class="import-header">
        <h1>Import Master Nasabah</h1>
        <p>
            Import data nasabah dari Excel atau CSV ke Master Data Nasabah Almara.
        </p>
    </div>

    <div class="import-card">

        <div class="import-card-header">
            <h2>1. Upload File</h2>
            <p>Pilih file Excel/CSV yang akan diperiksa sebelum proses import.</p>
        </div>

        <div class="import-card-body">

            <form
                id="customerImportWizardForm"
                action="{{ route('customers.import.preview') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="upload-box">

                    <div class="upload-icon">📊</div>

                    <div class="upload-title">
                        Pilih File Customer
                    </div>

                    <div class="upload-desc">
                        Format yang didukung: XLSX, XLS, CSV · Maksimal 20 MB
                    </div>

                    <label for="customerImportFile" class="btn btn-primary">
                        Pilih File Excel
                    </label>

                    <input
                        type="file"
                        name="excel_file"
                        id="customerImportFile"
                        class="file-input"
                        accept=".xlsx,.xls,.csv"
                    >

                    <div id="selectedFile" class="selected-file"></div>

                </div>

                <div class="actions">

                    <div class="actions-left">
                        <a
                            href="{{ route('customers.index') }}"
                            class="btn btn-secondary"
                        >
                            ← Kembali
                        </a>
                    </div>

                    <div class="actions-right">
                        <a
                            href="#"
                            class="btn btn-outline"
                            id="downloadTemplateButton"
                        >
                            ↓ Download Template
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="previewButton"
                            disabled
                        >
                            Preview & Validasi →
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="import-card">

        <div class="import-card-header">
            <h2>2. Informasi Import</h2>
            <p>Data akan diperiksa terlebih dahulu sebelum masuk ke database.</p>
        </div>

        <div class="import-card-body">

            <div class="info-grid">

                <div class="info-item">
                    <div class="info-label">Duplikat</div>
                    <div class="info-value">Otomatis diperiksa</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Tenant</div>
                    <div class="info-value">Tenant aktif</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Cabang</div>
                    <div class="info-value">Cabang aktif</div>
                </div>

            </div>

        </div>

    </div>

    <div class="import-card">

        <div class="import-card-header">
            <h2>Kolom yang Didukung</h2>
            <p>Kolom berikut akan digunakan dalam proses mapping import.</p>
        </div>

        <div class="import-card-body">

            <div class="column-list">
                <span>ID Nasabah</span>
                <span>IDPJK</span>
                <span>Kode Nasabah</span>
                <span>Nama</span>
                <span>Tipe</span>
                <span>Customer Type</span>
                <span>Tempat Lahir</span>
                <span>Tanggal Lahir</span>
                <span>Alamat</span>
                <span>Warga Negara</span>
                <span>Jenis Kelamin</span>
                <span>Pekerjaan</span>
                <span>No HP</span>
                <span>No Rekening</span>
                <span>Jenis ID</span>
                <span>No KTP</span>
                <span>Selain KTP</span>
                <span>No CIF</span>
                <span>NPWP</span>
                <span>Local ID</span>
                <span>Tgl Daftar</span>
                <span>Status</span>
                <span>KYC Status</span>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const fileInput = document.getElementById('customerImportFile');
    const selectedFile = document.getElementById('selectedFile');
    const previewButton = document.getElementById('previewButton');
    const form = document.getElementById('customerImportWizardForm');

    if (!fileInput) {
        return;
    }

    fileInput.addEventListener('change', function () {

        const file = this.files && this.files.length
            ? this.files[0]
            : null;

        if (!file) {
            selectedFile.classList.remove('show');
            selectedFile.textContent = '';
            previewButton.disabled = true;
            return;
        }

        const maxSize = 20 * 1024 * 1024;

        if (file.size > maxSize) {
            alert('Ukuran file maksimal 20 MB.');
            this.value = '';
            selectedFile.classList.remove('show');
            previewButton.disabled = true;
            return;
        }

        selectedFile.textContent =
            '✓ File dipilih: ' +
            file.name +
            ' (' +
            Math.round(file.size / 1024) +
            ' KB)';

        selectedFile.classList.add('show');
        previewButton.disabled = false;
    });

    form.addEventListener('submit', function () {

        if (!fileInput.files.length) {
            alert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        previewButton.disabled = true;
        previewButton.textContent = 'Memproses...';
    });

});
</script>

@endsection

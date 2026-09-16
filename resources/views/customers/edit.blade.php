@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
@php
    $monthNames = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    $birthDateDisplay = $customer->birth_date
        ? $customer->birth_date->format('d') . ' ' . $monthNames[(int) $customer->birth_date->format('n')] . ' ' . $customer->birth_date->format('Y')
        : '';

    $registrationDateDisplay = $customer->tgl_daftar
        ? $customer->tgl_daftar->format('d') . ' ' . $monthNames[(int) $customer->tgl_daftar->format('n')] . ' ' . $customer->tgl_daftar->format('Y')
        : '';

    $documentUrl = $customer->document_path
        ? \Illuminate\Support\Facades\Storage::url($customer->document_path)
        : null;

    $isPdf = $customer->document_path
        ? \Illuminate\Support\Str::endsWith(strtolower($customer->document_path), '.pdf')
        : false;
@endphp

<style>
    .customer-edit-page {
        padding: 18px;
    }

    .customer-edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 15px;
    }

    .customer-edit-title {
        margin: 0;
        font-size: 21px;
        font-weight: 700;
        color: #16382c;
    }

    .customer-edit-subtitle {
        margin: 3px 0 0;
        color: #718078;
        font-size: 12px;
    }

    .customer-edit-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-customer {
        border: 0;
        border-radius: 7px;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-back {
        background: #eef2ef;
        color: #385248;
    }

    .btn-save {
        background: #176b4d;
        color: #fff;
    }

    .btn-save:hover {
        background: #12563e;
    }

    .customer-workspace {
        display: grid;
        grid-template-columns: minmax(360px, 43%) minmax(0, 57%);
        gap: 14px;
        height: calc(100vh - 145px);
        min-height: 650px;
    }

    .customer-panel {
        background: #fff;
        border: 1px solid #dce6df;
        border-radius: 10px;
        min-height: 0;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(27, 59, 45, .05);
    }

    .customer-panel-header {
        height: 49px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 14px;
        border-bottom: 1px solid #e3ebe6;
        background: #f8fbf9;
    }

    .customer-panel-header strong {
        font-size: 13px;
        color: #24483a;
    }

    .customer-panel-header span {
        font-size: 11px;
        color: #7c8983;
    }

    .document-panel {
        display: flex;
        flex-direction: column;
    }

    .document-body {
        padding: 14px;
        overflow-y: auto;
        min-height: 0;
        flex: 1;
    }

    .document-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 10px;
    }

    .document-btn {
        border: 1px solid #cfdcd4;
        background: #f7faf8;
        color: #2c5142;
        border-radius: 7px;
        padding: 9px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
    }

    .document-btn:hover {
        background: #edf5f0;
    }

    .document-btn input {
        display: none;
    }

    .document-preview {
        width: 100%;
        min-height: 470px;
        border: 1px dashed #cbd8d0;
        border-radius: 8px;
        background: #f6f9f7;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .document-preview img {
        width: 100%;
        max-height: 680px;
        object-fit: contain;
        display: block;
    }

    .document-preview iframe {
        width: 100%;
        height: 680px;
        border: 0;
        display: block;
        background: #fff;
    }

    .document-placeholder {
        text-align: center;
        color: #87938d;
        padding: 30px;
    }

    .document-placeholder .icon {
        font-size: 38px;
        margin-bottom: 8px;
    }

    .document-placeholder strong {
        display: block;
        color: #65746c;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .document-placeholder span {
        font-size: 11px;
    }

    .document-name {
        background: #f1f5f2;
        border: 1px solid #dde6e0;
        border-radius: 6px;
        padding: 7px 9px;
        font-size: 11px;
        color: #5e6c65;
        margin-bottom: 10px;
        word-break: break-word;
    }

    .autofill-btn {
        width: 100%;
        border: 0;
        border-radius: 7px;
        padding: 10px;
        background: #d5a23a;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .autofill-btn:hover {
        background: #bc8b25;
    }

    .document-note {
        margin-top: 10px;
        font-size: 10px;
        line-height: 1.5;
        color: #7b8881;
    }

    .form-panel {
        display: flex;
        flex-direction: column;
    }

    .form-body {
        padding: 15px;
        overflow-y: auto;
        min-height: 0;
        flex: 1;
    }

    .form-section {
        margin-bottom: 17px;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #315b49;
        border-bottom: 1px solid #e5ece7;
        padding-bottom: 7px;
        margin-bottom: 10px;
    }

    .section-number {
        width: 21px;
        height: 21px;
        border-radius: 50%;
        background: #e7f1eb;
        color: #176b4d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px 11px;
    }

    .form-grid.three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #53625b;
        margin-bottom: 4px;
    }

    .required {
        color: #c25a4b;
    }

    .form-control,
    .form-select {
        width: 100%;
        height: 35px;
        border: 1px solid #d4ded8;
        border-radius: 6px;
        background: #fff;
        padding: 0 9px;
        font-size: 12px;
        color: #273b33;
        outline: none;
        box-sizing: border-box;
    }

    textarea.form-control {
        height: 70px;
        padding-top: 8px;
        resize: vertical;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #579879;
        box-shadow: 0 0 0 2px rgba(23, 107, 77, .08);
    }

    .readonly-field {
        background: #f3f6f4;
        color: #607069;
    }

    .identity-extra {
        display: none;
    }

    .identity-extra.active {
        display: block;
    }

    .date-wrapper {
        position: relative;
    }

    .date-display {
        cursor: pointer;
        background: #fff;
    }

    .date-native {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .alert-box {
        border-radius: 7px;
        padding: 9px 11px;
        font-size: 11px;
        margin-bottom: 12px;
    }

    .alert-error {
        background: #fff1ef;
        color: #9e3e33;
        border: 1px solid #f0c7c1;
    }

    .alert-success {
        background: #edf8f1;
        color: #25603f;
        border: 1px solid #c8e3d2;
    }

    .error-list {
        margin: 4px 0 0;
        padding-left: 17px;
    }

    .form-footer {
        border-top: 1px solid #e1e9e4;
        padding: 11px 14px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        background: #f9fbfa;
    }

    .current-document {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e9f3ed;
        color: #34614e;
        border-radius: 5px;
        padding: 4px 7px;
        font-size: 10px;
        font-weight: 700;
    }

    @media (max-width: 1050px) {
        .customer-workspace {
            grid-template-columns: 1fr;
            height: auto;
        }

        .document-panel,
        .form-panel {
            min-height: 650px;
        }

        .form-grid.three {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .customer-edit-page {
            padding: 10px;
        }

        .customer-edit-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .customer-edit-actions {
            width: 100%;
        }

        .customer-edit-actions .btn-customer {
            flex: 1;
        }

        .form-grid,
        .form-grid.three {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .document-preview {
            min-height: 350px;
        }
    }
</style>

<div class="customer-edit-page">

    <div class="customer-edit-header">
        <div>
            <h1 class="customer-edit-title">Edit Customer</h1>
            <p class="customer-edit-subtitle">
                Perbarui Master Customer —
                <strong>{{ $customer->id_nasabah ?: $customer->customer_number }}</strong>
            </p>
        </div>

        <div class="customer-edit-actions">
            <a href="{{ route('customers.index') }}" class="btn-customer btn-back">
                ← Kembali
            </a>

            <button type="submit" form="customer-edit-form" class="btn-customer btn-save">
                ✓ Simpan Perubahan
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-box alert-error">
            <strong>Data belum dapat disimpan.</strong>

            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert-box alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form
        id="customer-edit-form"
        action="{{ route('customers.update', $customer) }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="customer-workspace">

            {{-- =========================================================
                 PANEL KIRI — DOKUMEN
            ========================================================== --}}
            <div class="customer-panel document-panel">

                <div class="customer-panel-header">
                    <strong>Dokumen / Foto Identitas</strong>

                    @if ($customer->document_path)
                        <span class="current-document">Dokumen tersimpan</span>
                    @else
                        <span>Belum ada dokumen</span>
                    @endif
                </div>

                <div class="document-body">

                    <div class="document-actions">

                        <label class="document-btn">
                            📁 Pilih File
                            <input
                                type="file"
                                name="document"
                                id="documentInput"
                                accept="image/*,.pdf"
                            >
                        </label>

                        <label class="document-btn">
                            📷 Kamera
                            <input
                                type="file"
                                name="camera_document"
                                id="cameraInput"
                                accept="image/*"
                                capture="environment"
                            >
                        </label>

                    </div>

                    <div class="document-preview" id="documentPreview">

                        @if ($documentUrl && $isPdf)

                            <iframe
                                src="{{ $documentUrl }}"
                                title="Dokumen Customer"
                            ></iframe>

                        @elseif ($documentUrl)

                            <img
                                src="{{ $documentUrl }}"
                                alt="Dokumen Customer"
                                id="previewImage"
                            >

                        @else

                            <div class="document-placeholder">
                                <div class="icon">📄</div>
                                <strong>Belum ada dokumen</strong>
                                <span>Pilih file atau ambil foto menggunakan kamera.</span>
                            </div>

                        @endif

                    </div>

                    <div
                        class="document-name"
                        id="documentName"
                    >
                        @if ($customer->document_path)
                            Dokumen saat ini: {{ basename($customer->document_path) }}
                        @else
                            Belum ada file dipilih.
                        @endif
                    </div>

                    <button
                        type="button"
                        class="autofill-btn"
                        id="autoFillButton"
                    >
                        ✨ Auto Fill dari Dokumen
                    </button>

                    <div class="document-note">
                        Auto Fill/OCR akan digunakan untuk membaca data identitas
                        dari dokumen pada tahap KYC/OCR berikutnya.
                    </div>

                </div>
            </div>


            {{-- =========================================================
                 PANEL KANAN — MASTER DATA
            ========================================================== --}}
            <div class="customer-panel form-panel">

                <div class="customer-panel-header">
                    <strong>Master Data Customer</strong>
                    <span>Perubahan akan dicatat ke Audit Log</span>
                </div>

                <div class="form-body">

                    {{-- INFORMASI UTAMA --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span class="section-number">1</span>
                            Identitas Utama
                        </div>

                        <div class="form-grid three">

                            <div class="form-group">
                                <label class="form-label">ID_Nasabah</label>

                                <input
                                    type="text"
                                    class="form-control readonly-field"
                                    value="{{ old('id_nasabah', $customer->id_nasabah) }}"
                                    readonly
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">IDPJK</label>

                                <input
                                    type="text"
                                    name="idpjk"
                                    class="form-control"
                                    value="{{ old('idpjk', $customer->idpjk) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kode Nasabah</label>

                                <input
                                    type="text"
                                    class="form-control readonly-field"
                                    value="{{ $customer->customer_number }}"
                                    readonly
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Tipe Customer <span class="required">*</span>
                                </label>

                                <select
                                    name="tipe"
                                    id="customerType"
                                    class="form-select"
                                    required
                                >
                                    <option value="1" @selected(old('tipe', $customer->tipe) == 1)>
                                        Perorangan
                                    </option>

                                    <option value="2" @selected(old('tipe', $customer->tipe) == 2)>
                                        Perusahaan
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Nama <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    class="form-control"
                                    value="{{ old('full_name', $customer->full_name) }}"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nama Tampilan</label>

                                <input
                                    type="text"
                                    name="display_name"
                                    class="form-control"
                                    value="{{ old('display_name', $customer->display_name) }}"
                                    maxlength="255"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- DATA PRIBADI --}}
                    <div class="form-section" id="individualSection">

                        <div class="form-section-title">
                            <span class="section-number">2</span>
                            Data Pribadi
                        </div>

                        <div class="form-grid three">

                            <div class="form-group">
                                <label class="form-label">Tempat_Lahir</label>

                                <input
                                    type="text"
                                    name="tempat_lahir"
                                    class="form-control"
                                    value="{{ old('tempat_lahir', $customer->tempat_lahir) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tanggal_Lahir</label>

                                <div class="date-wrapper">
                                    <input
                                        type="text"
                                        id="birthDateDisplay"
                                        class="form-control date-display"
                                        value="{{ old('birth_date_display', $birthDateDisplay) }}"
                                        placeholder="dd Mmm yyyy"
                                        readonly
                                    >

                                    <input
                                        type="date"
                                        name="birth_date"
                                        id="birthDate"
                                        class="date-native"
                                        value="{{ old('birth_date', optional($customer->birth_date)->format('Y-m-d')) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Warga_Negara</label>

                                <input
                                    type="text"
                                    name="warga_negara"
                                    class="form-control"
                                    value="{{ old('warga_negara', $customer->warga_negara) }}"
                                    maxlength="100"
                                    placeholder="Contoh: Indonesia"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jenis_Kelamin</label>

                                <select
                                    name="jenis_kelamin"
                                    class="form-select"
                                >
                                    <option value="">- Pilih -</option>
                                    <option value="L" @selected(old('jenis_kelamin', $customer->jenis_kelamin) === 'L')>
                                        Laki-laki
                                    </option>
                                    <option value="P" @selected(old('jenis_kelamin', $customer->jenis_kelamin) === 'P')>
                                        Perempuan
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Pekerjaan</label>

                                <input
                                    type="text"
                                    name="pekerjaan"
                                    class="form-control"
                                    value="{{ old('pekerjaan', $customer->pekerjaan) }}"
                                    maxlength="150"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">No_HP</label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="{{ old('phone', $customer->phone) }}"
                                    maxlength="50"
                                >
                            </div>

                            <div class="form-group full">
                                <label class="form-label">Alamat</label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    maxlength="500"
                                >{{ old('address', $customer->address) }}</textarea>
                            </div>

                        </div>
                    </div>


                    {{-- DATA REKENING --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span class="section-number">3</span>
                            Rekening & Identitas
                        </div>

                        <div class="form-grid three">

                            <div class="form-group">
                                <label class="form-label">No_Rekening</label>

                                <input
                                    type="text"
                                    name="no_rekening"
                                    class="form-control"
                                    value="{{ old('no_rekening', $customer->no_rekening) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Jenis ID <span class="required">*</span>
                                </label>

                                <select
                                    name="jenis_id"
                                    id="identityType"
                                    class="form-select"
                                    required
                                >
                                    <option value="">- Pilih -</option>

                                    <option value="KTP" @selected(old('jenis_id', $customer->jenis_id) === 'KTP')>
                                        KTP
                                    </option>

                                    <option value="SIM" @selected(old('jenis_id', $customer->jenis_id) === 'SIM')>
                                        SIM
                                    </option>

                                    <option value="PASSPORT" @selected(old('jenis_id', $customer->jenis_id) === 'PASSPORT')>
                                        Passport
                                    </option>

                                    <option value="SERTIFIKAT" @selected(old('jenis_id', $customer->jenis_id) === 'SERTIFIKAT')>
                                        Sertifikat
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">No_KTP</label>

                                <input
                                    type="text"
                                    name="no_ktp"
                                    id="noKtp"
                                    class="form-control"
                                    value="{{ old('no_ktp', $customer->no_ktp) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group identity-extra" id="selainKtpGroup">
                                <label class="form-label">Selain_KTP</label>

                                <input
                                    type="text"
                                    name="selain_ktp"
                                    id="selainKtp"
                                    class="form-control"
                                    value="{{ old('selain_ktp', $customer->selain_ktp) }}"
                                    maxlength="255"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">No_CIF</label>

                                <input
                                    type="text"
                                    name="no_cif"
                                    class="form-control"
                                    value="{{ old('no_cif', $customer->no_cif) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">NPWP</label>

                                <input
                                    type="text"
                                    name="npwp"
                                    class="form-control"
                                    value="{{ old('npwp', $customer->npwp) }}"
                                    maxlength="100"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Local_ID</label>

                                <input
                                    type="text"
                                    name="local_id"
                                    class="form-control"
                                    value="{{ old('local_id', $customer->local_id) }}"
                                    maxlength="100"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- ADMINISTRASI --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span class="section-number">4</span>
                            Administrasi Customer
                        </div>

                        <div class="form-grid three">

                            <div class="form-group">
                                <label class="form-label">Tgl_Daftar</label>

                                <div class="date-wrapper">
                                    <input
                                        type="text"
                                        id="registrationDateDisplay"
                                        class="form-control date-display readonly-field"
                                        value="{{ old('tgl_daftar_display', $registrationDateDisplay) }}"
                                        readonly
                                    >

                                    <input
                                        type="date"
                                        name="tgl_daftar"
                                        id="registrationDate"
                                        class="date-native"
                                        value="{{ old('tgl_daftar', optional($customer->tgl_daftar)->format('Y-m-d')) }}"
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>

                                <select
                                    name="status"
                                    class="form-select"
                                >
                                    <option value="active" @selected(old('status', $customer->status) === 'active')>
                                        Aktif
                                    </option>

                                    <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">KYC Status</label>

                                <input
                                    type="text"
                                    class="form-control readonly-field"
                                    value="{{ ucfirst(old('kyc_status', $customer->kyc_status)) }}"
                                    readonly
                                >
                            </div>

                        </div>
                    </div>

                </div>

                <div class="form-footer">
                    <a
                        href="{{ route('customers.index') }}"
                        class="btn-customer btn-back"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn-customer btn-save"
                    >
                        ✓ Simpan Perubahan
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const monthNames = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des'
    ];

    function formatDateIndonesian(value) {
        if (!value) {
            return '';
        }

        const parts = value.split('-');

        if (parts.length !== 3) {
            return '';
        }

        const year = parts[0];
        const month = parseInt(parts[1], 10);
        const day = parts[2];

        if (!year || !month || !day || !monthNames[month - 1]) {
            return '';
        }

        return `${day} ${monthNames[month - 1]} ${year}`;
    }

    const birthDate = document.getElementById('birthDate');
    const birthDateDisplay = document.getElementById('birthDateDisplay');

    if (birthDate && birthDateDisplay) {
        birthDate.addEventListener('change', function () {
            birthDateDisplay.value = formatDateIndonesian(this.value);
        });

        birthDateDisplay.addEventListener('click', function () {
            if (typeof birthDate.showPicker === 'function') {
                birthDate.showPicker();
            } else {
                birthDate.focus();
            }
        });
    }

    const registrationDate = document.getElementById('registrationDate');
    const registrationDateDisplay = document.getElementById('registrationDateDisplay');

    if (registrationDate && registrationDateDisplay) {
        registrationDate.addEventListener('change', function () {
            registrationDateDisplay.value = formatDateIndonesian(this.value);
        });

        registrationDateDisplay.addEventListener('click', function () {
            if (!registrationDate.readOnly && typeof registrationDate.showPicker === 'function') {
                registrationDate.showPicker();
            }
        });
    }


    // =========================================================
    // IDENTITY TYPE
    // =========================================================

    const identityType = document.getElementById('identityType');
    const noKtp = document.getElementById('noKtp');
    const selainKtpGroup = document.getElementById('selainKtpGroup');
    const selainKtp = document.getElementById('selainKtp');

    function updateIdentityFields() {

        if (!identityType) {
            return;
        }

        const type = identityType.value;

        if (type === 'KTP') {

            if (selainKtpGroup) {
                selainKtpGroup.classList.remove('active');
            }

            if (selainKtp) {
                selainKtp.value = '';
            }

            if (noKtp) {
                noKtp.disabled = false;
            }

        } else if (type !== '') {

            if (selainKtpGroup) {
                selainKtpGroup.classList.add('active');
            }

            if (noKtp) {
                noKtp.disabled = true;
            }

        } else {

            if (selainKtpGroup) {
                selainKtpGroup.classList.remove('active');
            }

            if (noKtp) {
                noKtp.disabled = false;
            }
        }
    }

    if (identityType) {
        identityType.addEventListener('change', updateIdentityFields);
        updateIdentityFields();
    }


    // =========================================================
    // COMPANY
    // =========================================================

    const customerType = document.getElementById('customerType');

    function updateCustomerType() {

        if (!customerType) {
            return;
        }

        if (customerType.value === '2') {

            if (identityType) {
                identityType.value = 'SERTIFIKAT';
                identityType.dispatchEvent(new Event('change'));
            }

        }
    }

    if (customerType) {
        customerType.addEventListener('change', updateCustomerType);
        updateCustomerType();
    }


    // =========================================================
    // DOCUMENT PREVIEW
    // =========================================================

    const documentInput = document.getElementById('documentInput');
    const cameraInput = document.getElementById('cameraInput');
    const documentPreview = document.getElementById('documentPreview');
    const documentName = document.getElementById('documentName');

    function previewFile(file) {

        if (!file || !documentPreview) {
            return;
        }

        documentPreview.innerHTML = '';

        if (file.type === 'application/pdf') {

            const iframe = document.createElement('iframe');

            iframe.src = URL.createObjectURL(file);
            iframe.title = 'Preview Dokumen';

            documentPreview.appendChild(iframe);

        } else if (file.type.startsWith('image/')) {

            const image = document.createElement('img');

            image.src = URL.createObjectURL(file);
            image.alt = 'Preview Dokumen';

            documentPreview.appendChild(image);

        } else {

            documentPreview.innerHTML = `
                <div class="document-placeholder">
                    <div class="icon">⚠️</div>
                    <strong>Format tidak didukung</strong>
                    <span>Gunakan gambar atau PDF.</span>
                </div>
            `;
        }

        if (documentName) {
            documentName.textContent = 'File baru: ' + file.name;
        }
    }

    if (documentInput) {
        documentInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            previewFile(file);
        });
    }

    if (cameraInput) {
        cameraInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            previewFile(file);
        });
    }


    // =========================================================
    // AUTO FILL
    // =========================================================

    const autoFillButton = document.getElementById('autoFillButton');

    if (autoFillButton) {

        autoFillButton.addEventListener('click', function () {

            alert(
                'Fitur Auto Fill/OCR akan diaktifkan pada modul KYC/OCR. ' +
                'Untuk saat ini dokumen hanya dapat dipreview dan disimpan.'
            );

        });
    }

});
</script>
@endsection
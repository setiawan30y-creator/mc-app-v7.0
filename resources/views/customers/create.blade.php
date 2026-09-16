@extends('layouts.app')

@section('title', 'Tambah Nasabah')

@section('content')
<style>
    .customer-page {
        padding: 0;
    }

    .customer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }

    .customer-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #183b32;
    }

    .customer-header p {
        margin: 3px 0 0;
        font-size: 12px;
        color: #71817b;
    }

    .customer-back {
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        color: #176b4d;
        padding: 8px 12px;
        border: 1px solid #cbded5;
        border-radius: 7px;
        background: #fff;
    }

    .customer-workspace {
        display: grid;
        grid-template-columns: 44% 56%;
        gap: 10px;
        height: calc(100vh - 170px);
        min-height: 650px;
    }

    .document-panel,
    .form-panel {
        min-width: 0;
        min-height: 0;
        background: #fff;
        border: 1px solid #dce7e2;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(20, 60, 45, .05);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .panel-header {
        flex: 0 0 auto;
        padding: 11px 14px;
        border-bottom: 1px solid #e4ebe8;
        background: linear-gradient(180deg, #fbfdfc, #f4f8f6);
    }

    .panel-header strong {
        display: block;
        font-size: 14px;
        color: #21493d;
    }

    .panel-header span {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        color: #7a8984;
    }

    /* DOCUMENT PANEL */

    .document-tools {
        flex: 0 0 auto;
        display: flex;
        gap: 7px;
        padding: 10px;
        border-bottom: 1px solid #e5ece9;
        background: #fff;
    }

    .document-tools label,
    .document-tools button {
        border: 1px solid #bfd4ca;
        background: #f8fbfa;
        color: #205b47;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .document-tools label:hover,
    .document-tools button:hover {
        background: #edf6f1;
    }

    .document-tools input[type="file"] {
        display: none;
    }

    .document-preview {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        background:
            linear-gradient(45deg, #f4f6f5 25%, transparent 25%),
            linear-gradient(-45deg, #f4f6f5 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #f4f6f5 75%),
            linear-gradient(-45deg, transparent 75%, #f4f6f5 75%);
        background-size: 24px 24px;
        background-position: 0 0, 0 12px, 12px -12px, -12px 0;
    }        .document-preview-inner {
            width: 100%;
            min-width: 0;
            min-height: 100%;
            height: 100%;
            padding: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }

        .document-placeholder {
            width: 100%;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px dashed #b8cbc3;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .document-image {
            display: none;
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 100%;
            object-fit: contain;
            border-radius: 5px;
            box-shadow: 0 5px 18px rgba(0,0,0,.15);
            background: #fff;
            cursor: zoom-in;
            user-select: none;
            -webkit-user-drag: none;
        }

        .document-pdf {
            display: none;
            width: 100%;
            height: 100%;
            min-height: 500px;
            border: 0;
            background: #fff;
            box-shadow: 0 5px 18px rgba(0,0,0,.15);
        }

        /* ============================================================
           ID VIEWER
           ============================================================ */

        .id-viewer {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,.82);
        }

        .id-viewer.is-open {
            display: flex;
        }

        .id-viewer-toolbar {
            position: absolute;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            background: rgba(20,30,27,.94);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.3);
        }

        .id-viewer-toolbar button {
            min-width: 38px;
            height: 36px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 7px;
            background: #fff;
            color: #173f31;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .id-viewer-toolbar button:hover {
            background: #edf6f1;
        }

        .id-viewer-close {
            position: absolute;
            top: 18px;
            right: 20px;
            z-index: 4;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: rgba(255,255,255,.95);
            color: #333;
            font-size: 22px;
            cursor: pointer;
        }

        .id-viewer-stage {
            position: absolute;
            inset: 70px 20px 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
        }

        .id-viewer-stage.is-dragging {
            cursor: grabbing;
        }

        .id-viewer-image {
            max-width: 90%;
            max-height: 90%;
            width: auto;
            height: auto;
            object-fit: contain;
            transform: translate3d(0,0,0) scale(1);
            transform-origin: center center;
            user-select: none;
            -webkit-user-drag: none;
            box-shadow: 0 10px 40px rgba(0,0,0,.45);
        }

        .id-viewer-zoom-label {
            color: #fff;
            min-width: 55px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

    .autofill-area {
        flex: 0 0 auto;
        padding: 10px;
        border-top: 1px solid #e5ece9;
        background: #fff;
    }

    .autofill-button {
        width: 100%;
        border: 0;
        border-radius: 7px;
        background: #176b4d;
        color: #fff;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .autofill-button:hover {
        background: #125b41;
    }

    .document-status {
        margin-top: 6px;
        font-size: 10px;
        color: #82908b;
        text-align: center;
    }

    /* FORM PANEL */

    .form-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px;
    }

    .form-section {
        margin-bottom: 14px;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e6ece9;
        color: #1e513f;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 10px;
    }

    .field {
        min-width: 0;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: 4px;
        color: #4f625b;
        font-size: 10px;
        font-weight: 700;
    }

    .required {
        color: #b42318;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #cfdcd7;
        border-radius: 6px;
        background: #fff;
        color: #263b34;
        font-size: 12px;
        padding: 7px 8px;
        outline: none;
    }

    .field textarea {
        min-height: 65px;
        resize: vertical;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: #5e9b82;
        box-shadow: 0 0 0 2px rgba(35, 116, 83, .08);
    }

    .field input[readonly] {
        background: #f4f7f5;
        color: #687b73;
    }

    .date-field {
        display: grid;
        grid-template-columns: 1fr 38px;
        gap: 5px;
    }

    .date-picker-button {
        border: 1px solid #cfdcd7;
        border-radius: 6px;
        background: #f7faf8;
        color: #176b4d;
        cursor: pointer;
        font-size: 15px;
    }

    .date-native {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .field-error {
        margin-top: 3px;
        color: #b42318;
        font-size: 10px;
    }

    .auto-note {
        margin-top: 4px;
        font-size: 9px;
        color: #84928d;
    }

    .form-actions {
        flex: 0 0 auto;
        position: sticky;
        bottom: 0;
        z-index: 5;
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        padding: 10px 12px;
        border-top: 1px solid #dfe8e4;
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(5px);
    }

    .btn {
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-cancel {
        border: 1px solid #ccd9d4;
        background: #fff;
        color: #53665e;
    }

    .btn-save {
        border: 0;
        background: #176b4d;
        color: #fff;
    }

    .btn-save:hover {
        background: #125b41;
    }

    .alert-error {
        margin-bottom: 10px;
        padding: 9px 10px;
        border: 1px solid #f1c7c3;
        border-radius: 7px;
        background: #fff4f3;
        color: #a1261c;
        font-size: 11px;
    }

    @media (max-width: 1100px) {
        .customer-workspace {
            grid-template-columns: 1fr;
            height: auto;
        }

        .document-panel,
        .form-panel {
            min-height: 600px;
        }

        .document-panel {
            height: 700px;
        }

        .form-panel {
            height: 800px;
        }
    }

    @media (max-width: 700px) {
        .customer-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .field.full {
            grid-column: auto;
        }

        .document-panel {
            height: 600px;
        }

        .document-preview-inner {
            min-width: 940px;
        }

        .document-image,
        .document-pdf {
            width: 900px;
        }
    }
</style>

<div class="customer-page">

    <div class="customer-header">
        <div>
            <h1>Tambah Nasabah</h1>
            <p>Master Customer • Data nasabah baru</p>
        </div>

        <a href="{{ route('customers.index') }}" class="customer-back">
            ← Kembali ke Master Customer
        </a>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Data belum dapat disimpan.</strong>
            <ul style="margin:5px 0 0 16px;padding:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('customers.store') }}"
        enctype="multipart/form-data"
        id="customerForm"
    >
        @csrf

        <div class="customer-workspace">

            {{-- =========================================================
                 PANEL KIRI — DOKUMEN
            ========================================================== --}}
            <section class="document-panel">

                <div class="panel-header">
                    <strong>Dokumen / Foto Identitas</strong>
                    <span>Pastikan seluruh teks dokumen terlihat jelas.</span>
                </div>

                <div class="document-tools">

                    <label for="document">
                        📁 Pilih File
                    </label>

                    <label for="cameraInput">
                        📷 Kamera
                    </label>

                    <input
                        type="file"
                        id="document"
                        name="document"
                        accept="image/jpeg,image/png,application/pdf"
                    >

                    <input
                        type="file"
                        id="cameraInput"
                        accept="image/*"
                        capture="environment"
                    >

                </div>

                <div class="document-preview" id="documentPreview">

                    <div class="document-preview-inner">

                        <div class="document-placeholder" id="documentPlaceholder">
                            <div>
                                <div style="font-size:42px;margin-bottom:10px;">🪪</div>

                                <strong style="display:block;color:#49635a;margin-bottom:5px;">
                                    Belum ada dokumen
                                </strong>

                                <span>
                                    Pilih file atau gunakan kamera untuk menampilkan
                                    dokumen identitas di sini.
                                </span>
                            </div>
                        </div>

                        <img
                            id="documentImage"
                            class="document-image"
                            src=""
                            alt="Preview dokumen"
                        >

                        <iframe
                            id="documentPdf"
                            class="document-pdf"
                            title="Preview PDF"
                        ></iframe>

                    </div>

                </div>

                <div class="autofill-area">

                    <button
                        type="button"
                        class="autofill-button"
                        id="autoFillButton"
                    >
                        ✨ Auto Fill dari Dokumen
                    </button>

                    <div class="document-status" id="documentStatus">
                        Belum ada dokumen yang dipilih.
                    </div>

                </div>

            </section>


            {{-- =========================================================
                 PANEL KANAN — FORM MASTER CUSTOMER
            ========================================================== --}}
            <section class="form-panel">

                <div class="panel-header">
                    <strong>Master Data Nasabah</strong>
                    <span>Lengkapi data sesuai dokumen dan data nasabah.</span>
                </div>

                <div class="form-scroll">

                    {{-- IDENTITAS UTAMA --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span>01</span>
                            Identitas Utama
                        </div>

                        <div class="form-grid">

                            <div class="field">
                                <label>ID_Nasabah</label>
                                <input
                                    type="text"
                                    value="Otomatis saat disimpan"
                                    readonly
                                >
                                <div class="auto-note">
                                    Dibuat otomatis oleh sistem.
                                </div>
                            </div>

                            <div class="field">
                                <label>IDPJK</label>
                                <input
                                    type="text"
                                    value="{{ old('idpjk', '-') }}"
                                    readonly
                                >
                                <div class="auto-note">
                                    Mengikuti identitas PJK perusahaan/tenant.
                                </div>
                            </div>

                            <div class="field">
                                <label>Kode Nasabah</label>
                                <input
                                    type="text"
                                    value="Otomatis saat disimpan"
                                    readonly
                                >
                            </div>

                            <div class="field">
                                <label>
                                    Tipe <span class="required">*</span>
                                </label>

                                <select name="customer_type" id="customer_type" required>
                                    <option value="">Pilih Tipe</option>
                                    <option
                                        value="individual"
                                        @selected(old('customer_type') === 'individual')
                                    >
                                        Perorangan
                                    </option>
                                    <option
                                        value="company"
                                        @selected(old('customer_type') === 'company')
                                    >
                                        Perusahaan
                                    </option>
                                </select>
                            </div>

                            <div class="field full">
                                <label>
                                    Nama <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="full_name"
                                    value="{{ old('full_name') }}"
                                    maxlength="255"
                                    required
                                    autocomplete="off"
                                    placeholder="Nama lengkap nasabah"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- DATA PRIBADI --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span>02</span>
                            Data Pribadi
                        </div>

                        <div class="form-grid">

                            <div class="field">
                                <label>Tempat_Lahir</label>

                                <input
                                    type="text"
                                    name="tempat_lahir"
                                    value="{{ old('tempat_lahir') }}"
                                    maxlength="100"
                                    placeholder="Contoh: Jakarta"
                                >
                            </div>

                            <div class="field">
                                <label>Tanggal_Lahir</label>

                                <div class="date-field">

                                    <input
                                        type="text"
                                        id="birth_date_display"
                                        value=""
                                        placeholder="dd Mmm yyyy"
                                        readonly
                                    >

                                    <button
                                        type="button"
                                        class="date-picker-button"
                                        id="birthDateButton"
                                        title="Pilih tanggal"
                                    >
                                        📅
                                    </button>

                                </div>

                                <input
                                    type="date"
                                    name="birth_date"
                                    id="birth_date"
                                    class="date-native"
                                    value="{{ old('birth_date') }}"
                                >

                                <div class="auto-note">
                                    Format tampilan: dd Mmm yyyy
                                </div>
                            </div>

                            <div class="field full">
                                <label>
                                    Alamat <span class="required">*</span>
                                </label>

                                <textarea
                                    name="address"
                                    required
                                    placeholder="Alamat lengkap nasabah"
                                >{{ old('address') }}</textarea>
                            </div>

                            <div class="field">
                                <label>
                                    Warga_Negara <span class="required">*</span>
                                </label>

                                <select name="warga_negara" required>`n    <option value="">Pilih Warga Negara</option>`n    @foreach ($nationalities as $nationality)`n        <option value="{{ $nationality->name }}" @selected(old("warga_negara", "Indonesia") === $nationality->name)>{{ $nationality->name }}</option>`n    @endforeach`n</select>
                            </div>

                            <div class="field">
                                <label>Jenis_Kelamin</label>

                                <select name="jenis_kelamin">
                                    <option value="">Pilih</option>

                                    <option
                                        value="Laki-laki"
                                        @selected(old('jenis_kelamin') === 'Laki-laki')
                                    >
                                        Laki-laki
                                    </option>

                                    <option
                                        value="Perempuan"
                                        @selected(old('jenis_kelamin') === 'Perempuan')
                                    >
                                        Perempuan
                                    </option>
                                </select>
                            </div>

                            <div class="field">
                                <label>Pekerjaan</label>

                                <select name="pekerjaan">`n    <option value="">Pilih Pekerjaan</option>`n    @foreach ($occupations as $occupation)`n        <option value="{{ $occupation->name }}" @selected(old("pekerjaan") === $occupation->name)>{{ $occupation->name }}</option>`n    @endforeach`n</select>
                            </div>

                            <div class="field">
                                <label>
                                    No_HP <span class="required">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    maxlength="30"
                                    required
                                    placeholder="08xxxxxxxxxx"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- REKENING --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span>03</span>
                            Rekening
                        </div>

                        <div class="form-grid">

                            <div class="field full">
                                <label>No_Rekening</label>

                                <input
                                    type="text"
                                    name="no_rekening"
                                    value="{{ old('no_rekening') }}"
                                    maxlength="100"
                                    placeholder="Nomor rekening nasabah"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- IDENTITAS --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span>04</span>
                            Identitas
                        </div>

                        <div class="form-grid">

                            <div class="field">
                                <label>
                                    Jenis ID <span class="required">*</span>
                                </label>

                                <select
                                    name="jenis_id"
                                    id="jenis_id"
                                    required
                                >
                                    <option value="">Pilih Jenis ID</option>

                                    <option
                                        value="KTP"
                                        @selected(old('jenis_id') === 'KTP')
                                    >
                                        KTP
                                    </option>

                                    <option
                                        value="SIM"
                                        @selected(old('jenis_id') === 'SIM')
                                    >
                                        SIM
                                    </option>

                                    <option
                                        value="PASSPORT"
                                        @selected(old('jenis_id') === 'PASSPORT')
                                    >
                                        Passport
                                    </option>

                                    <option
                                        value="SERTIFIKAT"
                                        @selected(old('jenis_id') === 'SERTIFIKAT')
                                    >
                                        Sertifikat
                                    </option>
                                </select>
                            </div>

                            <div class="field" id="ktpField">
                                <label>No_KTP</label>

                                <input
                                    type="text"
                                    name="no_ktp"
                                    id="no_ktp"
                                    value="{{ old('no_ktp') }}"
                                    maxlength="100"
                                    placeholder="Nomor KTP"
                                >
                            </div>

                            <div class="field" id="otherIdField">
                                <label>Selain_KTP</label>

                                <input
                                    type="text"
                                    name="selain_ktp"
                                    id="selain_ktp"
                                    value="{{ old('selain_ktp') }}"
                                    maxlength="100"
                                    placeholder="Nomor SIM / Passport / Sertifikat"
                                >
                            </div>

                            <div class="field">
                                <label>No_CIF</label>

                                <input
                                    type="text"
                                    value="Otomatis saat disimpan"
                                    readonly
                                >

                                <div class="auto-note">
                                    Nomor CIF dibuat otomatis.
                                </div>
                            </div>

                            <div class="field">
                                <label>NPWP</label>

                                <input
                                    type="text"
                                    name="npwp"
                                    value="{{ old('npwp') }}"
                                    maxlength="100"
                                    placeholder="Nomor NPWP"
                                >
                            </div>

                            <div class="field">
                                <label>Local_ID</label>

                                <input
                                    type="text"
                                    name="local_id"
                                    value="{{ old('local_id') }}"
                                    maxlength="100"
                                    placeholder="ID lokal/internal"
                                >
                            </div>

                        </div>
                    </div>


                    {{-- PENDAFTARAN --}}
                    <div class="form-section">

                        <div class="form-section-title">
                            <span>05</span>
                            Pendaftaran
                        </div>

                        <div class="form-grid">

                            <div class="field">
                                <label>Tgl_Daftar</label>

                                <input
                                    type="text"
                                    value="{{ now()->format('d M Y') }}"
                                    readonly
                                >

                                <div class="auto-note">
                                    Tanggal dibuat oleh sistem.
                                </div>
                            </div>

                            <div class="field">
                                <label>Status</label>

                                <input
                                    type="text"
                                    value="Active"
                                    readonly
                                >
                            </div>

                        </div>
                    </div>

                </div>


                {{-- ACTION --}}
                <div class="form-actions">

                    <a
                        href="{{ route('customers.index') }}"
                        class="btn btn-cancel"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        💾 Simpan Nasabah
                    </button>

                </div>

            </section>

        </div>

    </form>
</div>


\ \ \ \ <div\ class="id-viewer"\ id="idViewer"\ aria-hidden="true">\n\ \ \ \ \ \ \ \ <div\ class="id-viewer-toolbar">\n\ \ \ \ \ \ \ \ \ \ \ \ <button\ type="button"\ id="idViewerZoomOut"\ title="Zoom\ Out">−</button>\n\ \ \ \ \ \ \ \ \ \ \ \ <button\ type="button"\ id="idViewerReset"\ title="Reset">Reset</button>\n\ \ \ \ \ \ \ \ \ \ \ \ <button\ type="button"\ id="idViewerZoomIn"\ title="Zoom\ In">\+</button>\n\ \ \ \ \ \ \ \ \ \ \ \ <span\ class="id-viewer-zoom-label"\ id="idViewerZoomLabel">100%</span>\n\ \ \ \ \ \ \ \ </div>\n\n\ \ \ \ \ \ \ \ <button\ type="button"\ class="id-viewer-close"\ id="idViewerClose"\ title="Tutup">\n\ \ \ \ \ \ \ \ \ \ \ \ ×\n\ \ \ \ \ \ \ \ </button>\n\n\ \ \ \ \ \ \ \ <div\ class="id-viewer-stage"\ id="idViewerStage">\n\ \ \ \ \ \ \ \ \ \ \ \ <img\n\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ id="idViewerImage"\n\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ class="id-viewer-image"\n\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ src=""\n\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ alt="Preview\ dokumen\ identitas"\n\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ draggable="false"\n\ \ \ \ \ \ \ \ \ \ \ \ >\n\ \ \ \ \ \ \ \ </div>\n\ \ \ \ </div>\n<script>
(function () {

    const documentInput = document.getElementById('document');
    const cameraInput = document.getElementById('cameraInput');

    const documentImage = document.getElementById('documentImage');
    const documentPdf = document.getElementById('documentPdf');
    const documentPlaceholder = document.getElementById('documentPlaceholder');
    const documentStatus = document.getElementById('documentStatus');

    const autoFillButton = document.getElementById('autoFillButton');

    const customerType = document.getElementById('customer_type');
    const jenisId = document.getElementById('jenis_id');

    const noKtp = document.getElementById('no_ktp');
    const selainKtp = document.getElementById('selain_ktp');

    const birthDate = document.getElementById('birth_date');
    const birthDateDisplay = document.getElementById('birth_date_display');
    const birthDateButton = document.getElementById('birthDateButton');


    /*
     * ============================================================
     * FORMAT TANGGAL
     * dd Mmm yyyy
     * Contoh: 17 Agu 1990
     * ============================================================
     */

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

    function formatDisplayDate(value) {

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

        if (!year || !month || !day) {
            return '';
        }

        return `${day} ${monthNames[month - 1]} ${year}`;
    }


    function syncBirthDateDisplay() {
        birthDateDisplay.value = formatDisplayDate(birthDate.value);
    }


    syncBirthDateDisplay();


    birthDateButton.addEventListener('click', function () {

        if (typeof birthDate.showPicker === 'function') {
            birthDate.showPicker();
        } else {
            birthDate.focus();
            birthDate.click();
        }

    });


    birthDate.addEventListener('change', function () {
        syncBirthDateDisplay();
    });


    /*
     * ============================================================
     * PREVIEW DOKUMEN
     * ============================================================
     */

    function showPlaceholder() {

        documentPlaceholder.style.display = 'flex';
        documentImage.style.display = 'none';
        documentPdf.style.display = 'none';

        documentImage.removeAttribute('src');
        documentPdf.removeAttribute('src');

    }


    let currentPreviewUrl = null;

    function previewFile(file) {

        if (!file) {
            showPlaceholder();

            documentStatus.textContent =
                'Belum ada dokumen yang dipilih.';

            return;
        }

        const fileName = file.name || 'Dokumen';

        documentStatus.textContent =
            'Dokumen: ' + fileName;

        if (currentPreviewUrl) {
            URL.revokeObjectURL(currentPreviewUrl);
            currentPreviewUrl = null;
        }

        if (file.type === 'application/pdf') {

            const url = URL.createObjectURL(file);
            currentPreviewUrl = url;

            documentPlaceholder.style.display = 'none';
            documentImage.style.display = 'none';

            documentPdf.src = url;
            documentPdf.style.display = 'block';

            return;
        }

        if (file.type.startsWith('image/')) {

            const url = URL.createObjectURL(file);
            currentPreviewUrl = url;

            documentPlaceholder.style.display = 'none';
            documentPdf.style.display = 'none';

            documentImage.style.width = 'auto';
            documentImage.style.maxWidth = '100%';
            documentImage.style.height = 'auto';
            documentImage.style.maxHeight = '100%';

            documentImage.src = url;
            documentImage.style.display = 'block';

            return;
        }

        showPlaceholder();

        documentStatus.textContent =
            'Format dokumen tidak didukung.';
    }

    /*
     * ============================================================
     * ID VIEWER
     * Klik gambar untuk membuka viewer.
     * Scroll = zoom.
     * Tahan klik kiri + geser = pan.
     * ============================================================
     */

    const idViewer = document.getElementById('idViewer');
    const idViewerStage = document.getElementById('idViewerStage');
    const idViewerImage = document.getElementById('idViewerImage');
    const idViewerClose = document.getElementById('idViewerClose');
    const idViewerZoomIn = document.getElementById('idViewerZoomIn');
    const idViewerZoomOut = document.getElementById('idViewerZoomOut');
    const idViewerReset = document.getElementById('idViewerReset');
    const idViewerZoomLabel = document.getElementById('idViewerZoomLabel');

    let viewerZoom = 1;
    let viewerX = 0;
    let viewerY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let dragOriginX = 0;
    let dragOriginY = 0;

    function updateViewer() {

        if (!idViewerImage) {
            return;
        }

        idViewerImage.style.transform =
            'translate3d(' +
            viewerX +
            'px,' +
            viewerY +
            'px,0) scale(' +
            viewerZoom +
            ')';

        if (idViewerZoomLabel) {
            idViewerZoomLabel.textContent =
                Math.round(viewerZoom * 100) + '%';
        }
    }

    function resetViewer() {
        viewerZoom = 1;
        viewerX = 0;
        viewerY = 0;
        updateViewer();
    }

    function openViewer() {

        if (!documentImage.src) {
            return;
        }

        idViewerImage.src = documentImage.src;

        resetViewer();

        idViewer.classList.add('is-open');
        idViewer.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';
    }

    function closeViewer() {

        idViewer.classList.remove('is-open');
        idViewer.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';
    }

    function changeViewerZoom(amount) {

        viewerZoom = Math.max(
            1,
            Math.min(5, viewerZoom + amount)
        );

        updateViewer();
    }

    documentImage.addEventListener('click', function () {
        openViewer();
    });

    idViewerClose.addEventListener('click', function () {
        closeViewer();
    });

    idViewerZoomIn.addEventListener('click', function () {
        changeViewerZoom(0.25);
    });

    idViewerZoomOut.addEventListener('click', function () {
        changeViewerZoom(-0.25);
    });

    idViewerReset.addEventListener('click', function () {
        resetViewer();
    });

    idViewerStage.addEventListener('wheel', function (event) {

        event.preventDefault();

        if (event.deltaY < 0) {
            changeViewerZoom(0.15);
        } else {
            changeViewerZoom(-0.15);
        }

    }, { passive: false });

    idViewerStage.addEventListener('mousedown', function (event) {

        if (event.button !== 0) {
            return;
        }

        isDragging = true;

        dragStartX = event.clientX;
        dragStartY = event.clientY;

        dragOriginX = viewerX;
        dragOriginY = viewerY;

        idViewerStage.classList.add('is-dragging');

    });

    window.addEventListener('mousemove', function (event) {

        if (!isDragging) {
            return;
        }

        viewerX =
            dragOriginX +
            (event.clientX - dragStartX);

        viewerY =
            dragOriginY +
            (event.clientY - dragStartY);

        updateViewer();

    });

    window.addEventListener('mouseup', function () {

        isDragging = false;

        idViewerStage.classList.remove('is-dragging');

    });

    idViewerStage.addEventListener('dblclick', function () {
        resetViewer();
    });

    idViewer.addEventListener('click', function (event) {

        if (event.target === idViewer) {
            closeViewer();
        }

    });

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape' &&
            idViewer.classList.contains('is-open')) {
            closeViewer();
        }

    });

    documentInput.addEventListener('change', function () {

        if (this.files && this.files.length > 0) {
            previewFile(this.files[0]);
        }

    });


    /*
     * Kamera mengambil gambar lalu dipindahkan ke input document.
     */

    cameraInput.addEventListener('change', function () {

        if (!this.files || this.files.length === 0) {
            return;
        }

        const file = this.files[0];

        try {

            const dataTransfer = new DataTransfer();

            dataTransfer.items.add(file);

            documentInput.files = dataTransfer.files;

        } catch (error) {

            console.warn(
                'Browser tidak mengizinkan pemindahan file kamera.',
                error
            );

        }

        previewFile(file);

    });


    /*
     * ============================================================
     * AUTO FILL
     * ============================================================
     *
     * Untuk tahap ini tombol belum melakukan OCR sungguhan.
     * Infrastruktur upload + preview sudah disiapkan.
     *
     * OCR akan dihubungkan ke service OCR pada tahap berikutnya.
     * ============================================================
     */

    autoFillButton.addEventListener('click', function () {

        const file =
            documentInput.files &&
            documentInput.files.length > 0
                ? documentInput.files[0]
                : null;

        if (!file) {

            alert(
                'Silakan pilih atau foto dokumen terlebih dahulu.'
            );

            return;
        }

        alert(
            'Dokumen sudah siap untuk proses Auto Fill. ' +
            'Mesin OCR akan diintegrasikan pada tahap KYC/OCR berikutnya.'
        );

    });


    /*
     * ============================================================
     * TIPE NASABAH
     * ============================================================
     */

    function syncCustomerType() {

        if (!customerType) {
            return;
        }

        if (customerType.value === 'company') {

            jenisId.value = 'SERTIFIKAT';

            jenisId.dispatchEvent(
                new Event('change')
            );

        }

    }


    customerType.addEventListener(
        'change',
        syncCustomerType
    );


    /*
     * ============================================================
     * JENIS ID
     * ============================================================
     */

    function syncIdentityFields() {

        const value = jenisId.value;

        if (value === 'KTP') {

            noKtp.closest('.field').style.display = 'block';
            selainKtp.closest('.field').style.display = 'none';

            selainKtp.value = '';

        } else {

            noKtp.closest('.field').style.display = 'none';
            selainKtp.closest('.field').style.display = 'block';

            noKtp.value = '';

        }

        if (customerType.value === 'company') {

            jenisId.value = 'SERTIFIKAT';

            noKtp.closest('.field').style.display = 'none';
            selainKtp.closest('.field').style.display = 'block';

        }

    }


    jenisId.addEventListener(
        'change',
        syncIdentityFields
    );


    syncCustomerType();
    syncIdentityFields();


    /*
     * ============================================================
     * UPPERCASE WARGA NEGARA
     * ============================================================
     */

    const wargaNegara =
        document.querySelector(
            'input[name="warga_negara"]'
        );

    if (wargaNegara) {

        wargaNegara.addEventListener(
            'input',
            function () {
                this.value =
                    this.value.toUpperCase();
            }
        );

    }

})();
</script>
@endsection
<!-- MC-ALMARA-ID-VIEWER-V2 -->

<style>
/* Sembunyikan viewer lama yang rusak */
#idViewer {
    display: none !important;
}

/* =========================================================
   ID VIEWER V2
   ========================================================= */

#idViewerV2 {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: none;
    background: rgba(0,0,0,.88);
}

#idViewerV2.is-open {
    display: block;
}

#idViewerV2 .idv2-toolbar {
    position: absolute;
    top: 18px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;

    display: flex;
    align-items: center;
    gap: 8px;

    padding: 8px 10px;

    background: rgba(20,25,23,.96);
    border: 1px solid rgba(255,255,255,.20);
    border-radius: 10px;

    box-shadow: 0 8px 30px rgba(0,0,0,.40);
}

#idViewerV2 .idv2-btn {
    min-width: 40px;
    height: 38px;

    padding: 0 12px;

    border: 1px solid #d5ddd9;
    border-radius: 7px;

    background: #fff;
    color: #173f31;

    font-size: 14px;
    font-weight: 700;

    cursor: pointer;
}

#idViewerV2 .idv2-btn:hover {
    background: #edf6f1;
}

#idViewerV2 .idv2-zoom-label {
    min-width: 58px;
    color: #fff;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
}

#idViewerV2 .idv2-close {
    position: absolute;
    top: 18px;
    right: 22px;
    z-index: 20;

    width: 42px;
    height: 42px;

    border: 0;
    border-radius: 50%;

    background: #fff;
    color: #222;

    font-size: 25px;
    line-height: 42px;

    cursor: pointer;

    box-shadow: 0 5px 20px rgba(0,0,0,.35);
}

#idViewerV2 .idv2-stage {
    position: absolute;
    inset: 75px 20px 20px;

    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: grab;
    touch-action: none;
}

#idViewerV2 .idv2-stage.dragging {
    cursor: grabbing;
}

#idViewerV2 .idv2-image {
    display: block;

    width: auto;
    height: auto;

    max-width: 90%;
    max-height: 90%;

    object-fit: contain;

    transform-origin: center center;

    user-select: none;
    -webkit-user-select: none;
    -webkit-user-drag: none;

    box-shadow: 0 12px 45px rgba(0,0,0,.50);
    cursor: grab;
}

#idViewerV2 .idv2-hint {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);

    color: rgba(255,255,255,.75);

    font-size: 12px;
    pointer-events: none;
}

@media (max-width: 768px) {

    #idViewerV2 .idv2-toolbar {
        top: 10px;
    }

    #idViewerV2 .idv2-close {
        top: 10px;
        right: 10px;
    }

    #idViewerV2 .idv2-stage {
        inset: 65px 8px 10px;
    }

}
</style>

<script>
(function () {

    function initIdViewerV2() {

        const documentImage =
            document.getElementById('documentImage');

        if (!documentImage) {
            return;
        }

        if (document.getElementById('idViewerV2')) {
            return;
        }

        /*
         * =========================================================
         * CREATE VIEWER
         * =========================================================
         */

        const viewer =
            document.createElement('div');

        viewer.id = 'idViewerV2';
        viewer.setAttribute('aria-hidden', 'true');

        viewer.innerHTML =
            '<div class="idv2-toolbar">' +

                '<button type="button" class="idv2-btn" id="idv2ZoomOut">' +
                    '−' +
                '</button>' +

                '<button type="button" class="idv2-btn" id="idv2Reset">' +
                    'Reset' +
                '</button>' +

                '<button type="button" class="idv2-btn" id="idv2ZoomIn">' +
                    '+' +
                '</button>' +

                '<span class="idv2-zoom-label" id="idv2ZoomLabel">' +
                    '100%' +
                '</span>' +

            '</div>' +

            '<button type="button" class="idv2-close" id="idv2Close">' +
                '×' +
            '</button>' +

            '<div class="idv2-stage" id="idv2Stage">' +

                '<img class="idv2-image" id="idv2Image" src="" alt="Preview ID">' +

            '</div>' +

            '<div class="idv2-hint">' +
                'Scroll untuk zoom • Tahan klik kiri lalu geser untuk memindahkan' +
            '</div>';

        document.body.appendChild(viewer);

        const stage =
            document.getElementById('idv2Stage');

        const viewerImage =
            document.getElementById('idv2Image');

        const closeButton =
            document.getElementById('idv2Close');

        const zoomInButton =
            document.getElementById('idv2ZoomIn');

        const zoomOutButton =
            document.getElementById('idv2ZoomOut');

        const resetButton =
            document.getElementById('idv2Reset');

        const zoomLabel =
            document.getElementById('idv2ZoomLabel');

        let zoom = 1;

        let posX = 0;
        let posY = 0;

        let dragging = false;

        let startX = 0;
        let startY = 0;

        let startPosX = 0;
        let startPosY = 0;


        function render() {

            viewerImage.style.transform =
                'translate3d(' +
                posX +
                'px,' +
                posY +
                'px,0) scale(' +
                zoom +
                ')';

            zoomLabel.textContent =
                Math.round(zoom * 100) + '%';
        }


        function reset() {

            zoom = 1;

            posX = 0;
            posY = 0;

            render();
        }


        function changeZoom(amount) {

            const oldZoom = zoom;

            zoom =
                Math.max(
                    1,
                    Math.min(5, zoom + amount)
                );

            /*
             * Sedikit kompensasi posisi supaya gambar
             * tetap berada di sekitar tengah.
             */
            if (oldZoom !== zoom && zoom === 1) {
                posX = 0;
                posY = 0;
            }

            render();
        }


        function openViewer() {

            if (!documentImage.src) {
                return;
            }

            /*
             * Pastikan viewer lama tidak pernah terlihat.
             */
            const oldViewer =
                document.getElementById('idViewer');

            if (oldViewer) {
                oldViewer.classList.remove('is-open');
                oldViewer.style.display = 'none';
            }

            viewerImage.src =
                documentImage.src;

            viewer.classList.add('is-open');
            viewer.setAttribute('aria-hidden', 'false');

            document.body.style.overflow = 'hidden';

            reset();
        }


        function closeViewer() {

            viewer.classList.remove('is-open');
            viewer.setAttribute('aria-hidden', 'true');

            document.body.style.overflow = '';

            viewerImage.src = '';
        }


        /*
         * KLIK GAMBAR
         */
        documentImage.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                openViewer();

            },
            true
        );


        /*
         * TOMBOL ZOOM
         */
        zoomInButton.addEventListener(
            'click',
            function () {
                changeZoom(.25);
            }
        );


        zoomOutButton.addEventListener(
            'click',
            function () {
                changeZoom(-.25);
            }
        );


        resetButton.addEventListener(
            'click',
            function () {
                reset();
            }
        );


        /*
         * CLOSE
         */
        closeButton.addEventListener(
            'click',
            function () {
                closeViewer();
            }
        );


        /*
         * KLIK AREA GELAP
         */
        viewer.addEventListener(
            'click',
            function (event) {

                if (event.target === viewer) {
                    closeViewer();
                }

            }
        );


        /*
         * SCROLL = ZOOM
         */
        stage.addEventListener(
            'wheel',
            function (event) {

                event.preventDefault();

                if (event.deltaY < 0) {
                    changeZoom(.15);
                } else {
                    changeZoom(-.15);
                }

            },
            {
                passive: false
            }
        );


        /*
         * TAHAN KLIK + GESER
         */
        stage.addEventListener(
            'pointerdown',
            function (event) {

                if (event.button !== 0) {
                    return;
                }

                dragging = true;

                startX = event.clientX;
                startY = event.clientY;

                startPosX = posX;
                startPosY = posY;

                stage.classList.add('dragging');

                stage.setPointerCapture(
                    event.pointerId
                );

            }
        );


        stage.addEventListener(
            'pointermove',
            function (event) {

                if (!dragging) {
                    return;
                }

                posX =
                    startPosX +
                    (event.clientX - startX);

                posY =
                    startPosY +
                    (event.clientY - startY);

                render();

            }
        );


        function stopDragging() {

            dragging = false;

            stage.classList.remove('dragging');

        }


        stage.addEventListener(
            'pointerup',
            stopDragging
        );

        stage.addEventListener(
            'pointercancel',
            stopDragging
        );

        stage.addEventListener(
            'pointerleave',
            function () {

                if (!stage.hasPointerCapture) {
                    stopDragging();
                }

            }
        );


        /*
         * DOUBLE CLICK = RESET
         */
        stage.addEventListener(
            'dblclick',
            function () {
                reset();
            }
        );


        /*
         * ESC = CLOSE
         */
        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    viewer.classList.contains('is-open')
                ) {
                    closeViewer();
                }

            }
        );

    }


    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            initIdViewerV2
        );

    } else {

        initIdViewerV2();

    }

})();
</script>

<!-- /MC-ALMARA-ID-VIEWER-V2 -->
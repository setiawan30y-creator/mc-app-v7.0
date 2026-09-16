@extends('layouts.app')

@section('title', 'Gantungan Baru')

@push('styles')
<style>
    .gantungan-page {
        padding: 28px 24px 44px;
        max-width: 1180px;
        margin: 0 auto;
    }

    .gantungan-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .gantungan-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--ui-gold, #b08b3e);
        margin-bottom: 7px;
    }

    .gantungan-eyebrow::before {
        content: '';
        width: 22px;
        height: 2px;
        border-radius: 999px;
        background: currentColor;
    }

    .gantungan-title {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
        color: var(--ui-text, #17201c);
    }

    .gantungan-subtitle {
        margin: 7px 0 0;
        max-width: 650px;
        font-size: 12px;
        line-height: 1.6;
        color: var(--ui-muted, #64748b);
    }

    .gantungan-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid var(--ui-border, #e5e7eb);
        border-radius: var(--ui-input-radius, 10px);
        background: var(--ui-card-bg, #fff);
        color: var(--ui-text, #17201c);
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .gantungan-back:hover {
        border-color: var(--ui-primary, #147957);
        color: var(--ui-primary, #147957);
    }

    .gantungan-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 285px;
        gap: 18px;
        align-items: start;
    }

    .form-card,
    .info-card {
        background: var(--ui-card-bg, #fff);
        border: 1px solid var(--ui-border, #e5e7eb);
        border-radius: var(--ui-card-radius, 14px);
        overflow: hidden;
    }

    .form-card-head,
    .info-card-head {
        padding: 16px 19px;
        border-bottom: 1px solid var(--ui-border, #e5e7eb);
    }

    .section-title {
        margin: 0;
        font-size: 13px;
        font-weight: 800;
        color: var(--ui-text, #17201c);
    }

    .section-note {
        margin: 4px 0 0;
        font-size: 11px;
        color: var(--ui-muted, #64748b);
    }

    .form-card-body {
        padding: 20px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 17px 16px;
    }

    .field { grid-column: span 6; }
    .field-4 { grid-column: span 4; }
    .field-12 { grid-column: span 12; }

    .field-label {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 750;
        color: var(--ui-text, #334155);
    }

    .required { color: #b91c1c; }

    .field-control {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--ui-border, #dfe4e1);
        border-radius: var(--ui-input-radius, 10px);
        background: var(--ui-card-bg, #fff);
        color: var(--ui-text, #17201c);
        font-size: 12px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .field-control:focus {
        border-color: var(--ui-primary, #147957);
        box-shadow: 0 0 0 3px rgba(20, 121, 87, .10);
    }

    textarea.field-control {
        min-height: 110px;
        resize: vertical;
        line-height: 1.55;
    }

    .field-help {
        margin-top: 6px;
        font-size: 10px;
        color: var(--ui-muted, #64748b);
    }

    .amount-wrap { position: relative; }
    .amount-prefix {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        font-weight: 800;
        color: var(--ui-muted, #64748b);
        pointer-events: none;
    }
    .amount-input { padding-left: 42px; }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
        padding-top: 17px;
        border-top: 1px solid var(--ui-border, #e5e7eb);
    }

    .action-left {
        font-size: 10px;
        color: var(--ui-muted, #64748b);
    }

    .action-right {
        display: flex;
        gap: 9px;
    }

    .btn-secondary,
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 15px;
        border-radius: var(--ui-input-radius, 10px);
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-secondary {
        border: 1px solid var(--ui-border, #e5e7eb);
        background: var(--ui-card-bg, #fff);
        color: var(--ui-text, #334155);
    }

    .btn-primary {
        border: 1px solid var(--ui-primary, #147957);
        background: var(--ui-primary, #147957);
        color: #fff;
        box-shadow: 0 5px 14px rgba(20, 121, 87, .15);
    }

    .btn-primary:hover { filter: brightness(.96); }

    .alert-error {
        margin-bottom: 18px;
        padding: 13px 15px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 11px;
    }

    .alert-error strong { display: block; margin-bottom: 5px; }
    .alert-error ul { margin: 0; padding-left: 18px; }

    .info-card-body { padding: 17px 18px; }

    .info-item {
        display: flex;
        gap: 11px;
        padding: 12px 0;
        border-bottom: 1px solid var(--ui-border, #edf0ee);
    }
    .info-item:first-child { padding-top: 0; }
    .info-item:last-child { border-bottom: 0; padding-bottom: 0; }

    .info-icon {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        background: var(--ui-primary-soft, #e7f5ef);
        color: var(--ui-primary, #147957);
        font-size: 13px;
        font-weight: 900;
    }

    .info-text strong {
        display: block;
        margin-bottom: 3px;
        font-size: 11px;
        color: var(--ui-text, #334155);
    }
    .info-text span {
        display: block;
        font-size: 10px;
        line-height: 1.5;
        color: var(--ui-muted, #64748b);
    }

    @media (max-width: 980px) {
        .gantungan-layout { grid-template-columns: 1fr; }
        .info-card { order: 2; }
    }

    @media (max-width: 700px) {
        .gantungan-page { padding: 20px 15px 32px; }
        .gantungan-head { flex-direction: column; }
        .gantungan-back { width: 100%; justify-content: center; }
        .field, .field-4 { grid-column: span 12; }
        .form-card-body { padding: 16px; }
        .form-actions { align-items: stretch; flex-direction: column; }
        .action-left { display: none; }
        .action-right { width: 100%; }
        .action-right > * { flex: 1; }
    }
</style>
@endpush

@section('content')
<div class="gantungan-page">
    <div class="gantungan-head">
        <div>
            <div class="gantungan-eyebrow">Kas &amp; Operasional</div>
            <h1 class="gantungan-title">Buat Gantungan Baru</h1>
            <p class="gantungan-subtitle">
                Catat dana yang sementara masih menggantung agar tetap terlacak dan dapat diperhitungkan saat proses settlement dan Closing.
            </p>
        </div>
        <a href="{{ route('gantungan.index') }}" class="gantungan-back">
            ← Kembali ke Gantungan
        </a>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <strong>Data belum dapat disimpan.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('gantungan.store') }}">
        @csrf

        <div class="gantungan-layout">
            <section class="form-card">
                <div class="form-card-head">
                    <h2 class="section-title">Informasi Gantungan</h2>
                    <p class="section-note">Isi data utama sesuai bukti atau kejadian operasional.</p>
                </div>

                <div class="form-card-body">
                    <div class="form-grid">
                        <div class="field-4">
                            <label class="field-label" for="business_date">Tanggal Bisnis <span class="required">*</span></label>
                            <input id="business_date" type="date" name="business_date" value="{{ old('business_date', now()->toDateString()) }}" class="field-control" required>
                        </div>

                        <div class="field-4">
                            <label class="field-label" for="category">Kategori <span class="required">*</span></label>
                            <select id="category" name="category" class="field-control" required>
                                <option value="employee" @selected(old('category') === 'employee')>Karyawan</option>
                                <option value="branch" @selected(old('category') === 'branch')>Cabang / Tempat Lain</option>
                                <option value="supplier" @selected(old('category') === 'supplier')>Supplier</option>
                                <option value="operational" @selected(old('category') === 'operational')>Operasional</option>
                                <option value="other" @selected(old('category') === 'other')>Lainnya</option>
                            </select>
                        </div>

                        <div class="field-4">
                            <label class="field-label" for="due_date">Jatuh Tempo</label>
                            <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" class="field-control">
                        </div>

                        <div class="field-12">
                            <label class="field-label" for="title">Judul Gantungan <span class="required">*</span></label>
                            <input id="title" name="title" value="{{ old('title') }}" class="field-control" maxlength="150" placeholder="Contoh: Pinjaman kas ke Andi" required>
                        </div>

                        <div class="field">
                            <label class="field-label" for="counterparty_name">Nama / PIC</label>
                            <input id="counterparty_name" name="counterparty_name" value="{{ old('counterparty_name') }}" class="field-control" maxlength="150" placeholder="Nama karyawan, supplier, atau pihak terkait">
                        </div>

                        <div class="field">
                            <label class="field-label" for="reference">Referensi</label>
                            <input id="reference" name="reference" value="{{ old('reference') }}" class="field-control" maxlength="100" placeholder="No. transaksi / bukti / referensi">
                        </div>

                        <div class="field-12">
                            <label class="field-label" for="amount">Nominal <span class="required">*</span></label>
                            <div class="amount-wrap">
                                <span class="amount-prefix">Rp</span>
                                <input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" class="field-control amount-input" placeholder="0" required>
                            </div>
                            <div class="field-help">Masukkan nilai dana yang masih menggantung. Settlement nantinya akan mengurangi outstanding.</div>
                        </div>

                        <div class="field-12">
                            <label class="field-label" for="description">Keterangan</label>
                            <textarea id="description" name="description" rows="4" maxlength="5000" class="field-control" placeholder="Jelaskan tujuan, kondisi, atau informasi pendukung Gantungan...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="action-left">Kolom bertanda <span class="required">*</span> wajib diisi.</div>
                        <div class="action-right">
                            <a href="{{ route('gantungan.index') }}" class="btn-secondary">Batal</a>
                            <button type="submit" class="btn-primary">Simpan Gantungan</button>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="info-card">
                <div class="info-card-head">
                    <h2 class="section-title">Alur Gantungan</h2>
                    <p class="section-note">Tetap tercatat sampai dana diselesaikan.</p>
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <div class="info-icon">1</div>
                        <div class="info-text">
                            <strong>Catat</strong>
                            <span>Buat Gantungan dan simpan nominal yang masih outstanding.</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">2</div>
                        <div class="info-text">
                            <strong>Monitor</strong>
                            <span>Outstanding dapat dipantau berdasarkan pihak dan tanggal.</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">3</div>
                        <div class="info-text">
                            <strong>Settlement</strong>
                            <span>Saat dana kembali atau diselesaikan, catat settlement-nya.</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">4</div>
                        <div class="info-text">
                            <strong>Closing</strong>
                            <span>Gantungan yang masih outstanding menjadi bagian dari kontrol operasional.</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('content')

<style>
    .almara-mutation-form-page {
        max-width: 1050px;
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

    .almara-mutation-separator {
        color: #a0aec0;
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
        margin: 8px 0 24px;
        color: #60758a;
        font-size: 16px;
    }

    .almara-mutation-card {
        background: #fff;
        border: 1px solid #e1e8ed;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(24,55,73,.07);
        overflow: hidden;
    }

    .almara-mutation-card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 24px 28px;
        background: #f7fafb;
        border-bottom: 1px solid #e8edf1;
    }

    .almara-mutation-icon {
        width: 55px;
        height: 55px;
        flex: 0 0 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #eaf3f2;
        color: #087f70;
        font-size: 27px;
    }

    .almara-mutation-card-title {
        margin: 0;
        color: #172d3d;
        font-size: 21px;
        font-weight: 700;
    }

    .almara-mutation-card-subtitle {
        margin: 4px 0 0;
        color: #718296;
        font-size: 13px;
    }

    .almara-mutation-form {
        padding: 28px;
    }

    .almara-mutation-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px 30px;
    }

    .almara-mutation-field.full {
        grid-column: 1 / -1;
    }

    .almara-mutation-label {
        display: block;
        margin-bottom: 8px;
        color: #172d3d;
        font-size: 14px;
        font-weight: 650;
    }

    .almara-mutation-required {
        color: #d64545;
    }

    .almara-mutation-input,
    .almara-mutation-select,
    .almara-mutation-textarea {
        width: 100%;
        border: 1px solid #d6e0e7;
        border-radius: 9px;
        background: #fff;
        color: #25394a;
        font-family: inherit;
        font-size: 15px;
        outline: none;
        transition: .18s ease;
        box-sizing: border-box;
    }

    .almara-mutation-input,
    .almara-mutation-select {
        height: 46px;
        padding: 0 13px;
    }

    .almara-mutation-textarea {
        min-height: 100px;
        padding: 12px 13px;
        resize: vertical;
    }

    .almara-mutation-input:focus,
    .almara-mutation-select:focus,
    .almara-mutation-textarea:focus {
        border-color: #168b7c;
        box-shadow: 0 0 0 3px rgba(22,139,124,.11);
    }

    .almara-mutation-help {
        margin-top: 6px;
        color: #8998a6;
        font-size: 12px;
    }

    .almara-mutation-amount-box {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        padding: 18px;
        border: 1px solid #e1e8ed;
        border-radius: 11px;
        background: #fafcfd;
    }

    .almara-mutation-amount-item label {
        display: block;
        margin-bottom: 7px;
        font-size: 13px;
        font-weight: 650;
        color: #34495a;
    }

    .almara-mutation-debit-label {
        color: #b44444 !important;
    }

    .almara-mutation-credit-label {
        color: #08735f !important;
    }

    .almara-mutation-error {
        margin-bottom: 22px;
        padding: 14px 17px;
        border: 1px solid #f2b8b8;
        border-radius: 9px;
        background: #fff5f5;
        color: #a92f2f;
    }

    .almara-mutation-error ul {
        margin: 0;
        padding-left: 19px;
    }

    .almara-mutation-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 28px;
        background: #fafcfd;
        border-top: 1px solid #e8edf1;
    }

    .almara-mutation-btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        transition: .18s ease;
    }

    .almara-mutation-btn-cancel {
        color: #34495a;
        background: #fff;
        border: 1px solid #ccd8e0;
    }

    .almara-mutation-btn-save {
        color: #fff;
        background: #087f70;
        border: 1px solid #087f70;
    }

    .almara-mutation-btn-save:hover {
        color: #fff;
        background: #066e62;
        border-color: #066e62;
    }

    @media (max-width: 750px) {
        .almara-mutation-grid {
            grid-template-columns: 1fr;
        }

        .almara-mutation-field.full {
            grid-column: auto;
        }

        .almara-mutation-amount-box {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .almara-mutation-form-page {
            padding: 24px 16px 40px;
        }

        .almara-mutation-title {
            font-size: 28px;
        }

        .almara-mutation-card-header,
        .almara-mutation-form {
            padding: 20px;
        }

        .almara-mutation-footer {
            padding: 16px 20px;
        }

        .almara-mutation-btn {
            flex: 1;
        }
    }
</style>

<div class="almara-mutation-form-page">

    <div class="almara-mutation-breadcrumb">
        <a href="{{ route('settings.bank-accounts.index') }}">Pengaturan</a>
        <span class="almara-mutation-separator">›</span>
        <a href="{{ route('settings.bank-accounts.show', $account->id) }}">
            {{ $account->bank_name }}
        </a>
        <span class="almara-mutation-separator">›</span>
        <a href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}">
            Mutasi Bank
        </a>
        <span class="almara-mutation-separator">›</span>
        <span>Tambah</span>
    </div>

    <h1 class="almara-mutation-title">
        Tambah Mutasi Bank
    </h1>

    <p class="almara-mutation-subtitle">
        Catat transaksi debit atau credit pada rekening bank.
    </p>

    @if($errors->any())
        <div class="almara-mutation-error">

            <strong>Periksa kembali data berikut:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <form
        method="POST"
        action="{{ route('settings.bank-accounts.mutations.store', $account->id) }}"
    >
        @csrf

        <div class="almara-mutation-card">

            <div class="almara-mutation-card-header">

                <div class="almara-mutation-icon">
                    ⇄
                </div>

                <div>

                    <h2 class="almara-mutation-card-title">
                        {{ $account->bank_name }}
                    </h2>

                    <p class="almara-mutation-card-subtitle">
                        {{ $account->account_name }}
                        ·
                        {{ $account->account_number }}
                        ·
                        {{ $account->currency?->code ?? '-' }}
                    </p>

                </div>

            </div>

            <div class="almara-mutation-form">

                <div class="almara-mutation-grid">

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Tanggal & Waktu
                            <span class="almara-mutation-required">*</span>
                        </label>

                        <input
                            type="datetime-local"
                            name="transaction_date"
                            class="almara-mutation-input"
                            value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                            required
                        >
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Value Date
                        </label>

                        <input
                            type="date"
                            name="value_date"
                            class="almara-mutation-input"
                            value="{{ old('value_date') }}"
                        >
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Referensi
                        </label>

                        <input
                            type="text"
                            name="reference"
                            class="almara-mutation-input"
                            value="{{ old('reference') }}"
                            maxlength="150"
                            placeholder="Nomor referensi bank"
                        >
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            External ID
                        </label>

                        <input
                            type="text"
                            name="external_id"
                            class="almara-mutation-input"
                            value="{{ old('external_id') }}"
                            maxlength="150"
                            placeholder="ID dari bank/API jika tersedia"
                        >
                    </div>

                    <div class="almara-mutation-field full">

                        <label class="almara-mutation-label">
                            Nominal Mutasi
                            <span class="almara-mutation-required">*</span>
                        </label>

                        <div class="almara-mutation-amount-box">

                            <div class="almara-mutation-amount-item">

                                <label class="almara-mutation-debit-label">
                                    Debit / Uang Keluar
                                </label>

                                <input
                                    type="number"
                                    name="debit"
                                    class="almara-mutation-input"
                                    value="{{ old('debit') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                >

                            </div>

                            <div class="almara-mutation-amount-item">

                                <label class="almara-mutation-credit-label">
                                    Credit / Uang Masuk
                                </label>

                                <input
                                    type="number"
                                    name="credit"
                                    class="almara-mutation-input"
                                    value="{{ old('credit') }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                >

                            </div>

                        </div>

                        <div class="almara-mutation-help">
                            Isi salah satu saja. Debit dan credit tidak boleh diisi bersamaan.
                        </div>

                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Saldo dari Bank
                        </label>

                        <input
                            type="number"
                            name="balance"
                            class="almara-mutation-input"
                            value="{{ old('balance') }}"
                            step="0.01"
                            placeholder="Opsional"
                            inputmode="decimal"
                        >

                        <div class="almara-mutation-help">
                            Isi jika saldo tersedia dari rekening koran/bank.
                        </div>
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Source
                        </label>

                        <select
                            name="source"
                            class="almara-mutation-select"
                        >
                            <option value="manual" @selected(old('source', 'manual') === 'manual')>
                                Manual
                            </option>
                            <option value="import" @selected(old('source') === 'import')>
                                Import
                            </option>
                            <option value="api" @selected(old('source') === 'api')>
                                API
                            </option>
                            <option value="bank_statement" @selected(old('source') === 'bank_statement')>
                                Bank Statement
                            </option>
                        </select>
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            Status Rekonsiliasi
                        </label>

                        <select
                            name="reconciliation_status"
                            class="almara-mutation-select"
                        >
                            <option value="unmatched" @selected(old('reconciliation_status', 'unmatched') === 'unmatched')>
                                Unmatched
                            </option>
                            <option value="manual" @selected(old('reconciliation_status') === 'manual')>
                                Manual
                            </option>
                            <option value="ignored" @selected(old('reconciliation_status') === 'ignored')>
                                Ignored
                            </option>
                            <option value="matched" @selected(old('reconciliation_status') === 'matched')>
                                Matched
                            </option>
                        </select>
                    </div>

                    <div class="almara-mutation-field">
                        <label class="almara-mutation-label">
                            ID Transaksi MC
                        </label>

                        <input
                            type="text"
                            name="matched_transaction_id"
                            class="almara-mutation-input"
                            value="{{ old('matched_transaction_id') }}"
                            maxlength="26"
                            placeholder="Diisi saat matched"
                        >
                    </div>

                    <div class="almara-mutation-field full">

                        <label class="almara-mutation-label">
                            Keterangan
                        </label>

                        <textarea
                            name="description"
                            class="almara-mutation-textarea"
                            placeholder="Keterangan transaksi bank..."
                        >{{ old('description') }}</textarea>

                    </div>

                    <div class="almara-mutation-field full">

                        <label class="almara-mutation-label">
                            Catatan Internal
                        </label>

                        <textarea
                            name="notes"
                            class="almara-mutation-textarea"
                            placeholder="Catatan internal jika diperlukan..."
                        >{{ old('notes') }}</textarea>

                    </div>

                </div>

            </div>

            <div class="almara-mutation-footer">

                <a
                    href="{{ route('settings.bank-accounts.mutations.index', $account->id) }}"
                    class="almara-mutation-btn almara-mutation-btn-cancel"
                >
                    ← Batal
                </a>

                <button
                    type="submit"
                    class="almara-mutation-btn almara-mutation-btn-save"
                >
                    ✓ Simpan Mutasi
                </button>

            </div>

        </div>

    </form>

</div>

@endsection

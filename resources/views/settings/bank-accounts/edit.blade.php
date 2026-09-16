@extends('layouts.app')

@section('content')

<style>
    .almara-bank-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 30px 24px 50px;
    }

    .almara-bank-breadcrumb {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 18px;
        color: #718096;
        font-size: 13px;
    }

    .almara-bank-breadcrumb a {
        color: #49658a;
        text-decoration: none;
    }

    .almara-bank-breadcrumb a:hover {
        color: #087f70;
    }

    .almara-bank-breadcrumb .separator {
        color: #a0aec0;
    }

    .almara-bank-header {
        margin-bottom: 24px;
    }

    .almara-bank-title {
        margin: 0;
        color: #102c3a;
        font-size: 36px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -0.8px;
    }

    .almara-bank-subtitle {
        margin: 8px 0 0;
        color: #60758a;
        font-size: 17px;
    }

    .almara-bank-card {
        background: #fff;
        border: 1px solid #e1e8ed;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(24, 55, 73, .07);
        overflow: hidden;
    }

    .almara-bank-card-header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 26px 28px 22px;
        border-bottom: 1px solid #edf1f4;
    }

    .almara-bank-card-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        border-radius: 11px;
        background: #eaf3f2;
        color: #086d61;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 29px;
    }

    .almara-bank-card-title {
        margin: 0;
        color: #142c3a;
        font-size: 22px;
        font-weight: 700;
    }

    .almara-bank-card-description {
        margin: 5px 0 0;
        color: #708296;
        font-size: 14px;
    }

    .almara-bank-form {
        padding: 28px;
    }

    .almara-bank-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px 30px;
    }

    .almara-bank-field {
        min-width: 0;
    }

    .almara-bank-field.full {
        grid-column: 1 / -1;
    }

    .almara-bank-label {
        display: block;
        margin-bottom: 8px;
        color: #172d3d;
        font-size: 14px;
        font-weight: 650;
    }

    .almara-bank-required {
        color: #d64545;
        margin-left: 2px;
    }

    .almara-bank-input-wrap {
        display: flex;
        align-items: stretch;
        width: 100%;
        border: 1px solid #d6e0e7;
        border-radius: 9px;
        background: #fff;
        overflow: hidden;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .almara-bank-input-wrap:focus-within {
        border-color: #168b7c;
        box-shadow: 0 0 0 3px rgba(22, 139, 124, .11);
    }

    .almara-bank-input-icon {
        width: 54px;
        flex: 0 0 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f4f7f9;
        border-right: 1px solid #e1e8ed;
        color: #60788e;
        font-size: 20px;
    }

    .almara-bank-input,
    .almara-bank-select,
    .almara-bank-textarea {
        width: 100%;
        border: 0 !important;
        outline: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
        color: #25394a;
        font-family: inherit;
        font-size: 15px;
    }

    .almara-bank-input {
        height: 46px;
        padding: 0 14px;
    }

    .almara-bank-select {
        height: 46px;
        padding: 0 40px 0 14px;
        cursor: pointer;
    }

    .almara-bank-input::placeholder,
    .almara-bank-textarea::placeholder {
        color: #98a7b5;
    }

    .almara-bank-money {
        display: flex;
        align-items: stretch;
        width: 100%;
        border: 1px solid #d6e0e7;
        border-radius: 9px;
        overflow: hidden;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .almara-bank-money:focus-within {
        border-color: #168b7c;
        box-shadow: 0 0 0 3px rgba(22, 139, 124, .11);
    }

    .almara-bank-money input {
        flex: 1;
        min-width: 0;
        height: 46px;
        border: 0;
        outline: 0;
        padding: 0 14px;
        font-size: 15px;
        color: #25394a;
    }

    .almara-bank-money-label {
        display: flex;
        align-items: center;
        padding: 0 17px;
        background: #f4f7f9;
        border-left: 1px solid #e1e8ed;
        color: #60788e;
        font-size: 13px;
        font-weight: 600;
    }

    .almara-bank-help {
        margin-top: 7px;
        color: #8998a6;
        font-size: 12px;
    }

    .almara-bank-textarea-wrap {
        display: flex;
        align-items: stretch;
        width: 100%;
        border: 1px solid #d6e0e7;
        border-radius: 9px;
        overflow: hidden;
        background: #fff;
    }

    .almara-bank-textarea-icon {
        width: 54px;
        flex: 0 0 54px;
        display: flex;
        justify-content: center;
        padding-top: 14px;
        background: #f4f7f9;
        border-right: 1px solid #e1e8ed;
        color: #60788e;
        font-size: 19px;
    }

    .almara-bank-textarea {
        min-height: 105px;
        resize: vertical;
        padding: 13px 14px;
        line-height: 1.5;
    }

    .almara-bank-active {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 22px;
    }

    .almara-bank-active input {
        width: 21px;
        height: 21px;
        margin: 1px 0 0;
        accent-color: #087f70;
        cursor: pointer;
    }

    .almara-bank-active-label {
        color: #263a49;
        font-size: 15px;
        font-weight: 650;
        cursor: pointer;
    }

    .almara-bank-active-help {
        display: block;
        margin-top: 3px;
        color: #718296;
        font-size: 13px;
        font-weight: 400;
    }

    .almara-bank-errors {
        margin-bottom: 22px;
        padding: 14px 17px;
        border: 1px solid #f2b8b8;
        border-radius: 9px;
        background: #fff5f5;
        color: #a92f2f;
    }

    .almara-bank-errors ul {
        margin: 0;
        padding-left: 19px;
    }

    .almara-bank-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 28px;
        background: #fafcfd;
        border-top: 1px solid #e8edf1;
    }

    .almara-bank-btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 0 21px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        transition: all .18s ease;
    }

    .almara-bank-btn-cancel {
        color: #34495a;
        background: #fff;
        border: 1px solid #ccd8e0;
    }

    .almara-bank-btn-cancel:hover {
        background: #f5f8fa;
    }

    .almara-bank-btn-save {
        color: #fff;
        background: #087f70;
        border: 1px solid #087f70;
        box-shadow: 0 4px 10px rgba(8, 127, 112, .16);
    }

    .almara-bank-btn-save:hover {
        background: #066e62;
        border-color: #066e62;
        transform: translateY(-1px);
    }

    @media (max-width: 900px) {
        .almara-bank-grid {
            grid-template-columns: 1fr;
        }

        .almara-bank-field.full {
            grid-column: auto;
        }
    }

    @media (max-width: 600px) {
        .almara-bank-page {
            padding: 24px 16px 40px;
        }

        .almara-bank-title {
            font-size: 28px;
        }

        .almara-bank-card-header,
        .almara-bank-form {
            padding: 20px;
        }

        .almara-bank-footer {
            padding: 16px 20px;
        }

        .almara-bank-btn {
            flex: 1;
        }
    }
</style>

<div class="almara-bank-page">

    <div class="almara-bank-breadcrumb">
        <a href="{{ route('settings.bank-accounts.index') }}">Pengaturan</a>
        <span class="separator">›</span>
        <a href="{{ route('settings.bank-accounts.index') }}">Rekening Bank</a>
        <span class="separator">›</span>
        <span>Edit</span>
    </div>

    <div class="almara-bank-header">
        <h1 class="almara-bank-title">Edit Rekening Bank</h1>
        <p class="almara-bank-subtitle">
            Perbarui informasi rekening bank untuk cabang aktif.
        </p>
    </div>

    @if($errors->any())
        <div class="almara-bank-errors">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('settings.bank-accounts.update', $account->id) }}">
        @csrf
        @method('PUT')

        <div class="almara-bank-card">

            <div class="almara-bank-card-header">
                <div class="almara-bank-card-icon">🏦</div>

                <div>
                    <h2 class="almara-bank-card-title">
                        Informasi Rekening Bank
                    </h2>

                    <p class="almara-bank-card-description">
                        Perubahan akan diterapkan pada rekening ini.
                    </p>
                </div>
            </div>

            <div class="almara-bank-form">

                <div class="almara-bank-grid">

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="bank_name">
                            Nama Bank
                            <span class="almara-bank-required">*</span>
                        </label>

                        <div class="almara-bank-input-wrap">
                            <div class="almara-bank-input-icon">🏛</div>

                            <input
                                type="text"
                                id="bank_name"
                                name="bank_name"
                                class="almara-bank-input"
                                value="{{ old('bank_name', $account->bank_name) }}"
                                required
                                maxlength="100"
                            >
                        </div>
                    </div>

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="bank_code">
                            Kode Bank
                        </label>

                        <div class="almara-bank-input-wrap">
                            <div class="almara-bank-input-icon">▥</div>

                            <input
                                type="text"
                                id="bank_code"
                                name="bank_code"
                                class="almara-bank-input"
                                value="{{ old('bank_code', $account->bank_code) }}"
                                maxlength="30"
                            >
                        </div>
                    </div>

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="account_name">
                            Nama Pemilik Rekening
                            <span class="almara-bank-required">*</span>
                        </label>

                        <div class="almara-bank-input-wrap">
                            <div class="almara-bank-input-icon">👤</div>

                            <input
                                type="text"
                                id="account_name"
                                name="account_name"
                                class="almara-bank-input"
                                value="{{ old('account_name', $account->account_name) }}"
                                required
                                maxlength="150"
                            >
                        </div>
                    </div>

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="account_number">
                            Nomor Rekening
                            <span class="almara-bank-required">*</span>
                        </label>

                        <div class="almara-bank-input-wrap">
                            <div class="almara-bank-input-icon">▣</div>

                            <input
                                type="text"
                                id="account_number"
                                name="account_number"
                                class="almara-bank-input"
                                value="{{ old('account_number', $account->account_number) }}"
                                required
                                maxlength="100"
                                inputmode="numeric"
                            >
                        </div>
                    </div>

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="currency_id">
                            Mata Uang
                            <span class="almara-bank-required">*</span>
                        </label>

                        <div class="almara-bank-input-wrap">
                            <div class="almara-bank-input-icon">◉</div>

                            <select
                                id="currency_id"
                                name="currency_id"
                                class="almara-bank-select"
                                required
                            >
                                @foreach($currencies as $currency)
                                    <option
                                        value="{{ $currency->id }}"
                                        @selected(old('currency_id', $account->currency_id) == $currency->id)
                                    >
                                        {{ $currency->code }} — {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="almara-bank-field">
                        <label class="almara-bank-label" for="opening_balance">
                            Saldo Awal
                        </label>

                        <div class="almara-bank-money">
                            <div class="almara-bank-input-icon">▣</div>

                            <input
                                type="number"
                                id="opening_balance"
                                name="opening_balance"
                                value="{{ old('opening_balance', $account->opening_balance) }}"
                                min="0"
                                step="0.01"
                                inputmode="decimal"
                            >

                            <div class="almara-bank-money-label">
                                Nominal
                            </div>
                        </div>

                        <div class="almara-bank-help">
                            Saldo awal sebelum mutasi rekening.
                        </div>
                    </div>

                    <div class="almara-bank-field full">
                        <label class="almara-bank-label" for="notes">
                            Catatan
                        </label>

                        <div class="almara-bank-textarea-wrap">
                            <div class="almara-bank-textarea-icon">▤</div>

                            <textarea
                                id="notes"
                                name="notes"
                                class="almara-bank-textarea"
                                maxlength="500"
                                placeholder="Tambahkan catatan jika diperlukan..."
                            >{{ old('notes', $account->notes) }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="almara-bank-active">

                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $account->is_active))
                    >

                    <label
                        for="is_active"
                        class="almara-bank-active-label"
                    >
                        Rekening aktif

                        <span class="almara-bank-active-help">
                            Nonaktifkan jika rekening sudah tidak digunakan.
                        </span>
                    </label>

                </div>

            </div>

            <div class="almara-bank-footer">

                <a
                    href="{{ route('settings.bank-accounts.show', $account->id) }}"
                    class="almara-bank-btn almara-bank-btn-cancel"
                >
                    ← Batal
                </a>

                <button
                    type="submit"
                    class="almara-bank-btn almara-bank-btn-save"
                >
                    ✓ Simpan Perubahan
                </button>

            </div>

        </div>
    </form>

</div>

@endsection

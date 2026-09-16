@extends('layouts.app')

@section('title', 'Master Currency Variant')

@section('page-title', 'Master Currency Variant')

@section('content')

<div class="currency-variant-page">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="currency-variant-header">

        <div>
            <div class="currency-variant-title">
                Master Currency Variant / Series
            </div>

            <div class="currency-variant-subtitle">
                Kelola variant atau series uang berdasarkan mata uang
            </div>
        </div>

        <div class="currency-variant-header-status">
            <span class="status-dot"></span>
            Master Aktif
        </div>

    </div>


    {{-- =========================================================
         FLASH MESSAGE
         ========================================================= --}}
    @if(session('success'))

        <div class="currency-variant-alert currency-variant-alert-success">
            <span class="alert-icon">✓</span>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif


    @if(session('error'))

        <div class="currency-variant-alert currency-variant-alert-danger">
            <span class="alert-icon">!</span>

            <span>
                {{ session('error') }}
            </span>
        </div>

    @endif


    {{-- =========================================================
         VALIDATION
         ========================================================= --}}
    @if($errors->any())

        <div class="currency-variant-alert currency-variant-alert-danger">

            <span class="alert-icon">!</span>

            <div>

                @foreach($errors->all() as $error)

                    <div>
                        {{ $error }}
                    </div>

                @endforeach

            </div>

        </div>

    @endif


    {{-- =========================================================
         CURRENCY SELECTOR
         ========================================================= --}}
    <div class="currency-variant-filter-card">

        <form
            method="GET"
            action="{{ route('settings.currency-variants.index') }}"
            class="currency-variant-filter-form"
        >

            <div class="filter-field">

                <label for="currency_id">
                    Mata Uang
                </label>

                <select
                    id="currency_id"
                    name="currency_id"
                    onchange="this.form.submit()"
                >

                    @forelse($currencies as $currency)

                        <option
                            value="{{ $currency->id }}"
                            @selected((int) $selectedCurrencyId === (int) $currency->id)
                        >
                            {{ $currency->flag ? $currency->flag . ' ' : '' }}
                            {{ $currency->code }}
                            — {{ $currency->name }}
                        </option>

                    @empty

                        <option value="">
                            Belum ada mata uang
                        </option>

                    @endforelse

                </select>

            </div>


            @php
                $selectedCurrency = $currencies->firstWhere('id', $selectedCurrencyId);
            @endphp


            <div class="filter-info">

                <div class="filter-info-label">
                    MASTER TERPILIH
                </div>

                <div class="filter-info-value">

                    @if($selectedCurrency)

                        <span class="currency-flag">
                            {{ $selectedCurrency->flag }}
                        </span>

                        <strong>
                            {{ $selectedCurrency->code }}
                        </strong>

                        <span class="info-separator">
                            /
                        </span>

                        <span>
                            {{ $selectedCurrency->name }}
                        </span>

                    @else

                        <span>
                            Belum ada currency aktif
                        </span>

                    @endif

                </div>

            </div>

        </form>

    </div>


    {{-- =========================================================
         MAIN CARD
         ========================================================= --}}
    <div class="currency-variant-card">

        <div class="currency-variant-card-header">

            <div>

                <div class="card-title">
                    Daftar Variant / Series
                </div>

                <div class="card-subtitle">

                    Variant yang tersedia untuk

                    <strong>
                        {{ $selectedCurrency?->code ?? '-' }}
                    </strong>

                    <span>
                        {{ $selectedCurrency?->name ?? '' }}
                    </span>

                </div>

            </div>


            @if($selectedCurrency)

                <button
                    type="button"
                    class="btn-primary"
                    onclick="openAddCurrencyVariantModal()"
                >
                    <span>＋</span>
                    Tambah Variant
                </button>

            @endif

        </div>


        {{-- =====================================================
             TABLE
             ===================================================== --}}
        <div class="currency-variant-table-wrapper">

            <table class="currency-variant-table">

                <thead>

                    <tr>

                        <th class="col-no">
                            NO
                        </th>

                        <th class="col-name">
                            VARIANT / SERIES
                        </th>

                        <th class="col-code">
                            CODE
                        </th>

                        <th class="col-default">
                            DEFAULT
                        </th>

                        <th class="col-status">
                            STATUS
                        </th>

                        <th class="col-order">
                            URUTAN
                        </th>

                        <th class="col-action">
                            AKSI
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($variants as $index => $variant)

                        <tr>

                            <td class="text-center">
                                {{ $index + 1 }}
                            </td>


                            <td>

                                <div class="variant-name-wrapper">

                                    <div class="variant-name">
                                        {{ $variant->name }}
                                    </div>

                                    @if($variant->description)

                                        <div class="variant-description">
                                            {{ $variant->description }}
                                        </div>

                                    @endif

                                </div>

                            </td>


                            <td>

                                @if($variant->code)

                                    <span class="code-badge">
                                        {{ $variant->code }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            <td>

                                @if($variant->is_default)

                                    <span class="default-badge">
                                        ★ Default
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            <td>

                                @if($variant->is_active)

                                    <span class="status-badge status-active">
                                        Aktif
                                    </span>

                                @else

                                    <span class="status-badge status-inactive">
                                        Nonaktif
                                    </span>

                                @endif

                            </td>


                            <td class="text-center">
                                {{ $variant->sort_order }}
                            </td>


                            <td>

                                <div class="action-group">

                                    {{-- EDIT --}}

                                    <button
                                        type="button"
                                        class="action-button action-edit"
                                        title="Edit"
                                        onclick='openEditCurrencyVariantModal(
                                            {{ $variant->id }},
                                            @json($variant->name),
                                            @json($variant->code ?? ''),
                                            @json($variant->description ?? ''),
                                            {{ $variant->sort_order }},
                                            {{ $variant->is_default ? 'true' : 'false' }}
                                        )'
                                    >
                                        ✎
                                    </button>


                                    {{-- SET DEFAULT --}}

                                    @if($variant->is_active && !$variant->is_default)

                                        <form
                                            method="POST"
                                            action="{{ route('settings.currency-variants.set-default', $variant) }}"
                                            class="inline-form"
                                            onsubmit="return confirmSetDefault('{{ addslashes($variant->name) }}');"
                                        >

                                            @csrf

                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="action-button action-default"
                                                title="Jadikan Default"
                                            >
                                                ★
                                            </button>

                                        </form>

                                    @endif


                                    {{-- TOGGLE --}}

                                    <form
                                        method="POST"
                                        action="{{ route('settings.currency-variants.toggle', $variant) }}"
                                        class="inline-form"
                                        onsubmit="return confirmToggleVariant('{{ addslashes($variant->name) }}', {{ $variant->is_active ? 'true' : 'false' }});"
                                    >

                                        @csrf

                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="action-button {{ $variant->is_active ? 'action-disable' : 'action-enable' }}"
                                            title="{{ $variant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        >
                                            {{ $variant->is_active ? '●' : '○' }}
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="empty-state"
                            >

                                <div class="empty-icon">
                                    ◎
                                </div>

                                <div class="empty-title">
                                    Belum ada variant
                                </div>

                                <div class="empty-description">

                                    Belum ada currency variant atau series
                                    yang dikonfigurasi untuk mata uang ini.

                                </div>


                                @if($selectedCurrency)

                                    <button
                                        type="button"
                                        class="btn-primary empty-button"
                                        onclick="openAddCurrencyVariantModal()"
                                    >
                                        ＋ Tambah Variant
                                    </button>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =============================================================
     ADD MODAL
     ============================================================= --}}

<div
    class="currency-variant-modal"
    id="addCurrencyVariantModal"
    aria-hidden="true"
>

    <div
        class="currency-variant-modal-backdrop"
        onclick="closeAddCurrencyVariantModal()"
    ></div>


    <div class="currency-variant-modal-dialog">

        <div class="modal-header">

            <div>

                <div class="modal-title">
                    Tambah Currency Variant
                </div>

                <div class="modal-subtitle">

                    @if($selectedCurrency)

                        {{ $selectedCurrency->flag }}
                        {{ $selectedCurrency->code }}
                        — {{ $selectedCurrency->name }}

                    @endif

                </div>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeAddCurrencyVariantModal()"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('settings.currency-variants.store') }}"
        >

            @csrf


            <input
                type="hidden"
                name="currency_id"
                value="{{ $selectedCurrencyId }}"
            >


            <div class="modal-body">

                <div class="form-field">

                    <label for="add_name">
                        Nama Variant / Series <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="add_name"
                        name="name"
                        maxlength="100"
                        placeholder="Contoh: Standard"
                        required
                    >

                    <div class="form-help">
                        Contoh: Standard, New Series, Old Series, Current Series.
                    </div>

                </div>


                <div class="form-field">

                    <label for="add_code">
                        Code
                    </label>

                    <input
                        type="text"
                        id="add_code"
                        name="code"
                        maxlength="50"
                        placeholder="Contoh: STD"
                    >

                </div>


                <div class="form-field">

                    <label for="add_sort_order">
                        Urutan
                    </label>

                    <input
                        type="number"
                        id="add_sort_order"
                        name="sort_order"
                        min="0"
                        max="999999"
                        value="0"
                    >

                </div>


                <div class="form-field">

                    <label for="add_description">
                        Keterangan
                    </label>

                    <textarea
                        id="add_description"
                        name="description"
                        rows="3"
                        placeholder="Keterangan variant atau series..."
                    ></textarea>

                </div>


                <div class="form-checkbox-field">

                    <label class="checkbox-label">

                        <input
                            type="checkbox"
                            id="add_is_default"
                            name="is_default"
                            value="1"
                        >

                        <span>
                            Jadikan sebagai Default Variant
                        </span>

                    </label>

                    <div class="form-help">
                        Hanya satu variant aktif yang dapat menjadi default
                        untuk setiap mata uang.
                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeAddCurrencyVariantModal()"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Simpan Variant
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
     EDIT MODAL
     ============================================================= --}}

<div
    class="currency-variant-modal"
    id="editCurrencyVariantModal"
    aria-hidden="true"
>

    <div
        class="currency-variant-modal-backdrop"
        onclick="closeEditCurrencyVariantModal()"
    ></div>


    <div class="currency-variant-modal-dialog">

        <div class="modal-header">

            <div>

                <div class="modal-title">
                    Edit Currency Variant
                </div>

                <div class="modal-subtitle">
                    Ubah konfigurasi variant / series
                </div>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeEditCurrencyVariantModal()"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            id="editCurrencyVariantForm"
        >

            @csrf

            @method('PUT')


            <div class="modal-body">

                <div class="form-field">

                    <label for="edit_name">
                        Nama Variant / Series <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        maxlength="100"
                        required
                    >

                    <div class="form-help">
                        Nama harus unik dalam satu mata uang.
                    </div>

                </div>


                <div class="form-field">

                    <label for="edit_code">
                        Code
                    </label>

                    <input
                        type="text"
                        id="edit_code"
                        name="code"
                        maxlength="50"
                    >

                </div>


                <div class="form-field">

                    <label for="edit_sort_order">
                        Urutan
                    </label>

                    <input
                        type="number"
                        id="edit_sort_order"
                        name="sort_order"
                        min="0"
                        max="999999"
                    >

                </div>


                <div class="form-field">

                    <label for="edit_description">
                        Keterangan
                    </label>

                    <textarea
                        id="edit_description"
                        name="description"
                        rows="3"
                    ></textarea>

                </div>


                <div class="form-checkbox-field">

                    <label class="checkbox-label">

                        <input
                            type="checkbox"
                            id="edit_is_default"
                            name="is_default"
                            value="1"
                        >

                        <span>
                            Jadikan sebagai Default Variant
                        </span>

                    </label>

                    <div class="form-help">
                        Variant nonaktif tidak dapat menjadi default.
                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeEditCurrencyVariantModal()"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>


<style>

/* =============================================================
   PAGE
   ============================================================= */

.currency-variant-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 0 20px;
    font-size: var(--ui-font-size);
}


/* =============================================================
   HEADER
   ============================================================= */

.currency-variant-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 14px;
}

.currency-variant-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--ui-primary-dark);
    line-height: 1.25;
}

.currency-variant-subtitle {
    margin-top: 3px;
    color: var(--ui-text-muted);
    font-size: 11px;
}

.currency-variant-header-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 10px;
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-badge-radius);
    background: var(--ui-surface);
    color: var(--ui-text-secondary);
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--ui-success);
}


/* =============================================================
   ALERT
   ============================================================= */

.currency-variant-alert {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 9px 12px;
    margin-bottom: 12px;
    border-radius: var(--ui-card-radius);
    font-size: 11px;
    line-height: 1.5;
}

.currency-variant-alert-success {
    color: #176b4d;
    background: var(--ui-success-light);
    border: 1px solid #cfe9da;
}

.currency-variant-alert-danger {
    color: #9c3d35;
    background: var(--ui-danger-light);
    border: 1px solid #efd0cd;
}

.alert-icon {
    font-weight: 800;
}


/* =============================================================
   FILTER
   ============================================================= */

.currency-variant-filter-card {
    background: var(--ui-surface);
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-card-radius);
    margin-bottom: 12px;
}

.currency-variant-filter-form {
    display: grid;
    grid-template-columns: minmax(260px, 1fr) minmax(300px, 1.2fr);
    gap: 12px;
    padding: 13px;
    align-items: end;
}

.filter-field {
    min-width: 0;
}

.filter-field label,
.form-field label {
    display: block;
    margin-bottom: 5px;
    color: var(--ui-text-secondary);
    font-size: var(--ui-font-label);
    font-weight: 700;
}

.filter-field select,
.form-field input,
.form-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--ui-border-dark);
    border-radius: var(--ui-input-radius);
    background: #fff;
    color: var(--ui-text);
    font-family: inherit;
    font-size: 12px;
    outline: none;
}

.filter-field select,
.form-field input {
    height: var(--ui-input-height);
    padding: 0 10px;
}

.form-field textarea {
    min-height: 78px;
    padding: 9px 10px;
    resize: vertical;
}

.filter-field select:focus,
.form-field input:focus,
.form-field textarea:focus {
    border-color: var(--ui-border-focus);
    box-shadow: 0 0 0 2px rgba(50, 148, 107, .08);
}

.filter-info {
    min-height: 34px;
    padding: 6px 10px;
    border: 1px solid var(--ui-border-light);
    border-radius: var(--ui-input-radius);
    background: var(--ui-surface-soft);
    box-sizing: border-box;
}

.filter-info-label {
    color: var(--ui-text-muted);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .5px;
}

.filter-info-value {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 1px;
    color: var(--ui-text);
    font-size: 12px;
}

.currency-flag {
    font-size: 15px;
}

.info-separator {
    color: var(--ui-text-muted);
}


/* =============================================================
   CARD
   ============================================================= */

.currency-variant-card {
    background: var(--ui-card-bg);
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-card-radius);
    overflow: hidden;
}

.currency-variant-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--ui-border);
    background: var(--ui-surface-soft);
}

.card-title {
    color: var(--ui-primary-dark);
    font-size: 13px;
    font-weight: 800;
}

.card-subtitle {
    margin-top: 3px;
    color: var(--ui-text-muted);
    font-size: 10px;
}

.card-subtitle strong {
    color: var(--ui-text-secondary);
}


/* =============================================================
   BUTTON
   ============================================================= */

.btn-primary,
.btn-secondary {
    height: var(--ui-button-height);
    padding: 0 12px;
    border-radius: var(--ui-button-radius);
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid transparent;
    white-space: nowrap;
}

.btn-primary {
    color: #fff;
    background: var(--ui-primary);
    border-color: var(--ui-primary);
}

.btn-primary:hover {
    background: var(--ui-primary-dark);
    border-color: var(--ui-primary-dark);
}

.btn-secondary {
    color: var(--ui-text-secondary);
    background: #fff;
    border-color: var(--ui-border-dark);
}

.btn-secondary:hover {
    background: var(--ui-bg);
}


/* =============================================================
   TABLE
   ============================================================= */

.currency-variant-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.currency-variant-table {
    width: 100%;
    min-width: 820px;
    border-collapse: collapse;
    table-layout: fixed;
}

.currency-variant-table th {
    height: 36px;
    padding: 0 10px;
    background: var(--ui-table-header-bg);
    border-bottom: 1px solid var(--ui-table-border);
    color: var(--ui-text-secondary);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .25px;
    text-align: left;
    white-space: nowrap;
}

.currency-variant-table td {
    min-height: 43px;
    padding: 7px 10px;
    border-bottom: 1px solid var(--ui-table-border);
    color: var(--ui-text);
    font-size: var(--ui-table-font-size);
    vertical-align: middle;
}

.currency-variant-table tbody tr:hover {
    background: var(--ui-table-row-hover);
}

.currency-variant-table tbody tr:last-child td {
    border-bottom: 0;
}


/* =============================================================
   COLUMN
   ============================================================= */

.col-no {
    width: 55px;
}

.col-name {
    width: 290px;
}

.col-code {
    width: 110px;
}

.col-default {
    width: 130px;
}

.col-status {
    width: 110px;
}

.col-order {
    width: 80px;
}

.col-action {
    width: 125px;
}


/* =============================================================
   TEXT
   ============================================================= */

.text-center {
    text-align: center !important;
}

.text-muted {
    color: var(--ui-text-muted);
}

.variant-name-wrapper {
    min-width: 0;
}

.variant-name {
    color: var(--ui-text);
    font-size: 12px;
    font-weight: 800;
}

.variant-description {
    margin-top: 3px;
    color: var(--ui-text-muted);
    font-size: 9px;
    line-height: 1.4;
}


/* =============================================================
   CODE
   ============================================================= */

.code-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    padding: 4px 7px;
    border-radius: 4px;
    background: var(--ui-primary-light);
    border: 1px solid #cfe3d7;
    color: var(--ui-primary-dark);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .3px;
}


/* =============================================================
   DEFAULT
   ============================================================= */

.default-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 7px;
    border-radius: var(--ui-badge-radius);
    background: var(--ui-primary-light);
    color: var(--ui-primary-dark);
    border: 1px solid #cfe3d7;
    font-size: 9px;
    font-weight: 800;
}


/* =============================================================
   STATUS
   ============================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 55px;
    padding: 4px 7px;
    border-radius: var(--ui-badge-radius);
    font-size: 9px;
    font-weight: 800;
}

.status-active {
    background: var(--ui-success-light);
    color: #1a7a4f;
    border: 1px solid #cfe9da;
}

.status-inactive {
    background: #f1f3f2;
    color: #78827e;
    border: 1px solid #dfe5e2;
}


/* =============================================================
   ACTION
   ============================================================= */

.action-group {
    display: flex;
    align-items: center;
    gap: 5px;
}

.inline-form {
    display: inline;
    margin: 0;
}

.action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    padding: 0;
    border-radius: 4px;
    border: 1px solid var(--ui-border);
    background: #fff;
    cursor: pointer;
    font-size: 12px;
}

.action-edit {
    color: var(--ui-primary-dark);
}

.action-edit:hover {
    background: var(--ui-primary-light);
    border-color: #bcdaca;
}

.action-default {
    color: var(--ui-primary-dark);
}

.action-default:hover {
    background: var(--ui-primary-light);
    border-color: #bcdaca;
}

.action-disable {
    color: var(--ui-warning);
}

.action-disable:hover {
    background: #fff8e8;
}

.action-enable {
    color: var(--ui-success);
}

.action-enable:hover {
    background: var(--ui-success-light);
}


/* =============================================================
   EMPTY
   ============================================================= */

.empty-state {
    padding: 45px 20px !important;
    text-align: center;
}

.empty-icon {
    margin-bottom: 7px;
    color: var(--ui-text-muted);
    font-size: 28px;
}

.empty-title {
    color: var(--ui-text);
    font-size: 13px;
    font-weight: 800;
}

.empty-description {
    max-width: 450px;
    margin: 4px auto 12px;
    color: var(--ui-text-muted);
    font-size: 10px;
}

.empty-button {
    display: inline-flex;
    align-items: center;
}


/* =============================================================
   FORM HELP
   ============================================================= */

.form-help {
    margin-top: 4px;
    color: var(--ui-text-muted);
    font-size: 9px;
    line-height: 1.4;
}

.form-checkbox-field {
    margin-top: 2px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: var(--ui-text-secondary);
    font-size: 11px;
    font-weight: 700;
}

.checkbox-label input {
    width: 15px;
    height: 15px;
    margin: 0;
    accent-color: var(--ui-primary);
}


/* =============================================================
   MODAL
   ============================================================= */

.currency-variant-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.currency-variant-modal.is-open {
    display: flex;
}

.currency-variant-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 40, 31, .38);
}

.currency-variant-modal-dialog {
    position: relative;
    width: min(500px, calc(100vw - 32px));
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    background: #fff;
    border: 1px solid var(--ui-border);
    border-radius: 6px;
    box-shadow: var(--ui-shadow-md);
    z-index: 1;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--ui-border);
    background: var(--ui-surface-soft);
}

.modal-title {
    color: var(--ui-primary-dark);
    font-size: 14px;
    font-weight: 800;
}

.modal-subtitle {
    margin-top: 3px;
    color: var(--ui-text-muted);
    font-size: 10px;
}

.modal-close {
    width: 28px;
    height: 28px;
    padding: 0;
    border: 1px solid var(--ui-border);
    border-radius: 4px;
    background: #fff;
    color: var(--ui-text-muted);
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
}

.modal-close:hover {
    color: var(--ui-danger);
    border-color: #e2c2be;
}

.modal-body {
    padding: 15px 16px;
}

.form-field {
    margin-bottom: 12px;
}

.form-field:last-child {
    margin-bottom: 0;
}

.form-field label span {
    color: var(--ui-danger);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--ui-border);
    background: var(--ui-surface-soft);
}


/* =============================================================
   RESPONSIVE
   ============================================================= */

@media (max-width: 900px) {

    .currency-variant-filter-form {
        grid-template-columns: 1fr 1fr;
    }

}

@media (max-width: 640px) {

    .currency-variant-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .currency-variant-filter-form {
        grid-template-columns: 1fr;
    }

    .currency-variant-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .currency-variant-card-header .btn-primary {
        width: 100%;
    }

}


/* =============================================================
   SCROLL LOCK
   ============================================================= */

body.currency-variant-modal-open {
    overflow: hidden;
}

</style>


<script>

/* =============================================================
   ADD MODAL
   ============================================================= */

function openAddCurrencyVariantModal() {

    const modal = document.getElementById(
        'addCurrencyVariantModal'
    );

    if (!modal) {
        return;
    }

    modal.classList.add('is-open');

    modal.setAttribute(
        'aria-hidden',
        'false'
    );

    document.body.classList.add(
        'currency-variant-modal-open'
    );

    const nameInput = document.getElementById(
        'add_name'
    );

    if (nameInput) {
        nameInput.focus();
    }

}


function closeAddCurrencyVariantModal() {

    const modal = document.getElementById(
        'addCurrencyVariantModal'
    );

    if (!modal) {
        return;
    }

    modal.classList.remove('is-open');

    modal.setAttribute(
        'aria-hidden',
        'true'
    );

    document.body.classList.remove(
        'currency-variant-modal-open'
    );

}


/* =============================================================
   EDIT MODAL
   ============================================================= */

function openEditCurrencyVariantModal(
    id,
    name,
    code,
    description,
    sortOrder,
    isDefault
) {

    const modal = document.getElementById(
        'editCurrencyVariantModal'
    );

    const form = document.getElementById(
        'editCurrencyVariantForm'
    );

    if (!modal || !form) {
        return;
    }


    form.action =
        "{{ url('/pengaturan/master-currency-variant') }}/" + id;


    document.getElementById(
        'edit_name'
    ).value = name || '';


    document.getElementById(
        'edit_code'
    ).value = code || '';


    document.getElementById(
        'edit_description'
    ).value = description || '';


    document.getElementById(
        'edit_sort_order'
    ).value = sortOrder ?? 0;


    document.getElementById(
        'edit_is_default'
    ).checked = !!isDefault;


    modal.classList.add('is-open');

    modal.setAttribute(
        'aria-hidden',
        'false'
    );

    document.body.classList.add(
        'currency-variant-modal-open'
    );


    document.getElementById(
        'edit_name'
    ).focus();

}


function closeEditCurrencyVariantModal() {

    const modal = document.getElementById(
        'editCurrencyVariantModal'
    );

    if (!modal) {
        return;
    }

    modal.classList.remove('is-open');

    modal.setAttribute(
        'aria-hidden',
        'true'
    );

    document.body.classList.remove(
        'currency-variant-modal-open'
    );

}


/* =============================================================
   CONFIRMATION
   ============================================================= */

function confirmSetDefault(name) {

    return confirm(
        'Jadikan "' +
        name +
        '" sebagai Default Variant?\\n\\n' +
        'Variant default sebelumnya pada mata uang ini akan diganti.'
    );

}


function confirmToggleVariant(name, isActive) {

    if (isActive) {

        return confirm(
            'Nonaktifkan variant "' +
            name +
            '"?'
        );

    }

    return confirm(
        'Aktifkan kembali variant "' +
        name +
        '"?'
    );

}


/* =============================================================
   ESCAPE KEY
   ============================================================= */

document.addEventListener(
    'keydown',
    function(event) {

        if (event.key !== 'Escape') {
            return;
        }

        closeAddCurrencyVariantModal();

        closeEditCurrencyVariantModal();

    }
);


/* =============================================================
   BACKDROP CLICK
   ============================================================= */

document.addEventListener(
    'click',
    function(event) {

        const addModal =
            document.getElementById(
                'addCurrencyVariantModal'
            );

        const editModal =
            document.getElementById(
                'editCurrencyVariantModal'
            );


        if (
            addModal &&
            event.target === addModal
        ) {
            closeAddCurrencyVariantModal();
        }


        if (
            editModal &&
            event.target === editModal
        ) {
            closeEditCurrencyVariantModal();
        }

    }
);

</script>


@endsection

@extends('layouts.app')

@section('title', 'Master Denomination')
@section('page-title', 'Master Denomination')

@section('content')

<div class="denomination-page">

    {{-- Header --}}
    <div class="denomination-header">
        <div>
            <div class="denomination-title">
                Master Denomination
            </div>
            <div class="denomination-subtitle">
                Kelola pecahan uang berdasarkan mata uang dan variant
            </div>
        </div>

        <div class="denomination-header-status">
            <span class="status-dot"></span>
            Master Aktif
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="denomination-alert denomination-alert-success">
            <span class="alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="denomination-alert denomination-alert-danger">
            <span class="alert-icon">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validation --}}
    @if($errors->any())
        <div class="denomination-alert denomination-alert-danger">
            <span class="alert-icon">!</span>

            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif


    {{-- Filter / Selector --}}
    <div class="denomination-filter-card">

        <form
            method="GET"
            action="{{ route('settings.denominations.index') }}"
            class="denomination-filter-form"
        >

            {{-- Currency --}}
            <div class="filter-field">

                <label for="currency_id">
                    Mata Uang
                </label>

                <select
                    id="currency_id"
                    name="currency_id"
                    onchange="this.form.submit()"
                >

                    @foreach($currencies as $currency)

                        <option
                            value="{{ $currency->id }}"
                            @selected((int) $selectedCurrencyId === (int) $currency->id)
                        >
                            {{ $currency->flag ? $currency->flag . ' ' : '' }}
                            {{ $currency->code }}
                            — {{ $currency->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Variant --}}
            <div class="filter-field">

                <label for="currency_variant_id">
                    Variant
                </label>

                <select
                    id="currency_variant_id"
                    name="variant_id"
                    onchange="this.form.submit()"
                >

                    @forelse($variants as $variant)

                        <option
                            value="{{ $variant->id }}"
                            @selected((int) $selectedVariantId === (int) $variant->id)
                        >
                            {{ $variant->name }}

                            @if($variant->code)
                                — {{ $variant->code }}
                            @endif
                        </option>

                    @empty

                        <option value="">
                            Belum ada variant
                        </option>

                    @endforelse

                </select>

            </div>


            {{-- Currency Info --}}
            @php
                $selectedCurrency = $currencies->firstWhere('id', $selectedCurrencyId);
                $selectedVariant = $variants->firstWhere('id', $selectedVariantId);
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

                        <span class="info-separator">/</span>
                    @endif

                    @if($selectedVariant)
                        <span>
                            {{ $selectedVariant->name }}
                        </span>
                    @endif

                </div>

            </div>

        </form>

    </div>


    {{-- Main Card --}}
    <div class="denomination-card">

        {{-- Card Header --}}
        <div class="denomination-card-header">

            <div>

                <div class="card-title">
                    Daftar Pecahan
                </div>

                <div class="card-subtitle">
                    Pecahan yang tersedia untuk
                    <strong>
                        {{ $selectedCurrency?->code ?? '-' }}
                    </strong>
                    /
                    <strong>
                        {{ $selectedVariant?->name ?? '-' }}
                    </strong>
                </div>

            </div>

            @if($selectedVariant)

                <button
                    type="button"
                    class="btn-primary"
                    onclick="openAddDenominationModal()"
                >
                    <span>＋</span>
                    Tambah Denomination
                </button>

            @endif

        </div>


        {{-- Table --}}
        <div class="denomination-table-wrapper">

            <table class="denomination-table">

                <thead>

                    	<tr>
    				<th class="col-no">NO</th>
    				<th class="col-value">NILAI PECAHAN</th>
    				<th class="col-type">JENIS</th>
    				<th class="col-label">LABEL</th>
    				<th class="col-status">STATUS</th>
    				<th class="col-order">URUTAN</th>
    				<th class="col-action">AKSI</th>
		   	</tr>

                </thead>

                <tbody>

                    @forelse($denominations as $index => $denomination)

                        <tr>

                            <td class="text-center">
                                {{ $index + 1 }}
                            </td>

                            <td>

                                <div class="denomination-value">

                                    @if($selectedCurrency)
                                        <span class="value-currency">
                                            {{ $selectedCurrency->code }}
                                        </span>
                                    @endif

                                    <strong>
                                        {{ $denomination->display_label }}
                                    </strong>

                                </div>
                            </td>

                            <td>
                                @if($denomination->isBanknote())
                                    <span class="type-badge type-banknote">
                                        Banknote
                                    </span>
                                @else
                                    <span class="type-badge type-coin">
                                        Coin
                                    </span>
                                @endif
                            </td>

                            <td>

                                @if($denomination->label)
                                    <span class="label-main">
                                        {{ $denomination->label }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        —
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if($denomination->is_active)

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
                                {{ $denomination->sort_order }}
                            </td>

                            <td>

                                <div class="action-group">

                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        class="action-button action-edit"
                                        title="Edit"
                                        onclick="openEditDenominationModal(
                                            {{ $denomination->id }},
                                            '{{ addslashes($denomination->value) }}',
                                            '{{ addslashes($denomination->type) }}',
                                            '{{ addslashes($denomination->label ?? '') }}',
                                            '{{ addslashes($denomination->description ?? '') }}',
                                            {{ $denomination->sort_order }}
                                        )"
                                    >
                                        ✎
                                    </button>


                                    {{-- Toggle --}}
                                    <form
                                        method="POST"
                                        action="{{ route('settings.denominations.toggle', $denomination) }}"
                                        class="inline-form"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="action-button {{ $denomination->is_active ? 'action-disable' : 'action-enable' }}"
                                            title="{{ $denomination->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        >
                                            {{ $denomination->is_active ? '●' : '○' }}
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
                                    Belum ada denomination
                                </div>

                                <div class="empty-description">
                                    Belum ada pecahan yang dikonfigurasi
                                    untuk currency dan variant ini.
                                </div>

                                @if($selectedVariant)

                                    <button
                                        type="button"
                                        class="btn-primary empty-button"
                                        onclick="openAddDenominationModal()"
                                    >
                                        ＋ Tambah Denomination
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


{{-- ============================================================
     ADD MODAL
     ============================================================ --}}

<div
    class="denomination-modal"
    id="addDenominationModal"
    aria-hidden="true"
>

    <div
        class="denomination-modal-backdrop"
        onclick="closeAddDenominationModal()"
    ></div>

    <div class="denomination-modal-dialog">

        <div class="modal-header">

            <div>

                <div class="modal-title">
                    Tambah Denomination
                </div>

                <div class="modal-subtitle">

                    @if($selectedCurrency)
                        {{ $selectedCurrency->flag }}
                        {{ $selectedCurrency->code }}
                    @endif

                    @if($selectedVariant)
                        / {{ $selectedVariant->name }}
                    @endif

                </div>

            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closeAddDenominationModal()"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            action="{{ route('settings.denominations.store') }}"
        >

            @csrf

            <input
                type="hidden"
                name="currency_variant_id"
                value="{{ $selectedVariantId }}"
            >


            <div class="modal-body">

                <div class="form-field">

                    <label for="add_value">
                        Nilai Pecahan <span>*</span>
                    </label>

                    <input
                        type="number"
                        id="add_value"
                        name="value"
                        step="0.000001"
                        min="0.000001"
                        placeholder="Contoh: 100"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="add_type">
                        Jenis <span>*</span>
                    </label>

                    <select
                        id="add_type"
                        name="type"
                        required
                    >
                        <option value="banknote">Banknote</option>
                        <option value="coin">Coin</option>
                    </select>

                    <div class="form-help">
                        Banknote untuk uang kertas, Coin untuk uang logam.
                    </div>

                </div>


                <div class="form-field">

                    <label for="add_label">
                        Label
                    </label>

                    <input
                        type="text"
                        id="add_label"
                        name="label"
                        maxlength="50"
                        placeholder="Contoh: USD 100"
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
                        placeholder="Keterangan tambahan..."
                    ></textarea>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeAddDenominationModal()"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Simpan Denomination
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ============================================================
     EDIT MODAL
     ============================================================ --}}

<div
    class="denomination-modal"
    id="editDenominationModal"
    aria-hidden="true"
>

    <div
        class="denomination-modal-backdrop"
        onclick="closeEditDenominationModal()"
    ></div>

    <div class="denomination-modal-dialog">

        <div class="modal-header">

            <div>

                <div class="modal-title">
                    Edit Denomination
                </div>

                <div class="modal-subtitle">
                    Ubah konfigurasi pecahan
                </div>

            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closeEditDenominationModal()"
            >
                ×
            </button>

        </div>


        <form
            method="POST"
            id="editDenominationForm"
        >

            @csrf
            @method('PUT')


            <div class="modal-body">

                <div class="form-field">

                    <label for="edit_value">
                        Nilai Pecahan <span>*</span>
                    </label>

                    <input
                        type="number"
                        id="edit_value"
                        name="value"
                        step="0.000001"
                        min="0.000001"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="edit_type">
                        Jenis <span>*</span>
                    </label>

                    <select
                        id="edit_type"
                        name="type"
                        required
                    >
                        <option value="banknote">Banknote</option>
                        <option value="coin">Coin</option>
                    </select>

                    <div class="form-help">
                        Banknote untuk uang kertas, Coin untuk uang logam.
                    </div>

                </div>


                <div class="form-field">

                    <label for="edit_label">
                        Label
                    </label>

                    <input
                        type="text"
                        id="edit_label"
                        name="label"
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

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeEditDenominationModal()"
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

    .denomination-page {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0 0 20px;
        font-size: var(--ui-font-size);
    }


    /* =========================================================
       HEADER
       ========================================================= */

    .denomination-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 14px;
    }

    .denomination-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--ui-primary-dark);
        line-height: 1.25;
    }

    .denomination-subtitle {
        margin-top: 3px;
        color: var(--ui-text-muted);
        font-size: 11px;
    }

    .denomination-header-status {
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


    /* =========================================================
       ALERT
       ========================================================= */

    .denomination-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 9px 12px;
        margin-bottom: 12px;
        border-radius: var(--ui-card-radius);
        font-size: 11px;
        line-height: 1.5;
    }

    .denomination-alert-success {
        color: #176b4d;
        background: var(--ui-success-light);
        border: 1px solid #cfe9da;
    }

    .denomination-alert-danger {
        color: #9c3d35;
        background: var(--ui-danger-light);
        border: 1px solid #efd0cd;
    }

    .alert-icon {
        font-weight: 800;
    }


    /* =========================================================
       FILTER
       ========================================================= */

    .denomination-filter-card {
        background: var(--ui-surface);
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-card-radius);
        margin-bottom: 12px;
    }

    .denomination-filter-form {
        display: grid;
        grid-template-columns: minmax(230px, 1fr) minmax(200px, 1fr) minmax(240px, 1.2fr);
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
    .form-field select,
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
    .form-field input,
    .form-field select {
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
    .form-field select:focus,
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


    /* =========================================================
       CARD
       ========================================================= */

    .denomination-card {
        background: var(--ui-card-bg);
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-card-radius);
        overflow: hidden;
    }

    .denomination-card-header {
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


    /* =========================================================
       BUTTON
       ========================================================= */

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


    /* =========================================================
       TABLE
       ========================================================= */

    .denomination-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .denomination-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .denomination-table th {
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

    .denomination-table td {
        height: 43px;
        padding: 5px 10px;
        border-bottom: 1px solid var(--ui-table-border);
        color: var(--ui-text);
        font-size: var(--ui-table-font-size);
        vertical-align: middle;
    }

    .denomination-table tbody tr:hover {
        background: var(--ui-table-row-hover);
    }

    .denomination-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .col-no {
        width: 55px;
    }

    .col-value {
        width: 190px;
    }

    .col-type {
        width: 105px;
    }

    .col-label {
        width: 220px;
    }

    .col-status {
        width: 120px;
    }

    .col-order {
        width: 80px;
    }

    .col-action {
        width: 110px;
    }

    .text-center {
        text-align: center !important;
    }

    .text-muted {
        color: var(--ui-text-muted);
    }

    .denomination-value {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .value-currency {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        padding: 2px 5px;
        border-radius: 3px;
        background: var(--ui-primary-light);
        color: var(--ui-primary-dark);
        font-size: 9px;
        font-weight: 800;
    }

    .denomination-value strong {
        color: var(--ui-text);
        font-size: 13px;
        font-weight: 800;
    }

    .label-main {
        font-weight: 600;
    }

    .type-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 68px;
        padding: 4px 7px;
        border-radius: var(--ui-badge-radius);
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .type-banknote {
        background: #eef5ff;
        color: #2563eb;
        border: 1px solid #d8e7ff;
    }

    .type-coin {
        background: #fff7e8;
        color: #b7791f;
        border: 1px solid #f3dfb4;
    }


    /* =========================================================
       STATUS
       ========================================================= */

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


    /* =========================================================
       ACTION
       ========================================================= */

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

    .action-disable {
        color: var(--ui-warning);
    }

    .action-enable {
        color: var(--ui-success);
    }


    /* =========================================================
       EMPTY
       ========================================================= */

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


    /* =========================================================
       MODAL
       ========================================================= */

    .denomination-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .denomination-modal.is-open {
        display: flex;
    }

    .denomination-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 40, 31, .38);
    }

    .denomination-modal-dialog {
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

    .form-help {
        margin-top: 4px;
        color: var(--ui-text-muted);
        font-size: 9px;
        line-height: 1.4;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 12px 16px;
        border-top: 1px solid var(--ui-border);
        background: var(--ui-surface-soft);
    }


    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 900px) {

        .denomination-filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .filter-info {
            grid-column: 1 / -1;
        }

    }


    @media (max-width: 640px) {

        .denomination-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .denomination-filter-form {
            grid-template-columns: 1fr;
        }

        .filter-info {
            grid-column: auto;
        }

        .denomination-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .denomination-card-header .btn-primary {
            width: 100%;
        }

    }

</style>


<script>

    function openAddDenominationModal() {

        const modal = document.getElementById('addDenominationModal');

        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        const valueInput = document.getElementById('add_value');

        if (valueInput) {
            valueInput.focus();
        }

    }


    function closeAddDenominationModal() {

        const modal = document.getElementById('addDenominationModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

    }


    function openEditDenominationModal(
        id,
        value,
        type,
        label,
        description,
        sortOrder
    ) {

        const modal = document.getElementById('editDenominationModal');
        const form = document.getElementById('editDenominationForm');

        if (!modal || !form) {
            return;
        }

        form.action =
            "{{ url('/pengaturan/master-denomination') }}/" + id;

        document.getElementById('edit_value').value = value;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_label').value = label;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_sort_order').value = sortOrder;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        document.getElementById('edit_value').focus();

    }


    function closeEditDenominationModal() {

        const modal = document.getElementById('editDenominationModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

    }


    document.addEventListener('keydown', function(event) {

        if (event.key !== 'Escape') {
            return;
        }

        closeAddDenominationModal();
        closeEditDenominationModal();

    });

</script>

@endsection
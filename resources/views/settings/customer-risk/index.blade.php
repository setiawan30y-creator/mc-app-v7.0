@extends('layouts.app')

@section('title', 'Master Customer')
@section('page-title', $type === 'nationality' ? 'Warga Negara' : 'Bidang Pekerjaan')

@section('content')

@php
    $isNationality = $type === 'nationality';

    $pageTitle = $isNationality
        ? 'Warga Negara'
        : 'Bidang Pekerjaan';

    $pageDescription = $isNationality
        ? 'Master kewarganegaraan customer dan referensi risiko.'
        : 'Master bidang pekerjaan customer dan referensi risiko.';
@endphp

<div class="customer-risk-page">

    {{-- =========================================================
        TAB NAVIGATION
    ========================================================== --}}
    <div class="customer-risk-tabs">

        <a
            href="{{ route('settings.company.edit') }}"
            class="customer-risk-tab"
        >
            <span class="customer-risk-tab-icon">🏢</span>
            <span>Profil Perusahaan</span>
        </a>

        <a
            href="{{ route('settings.customer-risk.index', ['type' => 'occupation']) }}"
            class="customer-risk-tab {{ !$isNationality ? 'active' : '' }}"
        >
            <span class="customer-risk-tab-icon">💼</span>
            <span>Bidang Pekerjaan</span>
        </a>

        <a
            href="{{ route('settings.customer-risk.index', ['type' => 'nationality']) }}"
            class="customer-risk-tab {{ $isNationality ? 'active' : '' }}"
        >
            <span class="customer-risk-tab-icon">🌐</span>
            <span>Warga Negara</span>
        </a>

    </div>


    {{-- =========================================================
        ALERT
    ========================================================== --}}
    @if(session('success'))
        <div class="customer-risk-alert customer-risk-alert-success">
            <span class="customer-risk-alert-icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="customer-risk-alert customer-risk-alert-error">
            <span class="customer-risk-alert-icon">!</span>

            <div>
                <strong>Data belum dapat disimpan.</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="customer-risk-header">

        <div>
            <h1>{{ $pageTitle }}</h1>
            <p>{{ $pageDescription }}</p>
        </div>

        <div class="customer-risk-summary">
            <span class="customer-risk-summary-label">TOTAL DATA</span>
            <strong>{{ $items->total() }}</strong>
        </div>

    </div>


    {{-- =========================================================
        FORM TAMBAH DATA
    ========================================================== --}}
    <div class="customer-risk-card">

        <div class="customer-risk-card-header">

            <div class="customer-risk-card-title">
                <span class="customer-risk-card-icon">＋</span>

                <div>
                    <h2>Tambah {{ $pageTitle }}</h2>
                    <p>Tambahkan master baru beserta referensi risiko.</p>
                </div>
            </div>

        </div>


        <form
            method="POST"
            action="{{ route('settings.customer-risk.store') }}"
            class="customer-risk-form"
        >
            @csrf

            <input
                type="hidden"
                name="type"
                value="{{ $type }}"
            >

            <div class="customer-risk-form-grid">

                {{-- NAMA --}}
                <div class="customer-risk-field customer-risk-field-wide">

                    <label for="name">
                        {{ $isNationality ? 'Nama Warga Negara' : 'Nama Bidang Pekerjaan' }}
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ $isNationality ? 'Contoh: Indonesia' : 'Contoh: Pegawai Swasta' }}"
                        required
                    >

                </div>


                {{-- CODE --}}
                <div class="customer-risk-field">

                    <label for="code">
                        Kode
                    </label>

                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="{{ $isNationality ? 'Contoh: ID' : 'Contoh: PS' }}"
                    >

                </div>


                {{-- RISK LEVEL --}}
                <div class="customer-risk-field">

                    <label for="risk_level">
                        Risk Level
                        <span>*</span>
                    </label>

                    <select
                        id="risk_level"
                        name="risk_level"
                        required
                    >
                        <option value="low" {{ old('risk_level', 'low') === 'low' ? 'selected' : '' }}>
                            Low
                        </option>

                        <option value="medium" {{ old('risk_level') === 'medium' ? 'selected' : '' }}>
                            Medium
                        </option>

                        <option value="high" {{ old('risk_level') === 'high' ? 'selected' : '' }}>
                            High
                        </option>
                    </select>

                </div>


                {{-- RISK SCORE --}}
                <div class="customer-risk-field">

                    <label for="risk_score">
                        Risk Score
                        <span>*</span>
                    </label>

                    <input
                        type="number"
                        id="risk_score"
                        name="risk_score"
                        value="{{ old('risk_score', 0) }}"
                        min="0"
                        max="100"
                        required
                    >

                </div>


                {{-- DESCRIPTION --}}
                <div class="customer-risk-field customer-risk-field-wide">

                    <label for="description">
                        Keterangan
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="2"
                        placeholder="Keterangan atau catatan referensi risiko..."
                    >{{ old('description') }}</textarea>

                </div>


                {{-- STATUS --}}
                <div class="customer-risk-field customer-risk-status-field">

                    <label>
                        Status
                    </label>

                    <label class="customer-risk-checkbox">

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                        >

                        <span>Aktif</span>

                    </label>

                </div>

            </div>


            <div class="customer-risk-form-actions">

                <button
                    type="submit"
                    class="customer-risk-button customer-risk-button-primary"
                >
                    <span>＋</span>
                    Simpan Master
                </button>

            </div>

        </form>

    </div>


    {{-- =========================================================
        TABLE DATA
    ========================================================== --}}
    <div class="customer-risk-card">

        <div class="customer-risk-card-header">

            <div class="customer-risk-card-title">

                <span class="customer-risk-card-icon">
                    ☷
                </span>

                <div>
                    <h2>Daftar {{ $pageTitle }}</h2>
                    <p>
                        Data master {{ strtolower($pageTitle) }}
                        yang digunakan sebagai referensi customer dan risk engine.
                    </p>
                </div>

            </div>

        </div>


        <div class="customer-risk-table-wrap">

            <table class="customer-risk-table">

                <thead>
                    <tr>
                        <th style="width: 55px;">No</th>

                        <th>
                            {{ $isNationality ? 'Warga Negara' : 'Bidang Pekerjaan' }}
                        </th>

                        <th style="width: 100px;">
                            Kode
                        </th>

                        <th style="width: 110px;">
                            Risk Level
                        </th>

                        <th style="width: 100px;">
                            Score
                        </th>

                        <th style="width: 90px;">
                            Status
                        </th>

                        <th style="width: 150px;">
                            Dibuat
                        </th>

                        <th style="width: 160px;">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($items as $item)

                        <tr>

                            <td class="text-center">
                                {{ $items->firstItem() + $loop->index }}
                            </td>


                            <td>

                                <div class="customer-risk-name">
                                    <strong>{{ $item->name }}</strong>

                                    @if($item->description)
                                        <small>
                                            {{ $item->description }}
                                        </small>
                                    @endif
                                </div>

                            </td>


                            <td>
                                {{ $item->code ?: '—' }}
                            </td>


                            <td>

                                @php
                                    $riskClass = match($item->risk_level) {
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        default => 'success',
                                    };
                                @endphp

                                <span class="customer-risk-badge customer-risk-badge-{{ $riskClass }}">
                                    {{ ucfirst($item->risk_level) }}
                                </span>

                            </td>


                            <td>

@php
    $scoreClass = match (true) {
        $item->risk_score >= 70 => 'high',
        $item->risk_score >= 30 => 'medium',
        default => 'low',
    };
@endphp

<strong class="customer-risk-score customer-risk-score-{{ $scoreClass }}">
    {{ $item->risk_score }}
</strong>

                            </td>


                            <td>

                                @if($item->is_active)

                                    <span class="customer-risk-status active">
                                        Aktif
                                    </span>

                                @else

                                    <span class="customer-risk-status inactive">
                                        Nonaktif
                                    </span>

                                @endif

                            </td>


                            <td>
                                {{ $item->created_at?->format('d/m/Y H:i') }}
                            </td>


                            <td>

                                <div class="customer-risk-actions">

                                    {{-- EDIT --}}
                                    <form
                                        method="POST"
                                        action="{{ route('settings.customer-risk.update', $item) }}"
                                        class="customer-risk-inline-form"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input
                                            type="hidden"
                                            name="type"
                                            value="{{ $item->type }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="name"
                                            value="{{ $item->name }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="code"
                                            value="{{ $item->code }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="risk_level"
                                            value="{{ $item->risk_level }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="risk_score"
                                            value="{{ $item->risk_score }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="description"
                                            value="{{ $item->description }}"
                                        >

                                        <input
                                            type="hidden"
                                            name="is_active"
                                            value="{{ $item->is_active ? '1' : '0' }}"
                                        >

                                        <button
                                            type="submit"
                                            class="customer-risk-action-button"
                                            title="Simpan ulang"
                                        >
                                            ✎
                                        </button>

                                    </form>


                                    {{-- NONAKTIF --}}
                                    @if($item->is_active)

                                        <form
                                            method="POST"
                                            action="{{ route('settings.customer-risk.destroy', $item) }}"
                                            class="customer-risk-inline-form"
                                            onsubmit="return confirm('Nonaktifkan data ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="customer-risk-action-button customer-risk-action-danger"
                                                title="Nonaktifkan"
                                            >
                                                ⏻
                                            </button>

                                        </form>

                                    @else

                                        <span
                                            class="customer-risk-action-disabled"
                                            title="Data sudah nonaktif"
                                        >
                                            —
                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="customer-risk-empty"
                            >

                                <div class="customer-risk-empty-icon">
                                    ○
                                </div>

                                <strong>
                                    Belum ada data
                                </strong>

                                <span>
                                    Silakan tambahkan
                                    {{ strtolower($pageTitle) }}
                                    menggunakan form di atas.
                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}
        @if($items->hasPages())

            <div class="customer-risk-pagination">

                {{ $items->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =============================================================
     PAGE STYLE
============================================================= --}}
<style>

.customer-risk-page {
    width: 100%;
    font-size: 12px;
}


/* =============================================================
   TABS
============================================================= */

.customer-risk-tabs {
    display: flex;
    align-items: stretch;
    gap: 2px;
    margin-bottom: 12px;
    border-bottom: 1px solid var(--ui-border);
}

.customer-risk-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 15px;
    color: var(--ui-text-secondary);
    background: #f7faf8;
    border: 1px solid transparent;
    border-bottom: none;
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
    transition: .15s ease;
}

.customer-risk-tab:hover {
    color: var(--ui-primary);
    background: var(--ui-primary-light);
}

.customer-risk-tab.active {
    color: var(--ui-primary-dark);
    background: #ffffff;
    border-color: var(--ui-border);
    position: relative;
}

.customer-risk-tab.active::after {
    content: "";
    position: absolute;
    left: -1px;
    right: -1px;
    bottom: -1px;
    height: 2px;
    background: var(--ui-gold);
}

.customer-risk-tab-icon {
    font-size: 13px;
}


/* =============================================================
   ALERT
============================================================= */

.customer-risk-alert {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 9px 11px;
    margin-bottom: 12px;
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-card-radius);
    font-size: 11px;
}

.customer-risk-alert-success {
    color: #176b4d;
    background: #edf9f2;
    border-color: #cce8d8;
}

.customer-risk-alert-error {
    color: #9b352d;
    background: #fff2f1;
    border-color: #f0cfcb;
}

.customer-risk-alert-icon {
    font-weight: 900;
}

.customer-risk-alert ul {
    margin: 5px 0 0;
    padding-left: 17px;
}


/* =============================================================
   HEADER
============================================================= */

.customer-risk-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 12px;
}

.customer-risk-header h1 {
    margin: 0;
    color: var(--ui-text);
    font-size: 16px;
    font-weight: 800;
}

.customer-risk-header p {
    margin: 3px 0 0;
    color: var(--ui-text-muted);
    font-size: 10px;
}

.customer-risk-summary {
    min-width: 90px;
    padding: 7px 10px;
    text-align: right;
    border-left: 2px solid var(--ui-gold);
    background: #fafcfb;
}

.customer-risk-summary-label {
    display: block;
    color: var(--ui-text-muted);
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .5px;
}

.customer-risk-summary strong {
    display: block;
    margin-top: 1px;
    color: var(--ui-primary-dark);
    font-size: 16px;
}


/* =============================================================
   CARD
============================================================= */

.customer-risk-card {
    margin-bottom: 12px;
    background: var(--ui-card-bg);
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-card-radius);
    overflow: hidden;
}

.customer-risk-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: var(--ui-surface-soft);
    border-bottom: 1px solid var(--ui-border-light);
}

.customer-risk-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.customer-risk-card-icon {
    width: 27px;
    height: 27px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    color: var(--ui-primary-dark);
    background: var(--ui-primary-light);
    font-size: 13px;
    font-weight: 900;
}

.customer-risk-card-header h2 {
    margin: 0;
    color: var(--ui-text);
    font-size: 12px;
    font-weight: 800;
}

.customer-risk-card-header p {
    margin: 2px 0 0;
    color: var(--ui-text-muted);
    font-size: 9px;
}


/* =============================================================
   FORM
============================================================= */

.customer-risk-form {
    padding: 11px 12px;
}

.customer-risk-form-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(120px, .8fr) minmax(130px, .9fr) minmax(100px, .7fr);
    gap: 9px 12px;
    align-items: end;
}

.customer-risk-field {
    min-width: 0;
}

.customer-risk-field-wide {
    grid-column: span 2;
}

.customer-risk-field label {
    display: block;
    margin-bottom: 5px;
    color: var(--ui-text-secondary);
    font-size: 10px;
    font-weight: 800;
}

.customer-risk-field label > span {
    color: var(--ui-danger);
}

.customer-risk-field input,
.customer-risk-field select,
.customer-risk-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--ui-border-dark);
    border-radius: var(--ui-input-radius);
    background: #ffffff;
    color: var(--ui-text);
    font-family: inherit;
    font-size: 11px;
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.customer-risk-field input,
.customer-risk-field select {
    height: var(--ui-input-height);
    padding: 0 9px;
}

.customer-risk-field textarea {
    min-height: 54px;
    padding: 7px 9px;
    resize: vertical;
}

.customer-risk-field input:focus,
.customer-risk-field select:focus,
.customer-risk-field textarea:focus {
    border-color: var(--ui-border-focus);
    box-shadow: 0 0 0 2px rgba(50, 148, 107, .08);
}

.customer-risk-status-field {
    align-self: center;
}

.customer-risk-checkbox {
    display: inline-flex !important;
    align-items: center;
    gap: 7px;
    margin: 0 !important;
    height: 34px;
    padding: 0 9px;
    border: 1px solid var(--ui-border);
    border-radius: var(--ui-input-radius);
    background: #fafcfb;
    cursor: pointer;
}

.customer-risk-checkbox input {
    width: auto !important;
    height: auto !important;
    margin: 0;
}

.customer-risk-checkbox span {
    color: var(--ui-primary-dark);
    font-size: 10px;
    font-weight: 800;
}

.customer-risk-form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
    padding-top: 9px;
    border-top: 1px solid var(--ui-border-light);
}

.customer-risk-button {
    height: 32px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 13px;
    border: 1px solid transparent;
    border-radius: var(--ui-button-radius);
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    cursor: pointer;
}

.customer-risk-button-primary {
    color: #ffffff;
    background: var(--ui-primary);
    border-color: var(--ui-primary-dark);
}

.customer-risk-button-primary:hover {
    background: var(--ui-primary-dark);
}


/* =============================================================
   TABLE
============================================================= */

.customer-risk-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.customer-risk-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    font-size: 11px;
}

.customer-risk-table th {
    padding: 8px 9px;
    text-align: left;
    color: var(--ui-text-secondary);
    background: var(--ui-table-header-bg);
    border-bottom: 1px solid var(--ui-table-border);
    white-space: nowrap;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .25px;
}

.customer-risk-table td {
    padding: 8px 9px;
    color: var(--ui-text-secondary);
    border-bottom: 1px solid var(--ui-table-border);
    vertical-align: middle;
}

.customer-risk-table tbody tr:hover {
    background: var(--ui-table-row-hover);
}

.customer-risk-table tbody tr:last-child td {
    border-bottom: none;
}

.text-center {
    text-align: center !important;
}

.customer-risk-name {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.customer-risk-name strong {
    color: var(--ui-text);
    font-size: 11px;
}

.customer-risk-name small {
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--ui-text-muted);
    font-size: 9px;
}


/* =============================================================
   BADGES
============================================================= */

.customer-risk-badge {
    display: inline-flex;
    align-items: center;
    min-width: 52px;
    justify-content: center;
    padding: 3px 6px;
    border-radius: var(--ui-badge-radius);
    font-size: 9px;
    font-weight: 900;
}

.customer-risk-badge-success {
    color: #176b4d;
    background: var(--ui-success-light);
}

.customer-risk-badge-warning {
    color: #91630f;
    background: var(--ui-warning-light);
}

.customer-risk-badge-danger {
    color: #9b352d;
    background: var(--ui-danger-light);
}

.customer-risk-score {
    color: var(--ui-text);
    font-size: 12px;
}

/* =============================================================
   RISK LEVEL & SCORE
============================================================= */

.customer-risk-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-width: 62px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 900;
    line-height: 1;
    white-space: nowrap;
}

.customer-risk-badge::before {
    content: "";
    width: 6px;
    height: 6px;
    flex: 0 0 6px;
    border-radius: 50%;
}


/* LOW */

.customer-risk-badge-success {
    color: #176b4d;
    background: #e9f7ef;
    border: 1px solid #c9e8d5;
}

.customer-risk-badge-success::before {
    background: #21a366;
}


/* MEDIUM */

.customer-risk-badge-warning {
    color: #8a620d;
    background: #fff7df;
    border: 1px solid #efdca8;
}

.customer-risk-badge-warning::before {
    background: #c58a18;
}


/* HIGH */

.customer-risk-badge-danger {
    color: #a23830;
    background: #fff0ee;
    border: 1px solid #efcbc7;
}

.customer-risk-badge-danger::before {
    background: #c24a3d;
}


/* SCORE */

.customer-risk-score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 23px;
    padding: 0 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 900;
    box-sizing: border-box;
}

.customer-risk-score-low {
    color: #176b4d;
    background: #e9f7ef;
    border: 1px solid #c9e8d5;
}

.customer-risk-score-medium {
    color: #8a620d;
    background: #fff7df;
    border: 1px solid #efdca8;
}

.customer-risk-score-high {
    color: #a23830;
    background: #fff0ee;
    border: 1px solid #efcbc7;
}

.customer-risk-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.customer-risk-status.active {
    color: var(--ui-success);
}

.customer-risk-status.inactive {
    color: var(--ui-danger);
}


/* =============================================================
   ACTIONS
============================================================= */

.customer-risk-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}

.customer-risk-inline-form {
    display: inline-flex;
    margin: 0;
}

.customer-risk-action-button {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid var(--ui-border);
    border-radius: 4px;
    color: var(--ui-primary-dark);
    background: #ffffff;
    cursor: pointer;
    font-size: 12px;
}

.customer-risk-action-button:hover {
    background: var(--ui-primary-light);
    border-color: var(--ui-border-dark);
}

.customer-risk-action-danger {
    color: var(--ui-danger);
}

.customer-risk-action-danger:hover {
    background: var(--ui-danger-light);
}

.customer-risk-action-disabled {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-text-muted);
}


/* =============================================================
   EMPTY
============================================================= */

.customer-risk-empty {
    padding: 35px 15px !important;
    text-align: center;
}

.customer-risk-empty-icon {
    margin-bottom: 5px;
    color: var(--ui-gold);
    font-size: 22px;
}

.customer-risk-empty strong {
    display: block;
    color: var(--ui-text);
    font-size: 11px;
}

.customer-risk-empty span {
    display: block;
    margin-top: 3px;
    color: var(--ui-text-muted);
    font-size: 9px;
}


/* =============================================================
   PAGINATION
============================================================= */

.customer-risk-pagination {
    padding: 8px 12px;
    border-top: 1px solid var(--ui-border-light);
    background: var(--ui-surface-soft);
}

.customer-risk-pagination nav {
    display: flex;
    justify-content: flex-end;
}

.customer-risk-pagination svg {
    width: 14px;
    height: 14px;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 900px) {

    .customer-risk-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-risk-field-wide {
        grid-column: span 2;
    }

}

@media (max-width: 650px) {

    .customer-risk-tabs {
        overflow-x: auto;
    }

    .customer-risk-tab {
        white-space: nowrap;
    }

    .customer-risk-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .customer-risk-form-grid {
        grid-template-columns: 1fr;
    }

    .customer-risk-field-wide {
        grid-column: span 1;
    }

    .customer-risk-summary {
        width: 100%;
        box-sizing: border-box;
        text-align: left;
    }

}

</style>

@endsection
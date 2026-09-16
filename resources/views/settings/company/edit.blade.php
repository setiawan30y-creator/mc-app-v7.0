@extends('layouts.app')

@section('title', 'Profil Perusahaan')
@section('page-title', 'Profil Perusahaan')

@section('content')
<div class="company-settings-page">

    <div class="settings-header">
        <div>
        </div>

        <div class="settings-header-badge">
            <span class="badge-dot"></span>
            Konfigurasi Aktif
        </div>
    </div>

    @if(session('success'))
        <div class="settings-alert settings-alert-success">
            <span class="alert-icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="settings-alert settings-alert-error">
            <span class="alert-icon">!</span>
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

    <form
        method="POST"
        action="{{ route('settings.company.update') }}"
        class="company-settings-form"
    >
        @csrf
        @method('PUT')

        {{-- PROFIL UTAMA --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon emerald">🏢</div>
                <div>
                    <h2>Identitas Perusahaan</h2>
                    <p>Informasi utama yang digunakan oleh sistem.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group form-group-wide">
                    <label for="company_name">
                        Nama Perusahaan <span>*</span>
                    </label>
                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old('company_name', $setting->company_name) }}"
                        placeholder="Contoh: PT Almara Valuta"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="company_short_name">Nama Singkat</label>
                    <input
                        type="text"
                        id="company_short_name"
                        name="company_short_name"
                        value="{{ old('company_short_name', $setting->company_short_name) }}"
                        placeholder="Contoh: ALMARA"
                    >
                </div>

            </div>
        </section>

        {{-- LEGALITAS --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon gold">⚖</div>
                <div>
                    <h2>Legalitas & Identitas Regulator</h2>
                    <p>Data legalitas perusahaan dan identitas regulator.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group">
                    <label for="idpjk">IDPJK</label>
                    <input
                        type="text"
                        id="idpjk"
                        name="idpjk"
                        value="{{ old('idpjk', $setting->idpjk) }}"
                        placeholder="Masukkan IDPJK"
                    >
                    <small>
                        Digunakan sebagai identitas perusahaan pada data operasional.
                    </small>
                </div>

                <div class="form-group">
                    <label for="npwp">NPWP</label>
                    <input
                        type="text"
                        id="npwp"
                        name="npwp"
                        value="{{ old('npwp', $setting->npwp) }}"
                        placeholder="Masukkan NPWP"
                    >
                </div>

                <div class="form-group">
                    <label for="license_number">Nomor Izin / Legalitas</label>
                    <input
                        type="text"
                        id="license_number"
                        name="license_number"
                        value="{{ old('license_number', $setting->license_number) }}"
                        placeholder="Nomor izin"
                    >
                </div>

            </div>
        </section>

        {{-- ALAMAT --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon blue">⌂</div>
                <div>
                    <h2>Alamat Perusahaan</h2>
                    <p>Alamat resmi perusahaan untuk kebutuhan operasional dan dokumen.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group form-group-full">
                    <label for="address">Alamat</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="Alamat lengkap perusahaan"
                    >{{ old('address', $setting->address) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="city">Kota</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city', $setting->city) }}"
                        placeholder="Kota"
                    >
                </div>

                <div class="form-group">
                    <label for="province">Provinsi</label>
                    <input
                        type="text"
                        id="province"
                        name="province"
                        value="{{ old('province', $setting->province) }}"
                        placeholder="Provinsi"
                    >
                </div>

                <div class="form-group">
                    <label for="postal_code">Kode Pos</label>
                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        value="{{ old('postal_code', $setting->postal_code) }}"
                        placeholder="Kode pos"
                    >
                </div>

                <div class="form-group">
                    <label for="country">Negara <span>*</span></label>
                    <input
                        type="text"
                        id="country"
                        name="country"
                        value="{{ old('country', $setting->country ?: 'Indonesia') }}"
                        required
                    >
                </div>

            </div>
        </section>

        {{-- KONTAK --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon purple">☎</div>
                <div>
                    <h2>Kontak Perusahaan</h2>
                    <p>Informasi komunikasi resmi perusahaan.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group">
                    <label for="phone">Nomor Telepon</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $setting->phone) }}"
                        placeholder="Contoh: 021xxxxxxx"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $setting->email) }}"
                        placeholder="email@perusahaan.com"
                    >
                </div>

                <div class="form-group form-group-wide">
                    <label for="website">Website</label>
                    <input
                        type="text"
                        id="website"
                        name="website"
                        value="{{ old('website', $setting->website) }}"
                        placeholder="https://..."
                    >
                </div>

            </div>
        </section>

        {{-- SISTEM --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon dark">⚙</div>
                <div>
                    <h2>Pengaturan Sistem</h2>
                    <p>Konfigurasi dasar yang digunakan oleh tenant.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group">
                    <label for="timezone">Timezone <span>*</span></label>
                    <select id="timezone" name="timezone" required>
                        <option
                            value="Asia/Jakarta"
                            @selected(old('timezone', $setting->timezone) === 'Asia/Jakarta')
                        >
                            Asia/Jakarta (WIB)
                        </option>
                        <option
                            value="Asia/Makassar"
                            @selected(old('timezone', $setting->timezone) === 'Asia/Makassar')
                        >
                            Asia/Makassar (WITA)
                        </option>
                        <option
                            value="Asia/Jayapura"
                            @selected(old('timezone', $setting->timezone) === 'Asia/Jayapura')
                        >
                            Asia/Jayapura (WIT)
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="locale">Bahasa <span>*</span></label>
                    <select id="locale" name="locale" required>
                        <option
                            value="id"
                            @selected(old('locale', $setting->locale) === 'id')
                        >
                            Bahasa Indonesia
                        </option>
                        <option
                            value="en"
                            @selected(old('locale', $setting->locale) === 'en')
                        >
                            English
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="default_currency">Mata Uang Utama <span>*</span></label>
                    <select id="default_currency" name="default_currency" required>
                        <option
                            value="IDR"
                            @selected(old('default_currency', $setting->default_currency) === 'IDR')
                        >
                            IDR — Indonesian Rupiah
                        </option>
                        <option
                            value="USD"
                            @selected(old('default_currency', $setting->default_currency) === 'USD')
                        >
                            USD — US Dollar
                        </option>
                        <option
                            value="SGD"
                            @selected(old('default_currency', $setting->default_currency) === 'SGD')
                        >
                            SGD — Singapore Dollar
                        </option>
                    </select>
                </div>

            </div>
        </section>

        {{-- CETAK --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <div class="settings-card-icon gold">▤</div>
                <div>
                    <h2>Dokumen & Struk</h2>
                    <p>Informasi tambahan yang dapat ditampilkan pada dokumen transaksi.</p>
                </div>
            </div>

            <div class="settings-grid">

                <div class="form-group">
                    <label for="receipt_header">Header Struk</label>
                    <textarea
                        id="receipt_header"
                        name="receipt_header"
                        rows="4"
                        placeholder="Teks yang ditampilkan pada bagian atas struk"
                    >{{ old('receipt_header', $setting->receipt_header) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="receipt_footer">Footer Struk</label>
                    <textarea
                        id="receipt_footer"
                        name="receipt_footer"
                        rows="4"
                        placeholder="Teks yang ditampilkan pada bagian bawah struk"
                    >{{ old('receipt_footer', $setting->receipt_footer) }}</textarea>
                </div>

            </div>
        </section>

        <div class="settings-save-bar">
            <div>
                <strong>Simpan konfigurasi perusahaan</strong>
                <span>Perubahan akan berlaku untuk tenant yang sedang aktif.</span>
            </div>

            <button type="submit" class="settings-save-button">
                <span>✓</span>
                Simpan Pengaturan
            </button>
        </div>

    </form>
</div>

<style>
.company-settings-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 0 20px;
    font-size: 12px;
}

.settings-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.settings-eyebrow {
    color: #a77a19;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.4px;
    margin-bottom: 5px;
}

.settings-header h1 {
    margin: 0;
    color: #173c2e;
    font-size: 25px;
    font-weight: 800;
}

.settings-header p {
    margin: 6px 0 0;
    color: #72847b;
    font-size: 13px;
}

.settings-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border: 1px solid #d9e8e0;
    border-radius: 20px;
    background: #f8fbf9;
    color: #34705a;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #21a366;
}

.settings-alert {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 12px 14px;
    margin-bottom: 16px;
    border-radius: 8px;
    font-size: 13px;
}

.settings-alert-success {
    background: #edf9f2;
    border: 1px solid #ccebd8;
    color: #176b45;
}

.settings-alert-error {
    background: #fff2f1;
    border: 1px solid #f1ceca;
    color: #9b3028;
}

.alert-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    border-radius: 50%;
    background: currentColor;
    color: white;
    font-weight: 800;
    font-size: 12px;
}

.settings-alert ul {
    margin: 5px 0 0 18px;
    padding: 0;
}

.company-settings-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.settings-card {
    background: #ebe8e8;
    border: 1px solid #053a1d;
    border-radius: 5px;
    box-shadow: 0 3px 12px rgba(27, 66, 51, 0.05);
    overflow: hidden;
}

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-bottom: 1px solid #e8eeeb;
    background: #e7faf9;
}

.settings-card-icon {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    font-size: 13px;
    flex-shrink: 0;
}

.settings-card-icon.emerald {
    background: #e5f4ec;
    color: #176b4d;
}

.settings-card-icon.gold {
    background: #f8efd9;
    color: #9a7018;
}

.settings-card-icon.blue {
    background: #e8f0fb;
    color: #4269a8;
}

.settings-card-icon.purple {
    background: #eeeafb;
    color: #6852a9;
}

.settings-card-icon.dark {
    background: #e8eceb;
    color: #334e45;
}

.settings-card-header h2 {
    margin: 0;
    color: #25483b;
    font-size: 14px;
    font-weight: 800;
}

.settings-card-header p {
    margin: 2px 0 0;
    color: #7a8982;
    font-size: 11px;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 9px 14px;
    padding: 11px 12px;
}

.form-group {
    min-width: 0;
    margin: 0;
}

.form-group-wide {
    grid-column: span 1;
}

.form-group-full {
    grid-column: 1 / -1;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    color: #40594f;
    font-size: 11px;
    font-weight: 800;
}

.form-group label span {
    color: #c24a3d;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d6e1dc;
    border-radius: 7px;
    background: #fff;
    color: #263f35;
    font-family: inherit;
    font-size: 12px;
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.form-group input,
.form-group select {
    height: 38px;
    padding: 0 11px;
}

.form-group textarea {
    padding: 10px 11px;
    min-height: 82px;
    resize: vertical;
    line-height: 1.5;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #32946b;
    box-shadow: 0 0 0 3px rgba(50, 148, 107, .10);
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #87958f;
    font-size: 10px;
}

.settings-save-bar {
    position: sticky;
    bottom: 12px;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 13px 15px;
    margin-top: 2px;
    background: rgba(255, 255, 255, .96);
    border: 1px solid #dbe8e2;
    border-radius: 10px;
    box-shadow: 0 7px 22px rgba(24, 63, 47, .10);
    backdrop-filter: blur(8px);
}

.settings-save-bar strong {
    display: block;
    color: #25483b;
    font-size: 12px;
}

.settings-save-bar span {
    display: block;
    margin-top: 3px;
    color: #819089;
    font-size: 10px;
}

.settings-save-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-width: 170px;
    height: 38px;
    padding: 0 17px;
    border: 0;
    border-radius: 7px;
    background: linear-gradient(135deg, #176b4d, #29986b);
    color: white;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(23, 107, 77, .20);
}

.settings-save-button:hover {
    filter: brightness(1.04);
    transform: translateY(-1px);
}

@media (max-width: 800px) {
    .company-settings-page {
        padding: 0 2px 25px;
    }

    .settings-header {
        flex-direction: column;
    }

    .settings-grid {
        grid-template-columns: 1fr;
    }

    .form-group-wide,
    .form-group-full {
        grid-column: auto;
    }

    .settings-save-bar {
        align-items: stretch;
        flex-direction: column;
    }

    .settings-save-button {
        width: 100%;
    }
}
</style>
@endsection





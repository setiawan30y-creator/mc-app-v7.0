@extends('layouts.app')

@section('title', 'Tampilan & Tema')
@section('page-title', 'Tampilan & Tema')

@section('content')
<div class="theme-page">
    <div class="theme-header">
        <div>
            <h1>Tampilan & Tema</h1>
            <p>Atur warna, mode, radius, dan kepadatan interface MC Almara.</p>
        </div>
        <button type="button" class="theme-reset" id="themeReset">Reset</button>
    </div>

    <div class="theme-layout">
        <section class="theme-card">
            <div class="theme-card-title">Mode Tampilan</div>
            <div class="theme-options" data-theme-mode>
                <button type="button" data-mode="light">☀️ <span>Light</span></button>
                <button type="button" data-mode="dark">🌙 <span>Dark</span></button>
                <button type="button" data-mode="system">🌓 <span>System</span></button>
            </div>

            <div class="theme-card-title section-gap">Preset Warna</div>
            <div class="theme-presets" data-theme-preset>
                <button type="button" data-preset="emerald"><i></i>Emerald</button>
                <button type="button" data-preset="gold"><i></i>Gold</button>
                <button type="button" data-preset="blue"><i></i>Blue</button>
                <button type="button" data-preset="slate"><i></i>Slate</button>
            </div>

            <div class="theme-card-title section-gap">Interface</div>
            <label class="theme-select-label">Radius
                <select id="themeRadius">
                    <option value="sharp">Sharp</option>
                    <option value="medium">Medium</option>
                    <option value="rounded">Rounded</option>
                </select>
            </label>
            <label class="theme-select-label">Density
                <select id="themeDensity">
                    <option value="compact">Compact</option>
                    <option value="comfortable">Comfortable</option>
                </select>
            </label>
        </section>

        <section class="theme-card preview-card">
            <div class="theme-card-title">Live Preview</div>
            <div class="theme-preview-shell">
                <div class="theme-preview-sidebar">MC<br><small>ALMARA</small></div>
                <div class="theme-preview-main">
                    <div class="theme-preview-top">Dashboard <span>● User</span></div>
                    <div class="theme-preview-content">
                        <div class="theme-preview-box"><small>TRANSAKSI HARI INI</small><strong>24</strong></div>
                        <div class="theme-preview-box"><small>STOK VALAS</small><strong>Rp 125 jt</strong></div>
                        <button class="theme-preview-button">Transaksi Baru</button>
                    </div>
                </div>
            </div>
            <p class="theme-note">Perubahan disimpan di browser ini dan langsung diterapkan ke seluruh halaman yang menggunakan Global UI Design System.</p>
        </section>
    </div>
</div>

<style>
.theme-page{max-width:1100px;margin:0 auto;color:var(--ui-text)}.theme-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px}.theme-header h1{margin:0;font-size:18px}.theme-header p{margin:4px 0 0;color:var(--ui-text-muted);font-size:11px}.theme-reset{height:32px;padding:0 13px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-card-bg);color:var(--ui-text-secondary);cursor:pointer;font-size:10px;font-weight:800}.theme-layout{display:grid;grid-template-columns:360px minmax(0,1fr);gap:14px}.theme-card{background:var(--ui-card-bg);border:1px solid var(--ui-border);border-radius:var(--ui-card-radius);padding:16px;box-shadow:var(--ui-card-shadow)}.theme-card-title{font-size:12px;font-weight:800}.section-gap{margin-top:20px}.theme-options{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:9px}.theme-options button,.theme-presets button{min-height:38px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-surface-soft);color:var(--ui-text-secondary);cursor:pointer;font-size:10px;font-weight:750}.theme-options button.active,.theme-presets button.active{border-color:var(--ui-gold);box-shadow:inset 0 0 0 1px var(--ui-gold);color:var(--ui-primary-dark)}.theme-presets{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:9px}.theme-presets button{display:flex;align-items:center;gap:8px;padding:0 10px}.theme-presets i{width:16px;height:16px;border-radius:50%;background:var(--preset);display:block}.theme-presets button:nth-child(1){--preset:#176b4d}.theme-presets button:nth-child(2){--preset:#a77a19}.theme-presets button:nth-child(3){--preset:#3267a8}.theme-presets button:nth-child(4){--preset:#596575}.theme-select-label{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:9px;color:var(--ui-text-secondary);font-size:10px;font-weight:750}.theme-select-label select{width:150px;height:34px;border:1px solid var(--ui-border-dark);border-radius:var(--ui-input-radius);background:var(--ui-surface);color:var(--ui-text);padding:0 8px}.theme-preview-shell{display:flex;min-height:270px;margin-top:10px;overflow:hidden;border:1px solid var(--ui-border);border-radius:var(--ui-card-radius);background:var(--ui-bg)}.theme-preview-sidebar{width:115px;padding:18px 12px;background:var(--ui-sidebar-bg);color:#fff;font-size:16px;font-weight:900}.theme-preview-sidebar small{font-size:7px;opacity:.7}.theme-preview-main{flex:1;min-width:0}.theme-preview-top{height:48px;padding:0 13px;display:flex;align-items:center;justify-content:space-between;background:var(--ui-topbar-bg);border-bottom:1px solid var(--ui-border);font-size:11px;font-weight:800}.theme-preview-top span{font-size:8px;color:var(--ui-text-muted)}.theme-preview-content{display:grid;grid-template-columns:1fr 1fr;gap:9px;padding:14px}.theme-preview-box{padding:12px;border:1px solid var(--ui-border);border-radius:var(--ui-card-radius);background:var(--ui-card-bg)}.theme-preview-box small{display:block;color:var(--ui-text-muted);font-size:7px}.theme-preview-box strong{display:block;margin-top:6px;font-size:16px;color:var(--ui-primary-dark)}.theme-preview-button{grid-column:1/-1;height:34px;border:0;border-radius:var(--ui-button-radius);background:var(--ui-primary);color:#fff;font-size:10px;font-weight:800}.theme-note{margin:10px 0 0;color:var(--ui-text-muted);font-size:9px;line-height:1.5}@media(max-width:800px){.theme-layout{grid-template-columns:1fr}.theme-preview-shell{min-height:220px}.theme-header{align-items:flex-start}.theme-select-label select{width:130px}}@media(max-width:480px){.theme-options{grid-template-columns:1fr}.theme-presets{grid-template-columns:1fr}.theme-preview-sidebar{width:78px}.theme-preview-content{grid-template-columns:1fr}.theme-preview-button{grid-column:auto}}
</style>
@endsection

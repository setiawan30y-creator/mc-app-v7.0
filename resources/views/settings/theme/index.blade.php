@extends('layouts.app')

@section('title', 'Tampilan & Tema')
@section('page-title', 'Tampilan & Tema')

@section('content')
<div class="theme-page" id="themeManager">
    <div class="theme-header">
        <div><h1>Tampilan & Tema</h1><p>Atur warna halaman, kartu, border, icon, shadow, radius, dan mode MC Almara.</p></div>
        <button type="button" class="theme-reset" id="themeReset">Reset Default</button>
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

            <div class="theme-card-title section-gap">Warna Interface</div>
            <div class="theme-color-grid">
                <label>Warna Halaman <span><input type="color" data-token="--ui-page-bg"><code data-value="--ui-page-bg"></code></span></label>
                <label>Warna Kartu <span><input type="color" data-token="--ui-card-bg"><code data-value="--ui-card-bg"></code></span></label>
                <label>Warna Surface <span><input type="color" data-token="--ui-surface-soft"><code data-value="--ui-surface-soft"></code></span></label>
                <label>Border Kartu <span><input type="color" data-token="--ui-card-border"><code data-value="--ui-card-border"></code></span></label>
                <label>Border Umum <span><input type="color" data-token="--ui-border"><code data-value="--ui-border"></code></span></label>
                <label>Icon <span><input type="color" data-token="--ui-icon"><code data-value="--ui-icon"></code></span></label>
                <label>Icon Aktif <span><input type="color" data-token="--ui-icon-active"><code data-value="--ui-icon-active"></code></span></label>
                <label>Teks <span><input type="color" data-token="--ui-text"><code data-value="--ui-text"></code></span></label>
                <label>Teks Sekunder <span><input type="color" data-token="--ui-text-secondary"><code data-value="--ui-text-secondary"></code></span></label>
                <label>Primary <span><input type="color" data-token="--ui-primary"><code data-value="--ui-primary"></code></span></label>
                <label>Gold / Accent <span><input type="color" data-token="--ui-gold"><code data-value="--ui-gold"></code></span></label>
                <label>Topbar <span><input type="color" data-token="--ui-topbar-bg"><code data-value="--ui-topbar-bg"></code></span></label>
            </div>

            <div class="theme-card-title section-gap">Efek Interface</div>
            <label class="theme-select-label">Shadow Kartu<select id="themeShadow"><option value="none">Tidak ada</option><option value="soft">Soft</option><option value="medium">Medium</option><option value="strong">Strong</option></select></label>
            <label class="theme-select-label">Radius Kartu<select id="themeRadius"><option value="sharp">Sharp</option><option value="medium">Medium</option><option value="rounded">Rounded</option></select></label>
            <label class="theme-select-label">Radius Input<select id="themeInputRadius"><option value="sharp">Sharp</option><option value="medium">Medium</option><option value="rounded">Rounded</option></select></label>
            <label class="theme-select-label">Density<select id="themeDensity"><option value="compact">Compact</option><option value="comfortable">Comfortable</option></select></label>
        </section>

        <section class="theme-card preview-card">
            <div class="theme-card-title">Live Preview</div>
            <div class="theme-preview-shell">
                <div class="theme-preview-sidebar"><strong>MC</strong><small>ALMARA</small><span>▣ Dashboard</span><span>↔ Transaksi</span><span>◉ Nasabah</span></div>
                <div class="theme-preview-main">
                    <div class="theme-preview-top">Dashboard <span>● User</span></div>
                    <div class="theme-preview-content">
                        <div class="theme-preview-box"><small>TRANSAKSI HARI INI</small><strong>24</strong><em>+12%</em></div>
                        <div class="theme-preview-box"><small>STOK VALAS</small><strong>Rp 125 jt</strong><em>Aktif</em></div>
                        <div class="theme-preview-table"><div><b>Currency</b><b>Rate</b><b>Stock</b></div><div><span>USD</span><span>16.450</span><span>125</span></div><div><span>SGD</span><span>12.950</span><span>80</span></div></div>
                        <button class="theme-preview-button">Transaksi Baru</button>
                    </div>
                </div>
            </div>
            <p class="theme-note">Semua perubahan disimpan di browser ini dan diterapkan melalui Global UI Design System.</p>
        </section>
    </div>
</div>

<style>
.theme-page{max-width:1180px;margin:0 auto;color:var(--ui-text)}.theme-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px}.theme-header h1{margin:0;font-size:18px}.theme-header p{margin:4px 0 0;color:var(--ui-text-muted);font-size:11px}.theme-reset{height:32px;padding:0 13px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-card-bg);color:var(--ui-text-secondary);cursor:pointer;font-size:10px;font-weight:800}.theme-layout{display:grid;grid-template-columns:minmax(420px,500px) minmax(0,1fr);gap:14px;align-items:start}.theme-card{background:var(--ui-card-bg);border:1px solid var(--ui-card-border);border-radius:var(--ui-card-radius);padding:16px;box-shadow:var(--ui-card-shadow)}.theme-card-title{font-size:12px;font-weight:800}.section-gap{margin-top:20px}.theme-options{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:9px}.theme-options button,.theme-presets button{min-height:38px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-surface-soft);color:var(--ui-text-secondary);cursor:pointer;font-size:10px;font-weight:750}.theme-options button.active,.theme-presets button.active{border-color:var(--ui-gold);box-shadow:inset 0 0 0 1px var(--ui-gold);color:var(--ui-primary-dark)}.theme-presets{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:9px}.theme-presets button{display:flex;align-items:center;gap:8px;padding:0 10px}.theme-presets i{width:16px;height:16px;border-radius:50%;background:var(--preset);display:block}.theme-presets button:nth-child(1){--preset:#176b4d}.theme-presets button:nth-child(2){--preset:#a77a19}.theme-presets button:nth-child(3){--preset:#3267a8}.theme-presets button:nth-child(4){--preset:#596575}.theme-color-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:9px}.theme-color-grid label{display:flex;align-items:center;justify-content:space-between;gap:7px;min-height:36px;padding:5px 7px 5px 9px;border:1px solid var(--ui-border);border-radius:var(--ui-input-radius);background:var(--ui-surface-soft);color:var(--ui-text-secondary);font-size:9px;font-weight:750}.theme-color-grid label span{display:flex;align-items:center;gap:5px}.theme-color-grid input[type=color]{width:25px;height:25px;padding:2px;border:1px solid var(--ui-border-dark);border-radius:5px;background:transparent;cursor:pointer}.theme-color-grid code{min-width:52px;color:var(--ui-text-muted);font-size:8px}.theme-select-label{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:9px;color:var(--ui-text-secondary);font-size:10px;font-weight:750}.theme-select-label select{width:160px;height:34px;border:1px solid var(--ui-border-dark);border-radius:var(--ui-input-radius);background:var(--ui-surface);color:var(--ui-text);padding:0 8px}.theme-preview-shell{display:flex;min-height:390px;margin-top:10px;overflow:hidden;border:1px solid var(--ui-border);border-radius:var(--ui-card-radius);background:var(--ui-page-bg);box-shadow:var(--ui-card-shadow)}.theme-preview-sidebar{width:130px;flex:0 0 130px;padding:17px 11px;background:var(--ui-sidebar-bg);color:var(--ui-sidebar-text);font-size:10px;font-weight:700;display:flex;flex-direction:column;gap:12px}.theme-preview-sidebar strong{font-size:19px}.theme-preview-sidebar small{font-size:7px;opacity:.7;margin-top:-9px}.theme-preview-main{flex:1;min-width:0}.theme-preview-top{height:48px;padding:0 13px;display:flex;align-items:center;justify-content:space-between;background:var(--ui-topbar-bg);border-bottom:1px solid var(--ui-border);font-size:11px;font-weight:800}.theme-preview-top span{font-size:8px;color:var(--ui-text-muted)}.theme-preview-content{display:grid;grid-template-columns:1fr 1fr;gap:9px;padding:14px}.theme-preview-box{padding:12px;border:1px solid var(--ui-card-border);border-radius:var(--ui-card-radius);background:var(--ui-card-bg);box-shadow:var(--ui-card-shadow)}.theme-preview-box small{display:block;color:var(--ui-text-muted);font-size:7px}.theme-preview-box strong{display:block;margin-top:6px;font-size:16px;color:var(--ui-primary-dark)}.theme-preview-box em{font-style:normal;font-size:8px;color:var(--ui-success)}.theme-preview-table{grid-column:1/-1;overflow:hidden;border:1px solid var(--ui-card-border);border-radius:var(--ui-card-radius);background:var(--ui-card-bg);box-shadow:var(--ui-card-shadow);font-size:8px}.theme-preview-table div{display:grid;grid-template-columns:1fr 1fr 1fr;padding:8px 9px;border-bottom:1px solid var(--ui-border)}.theme-preview-table div:last-child{border-bottom:0}.theme-preview-table div:first-child{background:var(--ui-table-header-bg);color:var(--ui-text-secondary)}.theme-preview-button{grid-column:1/-1;height:34px;border:0;border-radius:var(--ui-button-radius);background:var(--ui-primary);color:#fff;font-size:10px;font-weight:800;cursor:pointer}.theme-note{margin:10px 0 0;color:var(--ui-text-muted);font-size:9px;line-height:1.5}@media(max-width:950px){.theme-layout{grid-template-columns:1fr}}@media(max-width:600px){.theme-color-grid,.theme-options,.theme-presets{grid-template-columns:1fr}.theme-header{align-items:flex-start}.theme-preview-sidebar{width:82px;flex-basis:82px}.theme-preview-content{grid-template-columns:1fr}.theme-preview-table,.theme-preview-button{grid-column:auto}.theme-select-label select{width:140px}}
</style>

<script>
(function(){
const root=document.documentElement,key='mc-almara-theme';
const defaults={mode:'light',preset:'emerald',radius:'medium',inputRadius:'medium',density:'compact',shadow:'none',colors:{'--ui-page-bg':'#f5f7f6','--ui-card-bg':'#ffffff','--ui-surface-soft':'#fafcfb','--ui-card-border':'#dfe8e3','--ui-border':'#dfe8e3','--ui-icon':'#176b4d','--ui-icon-active':'#a77a19','--ui-text':'#25483b','--ui-text-secondary':'#40594f','--ui-primary':'#176b4d','--ui-gold':'#a77a19','--ui-topbar-bg':'#ffffff'}};
const presets={emerald:{primary:'#176b4d',gold:'#a77a19',page:'#f5f7f6',card:'#ffffff',surface:'#fafcfb',border:'#dfe8e3',text:'#25483b',secondary:'#40594f',icon:'#176b4d',active:'#a77a19'},gold:{primary:'#7d641d',gold:'#b18a2c',page:'#f7f5ef',card:'#fffdf7',surface:'#fcfaf3',border:'#e6ddc5',text:'#4b422d',secondary:'#675f4c',icon:'#8a6b20',active:'#176b4d'},blue:{primary:'#3267a8',gold:'#b18a2c',page:'#f3f6fa',card:'#ffffff',surface:'#f8fafc',border:'#d9e1eb',text:'#263f5e',secondary:'#53667c',icon:'#3267a8',active:'#a77a19'},slate:{primary:'#596575',gold:'#a77a19',page:'#f1f3f5',card:'#ffffff',surface:'#f7f8f9',border:'#d8dde3',text:'#303943',secondary:'#5c6672',icon:'#596575',active:'#a77a19'}};
const shadows={none:'none',soft:'0 2px 6px rgba(0,0,0,.05)',medium:'0 4px 12px rgba(0,0,0,.08)',strong:'0 8px 24px rgba(0,0,0,.14)'},radii={sharp:'2px',medium:'5px',rounded:'12px'};
function state(){try{return JSON.parse(localStorage.getItem(key))||structuredClone(defaults)}catch(e){return structuredClone(defaults)}}
function apply(s){root.dataset.themeMode=s.mode;root.dataset.themePreset=s.preset;const dark=s.mode==='dark'||(s.mode==='system'&&matchMedia('(prefers-color-scheme: dark)').matches);root.classList.toggle('theme-dark',dark);Object.entries(s.colors).forEach(([k,v])=>root.style.setProperty(k,v));root.style.setProperty('--ui-card-radius',radii[s.radius]);root.style.setProperty('--ui-input-radius',radii[s.inputRadius]);root.style.setProperty('--ui-card-shadow',shadows[s.shadow]);if(s.density==='comfortable'){root.style.setProperty('--ui-table-cell-padding-y','9px');root.style.setProperty('--ui-table-cell-padding-x','10px');root.style.setProperty('--ui-input-height','38px')}else{root.style.setProperty('--ui-table-cell-padding-y','6px');root.style.setProperty('--ui-table-cell-padding-x','8px');root.style.setProperty('--ui-input-height','34px')}document.querySelectorAll('[data-mode]').forEach(b=>b.classList.toggle('active',b.dataset.mode===s.mode));document.querySelectorAll('[data-preset]').forEach(b=>b.classList.toggle('active',b.dataset.preset===s.preset));document.getElementById('themeRadius').value=s.radius;document.getElementById('themeInputRadius').value=s.inputRadius;document.getElementById('themeDensity').value=s.density;document.getElementById('themeShadow').value=s.shadow;document.querySelectorAll('[data-token]').forEach(i=>{let v=getComputedStyle(root).getPropertyValue(i.dataset.token).trim();if(/^#[0-9a-f]{6}$/i.test(v))i.value=v;i.closest('label').querySelector('[data-value]').textContent=v});localStorage.setItem(key,JSON.stringify(s))}
function setState(fn){const s=state();fn(s);apply(s)}
function preset(name){const p=presets[name];setState(s=>{s.preset=name;Object.assign(s.colors,{'--ui-primary':p.primary,'--ui-gold':p.gold,'--ui-page-bg':p.page,'--ui-card-bg':p.card,'--ui-surface-soft':p.surface,'--ui-card-border':p.border,'--ui-border':p.border,'--ui-text':p.text,'--ui-text-secondary':p.secondary,'--ui-icon':p.icon,'--ui-icon-active':p.active,'--ui-topbar-bg':p.card})})}
document.querySelectorAll('[data-mode]').forEach(b=>b.addEventListener('click',()=>setState(s=>s.mode=b.dataset.mode)));document.querySelectorAll('[data-preset]').forEach(b=>b.addEventListener('click',()=>preset(b.dataset.preset)));document.querySelectorAll('[data-token]').forEach(i=>i.addEventListener('input',()=>setState(s=>s.colors[i.dataset.token]=i.value)));document.getElementById('themeRadius').addEventListener('change',e=>setState(s=>s.radius=e.target.value));document.getElementById('themeInputRadius').addEventListener('change',e=>setState(s=>s.inputRadius=e.target.value));document.getElementById('themeDensity').addEventListener('change',e=>setState(s=>s.density=e.target.value));document.getElementById('themeShadow').addEventListener('change',e=>setState(s=>s.shadow=e.target.value));document.getElementById('themeReset').addEventListener('click',()=>{localStorage.removeItem(key);apply(structuredClone(defaults))});apply(state());
})();
</script>
@endsection

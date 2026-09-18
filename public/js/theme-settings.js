(() => {
    const presets = {
        emerald: { primary:'#176b4d', primaryDark:'#0f5139', primaryLight:'#e8f3ed', secondary:'#34705a', gold:'#a77a19', goldDark:'#8b6415', goldLight:'#f8efd9', sidebar:'#176b4d' },
        gold: { primary:'#7a5b16', primaryDark:'#5e450f', primaryLight:'#f6edd7', secondary:'#8c702a', gold:'#b58a2a', goldDark:'#8c681d', goldLight:'#fbf3df', sidebar:'#5f4a18' },
        blue: { primary:'#285f91', primaryDark:'#1e466b', primaryLight:'#e7f0f8', secondary:'#477ba8', gold:'#b18a3b', goldDark:'#8d6d2f', goldLight:'#f7efd9', sidebar:'#244f78' },
        slate: { primary:'#4b5968', primaryDark:'#35404c', primaryLight:'#edf0f3', secondary:'#667381', gold:'#a07d36', goldDark:'#80642b', goldLight:'#f5eedf', sidebar:'#3d4854' }
    };
    const defaults = { mode:'light', preset:'emerald', radius:'medium', density:'compact' };
    const root = document.documentElement;
    const key = 'mc-almara-theme';
    const get = () => { try { return {...defaults, ...(JSON.parse(localStorage.getItem(key)) || {})}; } catch { return {...defaults}; } };
    const save = state => localStorage.setItem(key, JSON.stringify(state));
    const apply = state => {
        const p = presets[state.preset] || presets.emerald;
        Object.entries({ '--ui-primary':p.primary,'--ui-primary-dark':p.primaryDark,'--ui-primary-light':p.primaryLight,'--ui-secondary':p.secondary,'--ui-gold':p.gold,'--ui-gold-dark':p.goldDark,'--ui-gold-light':p.goldLight,'--ui-sidebar-bg':p.sidebar }).forEach(([k,v]) => root.style.setProperty(k,v));
        const dark = state.mode === 'dark' || (state.mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
        if (dark) {
            root.style.setProperty('--ui-bg','#17201d'); root.style.setProperty('--ui-surface','#202a26'); root.style.setProperty('--ui-surface-soft','#1b2521'); root.style.setProperty('--ui-card-bg','#202a26'); root.style.setProperty('--ui-text','#edf4f0'); root.style.setProperty('--ui-text-secondary','#c1cec7'); root.style.setProperty('--ui-text-muted','#93a39a'); root.style.setProperty('--ui-border','#35433d'); root.style.setProperty('--ui-border-light','#2b3833'); root.style.setProperty('--ui-border-dark','#46554e'); root.style.setProperty('--ui-table-header-bg','#25312c'); root.style.setProperty('--ui-table-border','#35433d'); root.style.setProperty('--ui-table-border-strong','#46554e'); root.style.setProperty('--ui-table-row-hover','#293730'); root.style.setProperty('--ui-topbar-bg','#202a26');
        } else {
            root.style.removeProperty('--ui-bg'); root.style.removeProperty('--ui-surface'); root.style.removeProperty('--ui-surface-soft'); root.style.removeProperty('--ui-card-bg'); root.style.removeProperty('--ui-text'); root.style.removeProperty('--ui-text-secondary'); root.style.removeProperty('--ui-text-muted'); root.style.removeProperty('--ui-border'); root.style.removeProperty('--ui-border-light'); root.style.removeProperty('--ui-border-dark'); root.style.removeProperty('--ui-table-header-bg'); root.style.removeProperty('--ui-table-border'); root.style.removeProperty('--ui-table-border-strong'); root.style.removeProperty('--ui-table-row-hover'); root.style.removeProperty('--ui-topbar-bg');
        }
        const radius = {sharp:'2px',medium:'5px',rounded:'12px'}[state.radius] || '5px';
        root.style.setProperty('--ui-card-radius', radius); root.style.setProperty('--ui-input-radius', radius); root.style.setProperty('--ui-button-radius', radius);
        root.dataset.uiDensity = state.density;
        document.querySelectorAll('[data-mode]') .forEach(b => b.classList.toggle('active', b.dataset.mode === state.mode));
        document.querySelectorAll('[data-preset]').forEach(b => b.classList.toggle('active', b.dataset.preset === state.preset));
        const r = document.getElementById('themeRadius'); if (r) r.value = state.radius;
        const d = document.getElementById('themeDensity'); if (d) d.value = state.density;
    };
    const state = get(); apply(state);
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-mode]').forEach(b => b.addEventListener('click', () => { const s=get(); s.mode=b.dataset.mode; save(s); apply(s); }));
        document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => { const s=get(); s.preset=b.dataset.preset; save(s); apply(s); }));
        document.getElementById('themeRadius')?.addEventListener('change', e => { const s=get(); s.radius=e.target.value; save(s); apply(s); });
        document.getElementById('themeDensity')?.addEventListener('change', e => { const s=get(); s.density=e.target.value; save(s); apply(s); });
        document.getElementById('themeReset')?.addEventListener('click', () => { save({...defaults}); apply({...defaults}); });
    });
})();

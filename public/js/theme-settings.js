(() => {
    const root = document.documentElement;
    const key = 'mc-almara-theme';

    const presets = {
        emerald: { primary:'#176b4d', primaryDark:'#0f5139', primaryLight:'#e8f3ed', secondary:'#34705a', gold:'#a77a19', goldDark:'#8b6415', goldLight:'#f8efd9', sidebar:'#176b4d' },
        gold: { primary:'#7a5b16', primaryDark:'#5e450f', primaryLight:'#f6edd7', secondary:'#8c702a', gold:'#b58a2a', goldDark:'#8c681d', goldLight:'#fbf3df', sidebar:'#5f4a18' },
        blue: { primary:'#285f91', primaryDark:'#1e466b', primaryLight:'#e7f0f8', secondary:'#477ba8', gold:'#b18a3b', goldDark:'#8d6d2f', goldLight:'#f7efd9', sidebar:'#244f78' },
        slate: { primary:'#4b5968', primaryDark:'#35404c', primaryLight:'#edf0f3', secondary:'#667381', gold:'#a07d36', goldDark:'#80642b', goldLight:'#f5eedf', sidebar:'#3d4854' }
    };

    const defaults = {
        mode:'light', preset:'emerald', radius:'medium', inputRadius:'medium', density:'compact', shadow:'none',
        colors: {
            '--ui-page-bg':'#f5f7f6', '--ui-card-bg':'#ffffff', '--ui-surface-soft':'#fafcfb', '--ui-card-border':'#dfe8e3',
            '--ui-border':'#dfe8e3', '--ui-icon':'#176b4d', '--ui-icon-active':'#a77a19', '--ui-text':'#25483b',
            '--ui-text-secondary':'#40594f', '--ui-primary':'#176b4d', '--ui-gold':'#a77a19', '--ui-topbar-bg':'#ffffff'
        }
    };

    const shadows = { none:'none', soft:'0 2px 6px rgba(0,0,0,.05)', medium:'0 4px 12px rgba(0,0,0,.08)', strong:'0 8px 24px rgba(0,0,0,.14)' };
    const radii = { sharp:'2px', medium:'5px', rounded:'12px' };

    function clone(value) { return JSON.parse(JSON.stringify(value)); }

    function getState() {
        try {
            const saved = JSON.parse(localStorage.getItem(key));
            if (!saved) return clone(defaults);
            return {
                ...clone(defaults),
                ...saved,
                colors: { ...clone(defaults).colors, ...(saved.colors || {}) }
            };
        } catch (_) { return clone(defaults); }
    }

    function save(state) { localStorage.setItem(key, JSON.stringify(state)); }

    function apply(state) {
        const preset = presets[state.preset] || presets.emerald;
        const dark = state.mode === 'dark' || (state.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        const themeColors = {
            '--ui-primary': preset.primary,
            '--ui-primary-dark': preset.primaryDark,
            '--ui-primary-light': preset.primaryLight,
            '--ui-secondary': preset.secondary,
            '--ui-gold': preset.gold,
            '--ui-gold-dark': preset.goldDark,
            '--ui-gold-light': preset.goldLight,
            '--ui-sidebar-bg': preset.sidebar
        };

        Object.entries(themeColors).forEach(([name, value]) => root.style.setProperty(name, value));

        // Apply every custom interface color saved by Theme Manager.
        Object.entries(state.colors || {}).forEach(([name, value]) => root.style.setProperty(name, value));

        // Explicit aliases used by existing pages.
        root.style.setProperty('--ui-bg', state.colors['--ui-page-bg'] || '#f5f7f6');
        root.style.setProperty('--ui-surface', state.colors['--ui-card-bg'] || '#ffffff');
        root.style.setProperty('--ui-card-shadow', shadows[state.shadow] || shadows.none);
        root.style.setProperty('--ui-card-radius', radii[state.radius] || radii.medium);
        root.style.setProperty('--ui-input-radius', radii[state.inputRadius] || radii.medium);
        root.style.setProperty('--ui-button-radius', radii[state.inputRadius] || radii.medium);

        if (dark) {
            root.style.setProperty('--ui-bg','#17201d');
            root.style.setProperty('--ui-surface','#202a26');
            root.style.setProperty('--ui-surface-soft','#1b2521');
            root.style.setProperty('--ui-card-bg','#202a26');
            root.style.setProperty('--ui-text','#edf4f0');
            root.style.setProperty('--ui-text-secondary','#c1cec7');
            root.style.setProperty('--ui-text-muted','#93a39a');
            root.style.setProperty('--ui-border','#35433d');
            root.style.setProperty('--ui-border-light','#2b3833');
            root.style.setProperty('--ui-border-dark','#46554e');
            root.style.setProperty('--ui-table-header-bg','#25312c');
            root.style.setProperty('--ui-table-border','#35433d');
            root.style.setProperty('--ui-table-border-strong','#46554e');
            root.style.setProperty('--ui-table-row-hover','#293730');
            root.style.setProperty('--ui-topbar-bg','#202a26');
        } else {
            root.style.removeProperty('--ui-text-muted');
            root.style.removeProperty('--ui-border-light');
            root.style.removeProperty('--ui-border-dark');
            root.style.removeProperty('--ui-table-header-bg');
            root.style.removeProperty('--ui-table-border');
            root.style.removeProperty('--ui-table-border-strong');
            root.style.removeProperty('--ui-table-row-hover');
            root.classList.remove('theme-dark');
        }

        root.dataset.themeMode = state.mode;
        root.dataset.themePreset = state.preset;
        root.dataset.uiDensity = state.density;
        root.classList.toggle('theme-dark', dark);
    }

    // Apply before first paint so every page receives the saved theme.
    apply(getState());

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-mode]').forEach(button => {
            button.addEventListener('click', () => {
                const state = getState();
                state.mode = button.dataset.mode;
                save(state);
                apply(state);
            });
        });

        document.querySelectorAll('[data-preset]').forEach(button => {
            button.addEventListener('click', () => {
                const name = button.dataset.preset;
                const p = presets[name];
                if (!p) return;
                const state = getState();
                state.preset = name;
                Object.assign(state.colors, {
                    '--ui-primary':p.primary, '--ui-gold':p.gold, '--ui-page-bg':name === 'emerald' ? '#f5f7f6' : name === 'gold' ? '#f7f5ef' : name === 'blue' ? '#f3f6fa' : '#f1f3f5',
                    '--ui-card-bg':name === 'gold' ? '#fffdf7' : '#ffffff', '--ui-surface-soft':name === 'gold' ? '#fcfaf3' : name === 'blue' ? '#f8fafc' : name === 'slate' ? '#f7f8f9' : '#fafcfb',
                    '--ui-card-border':name === 'gold' ? '#e6ddc5' : name === 'blue' ? '#d9e1eb' : name === 'slate' ? '#d8dde3' : '#dfe8e3',
                    '--ui-border':name === 'gold' ? '#e6ddc5' : name === 'blue' ? '#d9e1eb' : name === 'slate' ? '#d8dde3' : '#dfe8e3',
                    '--ui-text':name === 'gold' ? '#4b422d' : name === 'blue' ? '#263f5e' : name === 'slate' ? '#303943' : '#25483b',
                    '--ui-text-secondary':name === 'gold' ? '#675f4c' : name === 'blue' ? '#53667c' : name === 'slate' ? '#5c6672' : '#40594f',
                    '--ui-icon':p.primary, '--ui-icon-active':p.gold, '--ui-topbar-bg':state.colors['--ui-card-bg']
                });
                save(state);
                apply(state);
            });
        });

        document.querySelectorAll('[data-token]').forEach(input => {
            input.addEventListener('input', () => {
                const state = getState();
                state.colors[input.dataset.token] = input.value;
                state.preset = 'custom';
                save(state);
                apply(state);
                const value = input.closest('label')?.querySelector('[data-value]');
                if (value) value.textContent = input.value;
            });
        });

        document.getElementById('themeRadius')?.addEventListener('change', e => { const s=getState(); s.radius=e.target.value; save(s); apply(s); });
        document.getElementById('themeInputRadius')?.addEventListener('change', e => { const s=getState(); s.inputRadius=e.target.value; save(s); apply(s); });
        document.getElementById('themeDensity')?.addEventListener('change', e => { const s=getState(); s.density=e.target.value; save(s); apply(s); });
        document.getElementById('themeShadow')?.addEventListener('change', e => { const s=getState(); s.shadow=e.target.value; save(s); apply(s); });
        document.getElementById('themeReset')?.addEventListener('click', () => { const s=clone(defaults); save(s); apply(s); location.reload(); });

        apply(getState());
    });

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener?.('change', () => { const state=getState(); if(state.mode==='system') apply(state); });
})();

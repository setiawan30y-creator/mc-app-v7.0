(() => {
    const dashboard = document.querySelector('.dashboard');
    if (!dashboard) return;

    const money = (value) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value ?? 0));
    const cards = () => [...dashboard.querySelectorAll('.finance-grid .finance-card')];
    const findCard = (label) => cards().find(card => (card.querySelector('.finance-label')?.textContent || '').trim().replace(/\s+/g, ' ').toLowerCase() === label.toLowerCase());
    const bankCard = () => dashboard.querySelector('[data-dashboard-bank-card]') || findCard('Mutasi Bank') || cards()[2] || null;

    const injectBankStyles = () => {
        if (document.getElementById('dashboard-bank-card-styles')) return;
        const style = document.createElement('style');
        style.id = 'dashboard-bank-card-styles';
        style.textContent = `.dashboard-bank-accounts{margin-top:0}.bank-account-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:16px}.bank-account-card{border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff;min-width:0}.bank-account-top{display:flex;align-items:center;gap:10px}.bank-account-icon{width:34px;height:34px;border-radius:9px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:16px}.bank-account-name{font-size:11px;font-weight:800;color:#111827}.bank-account-number{font-size:9px;color:#9ca3af;margin-top:3px}.bank-account-holder{font-size:9px;color:#64748b;margin-top:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bank-account-balance{font-size:18px;font-weight:850;color:#0f172a;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bank-account-meta{display:flex;justify-content:space-between;gap:8px;margin-top:8px;font-size:8px;color:#64748b}.bank-account-empty,.bank-account-error,.bank-account-loading{padding:22px 16px;font-size:10px;color:#64748b}.bank-account-error{color:#b91c1c}@media(max-width:950px){.bank-account-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.bank-account-grid{grid-template-columns:1fr}}`;
        document.head.appendChild(style);
    };

    const ensureBankAccountsPanel = () => {
        let panel = dashboard.querySelector('[data-dashboard-bank-accounts]');
        if (panel) return panel;
        const financeGrid = dashboard.querySelector('.finance-grid');
        if (!financeGrid) return null;
        panel = document.createElement('section');
        panel.className = 'dashboard-card dashboard-bank-accounts';
        panel.setAttribute('data-dashboard-bank-accounts', 'true');
        panel.innerHTML = `<div class="dashboard-card-header"><div><div class="dashboard-card-title">Rekening Bank</div><div class="dashboard-card-subtitle">Saldo berjalan setiap rekening aktif</div></div><div class="dashboard-card-link">Kas &amp; Bank</div></div><div class="bank-account-grid" data-dashboard-bank-account-grid><div class="bank-account-loading">Memuat rekening bank...</div></div>`;
        financeGrid.insertAdjacentElement('afterend', panel);
        return panel;
    };

    const renderBankAccounts = (accounts) => {
        const panel = ensureBankAccountsPanel();
        if (!panel) return;
        const grid = panel.querySelector('[data-dashboard-bank-account-grid]');
        if (!Array.isArray(accounts) || !accounts.length) {
            grid.innerHTML = '<div class="bank-account-empty">Belum ada rekening bank aktif pada cabang ini.</div>';
            return;
        }
        grid.innerHTML = '';
        accounts.forEach(a => {
            const card = document.createElement('div');
            card.className = 'bank-account-card';
            const n = String(a.account_number || '');
            const masked = n.length > 4 ? '•••• ' + n.slice(-4) : (n || '-');
            card.innerHTML = `<div class="bank-account-top"><div class="bank-account-icon">🏦</div><div><div class="bank-account-name">${a.bank_name || 'Bank'}</div><div class="bank-account-number">${masked}</div></div></div><div class="bank-account-holder">${a.account_name || '-'}</div><div class="bank-account-balance">${money(a.balance)}</div><div class="bank-account-meta"><span>${a.currency || 'IDR'}</span><span>Credit ${money(a.credit)} · Debit ${money(a.debit)}</span></div>`;
            grid.appendChild(card);
        });
    };

    const showBankError = (message) => {
        const panel = ensureBankAccountsPanel();
        if (!panel) return;
        const grid = panel.querySelector('[data-dashboard-bank-account-grid]');
        grid.innerHTML = `<div class="bank-account-error">${message}</div>`;
    };

    const writeCard = (card, value, meta) => {
        if (!card) return;
        card.setAttribute('data-dashboard-bank-card', 'true');
        card.querySelector('.finance-value')?.replaceChildren(document.createTextNode(money(value)));
        const metaEl = card.querySelector('.finance-meta');
        if (metaEl) metaEl.innerHTML = meta;
    };

    const setCard = (label, value, meta = 'Terintegrasi') => {
        const card = findCard(label);
        if (!card) return;
        card.querySelector('.finance-value')?.replaceChildren(document.createTextNode(typeof value === 'string' ? value : money(value)));
        const metaEl = card.querySelector('.finance-meta');
        if (metaEl) metaEl.innerHTML = `<span class="finance-neutral">${meta}</span>`;
    };

    const ensureFinanceCard = (label, icon, metaLeft, metaRight) => {
        let card = findCard(label);
        if (card) return card;
        const grid = dashboard.querySelector('.finance-grid');
        if (!grid) return null;
        card = document.createElement('div');
        card.className = 'finance-card';
        card.innerHTML = `<div class="finance-icon">${icon}</div><div class="finance-label">${label}</div><div class="finance-value">Rp 0</div><div class="finance-meta"><span class="finance-neutral">${metaLeft}</span><span class="finance-neutral">${metaRight}</span></div>`;
        grid.appendChild(card);
        return card;
    };

    const ensureCashRpCard = () => ensureFinanceCard('Cash Rp', 'Rp', 'Saldo tersedia', 'Kas fisik');

    const ensurePositionPanel = () => {
        let panel = dashboard.querySelector('[data-dashboard-position-panel]');
        if (panel) return panel;
        const mainGrid = dashboard.querySelector('.dashboard-main-grid');
        if (!mainGrid) return null;
        panel = document.createElement('div');
        panel.className = 'dashboard-card';
        panel.setAttribute('data-dashboard-position-panel', 'true');
        panel.innerHTML = `<div class="dashboard-card-header"><div><div class="dashboard-card-title">Posisi Kas, Bank dan Valas</div><div class="dashboard-card-subtitle">Posisi berjalan dari Saldo Awal + ledger</div></div></div><div class="summary-box"><div class="summary-row"><span class="summary-label">Kas</span><span class="summary-value" data-dashboard-position="cash">Rp 0</span></div><div class="summary-row"><span class="summary-label">Bank</span><span class="summary-value" data-dashboard-position="bank">Rp 0</span></div><div class="summary-row"><span class="summary-label">Valas (nilai Rp)</span><span class="summary-value" data-dashboard-position="forex">Rp 0</span></div><div class="summary-total"><div class="summary-total-label">Total Posisi</div><div class="summary-total-value" data-dashboard-position="gross">Rp 0</div></div></div>`;
        mainGrid.appendChild(panel);
        return panel;
    };

    const setPosition = (p) => {
        const panel = ensurePositionPanel();
        if (!panel) return;
        panel.querySelector('[data-dashboard-position="cash"]')?.replaceChildren(document.createTextNode(money(p.cash)));
        panel.querySelector('[data-dashboard-position="bank"]')?.replaceChildren(document.createTextNode(money(p.bank)));
        panel.querySelector('[data-dashboard-position="forex"]')?.replaceChildren(document.createTextNode(money(p.forex)));
        panel.querySelector('[data-dashboard-position="gross"]')?.replaceChildren(document.createTextNode(money(p.gross)));
    };

    const ensureOpeningPanel = () => {
        let panel = dashboard.querySelector('[data-dashboard-opening-panel]');
        if (panel) return panel;
        const sideStack = dashboard.querySelector('.side-stack');
        if (!sideStack) return null;
        panel = document.createElement('div');
        panel.className = 'dashboard-card';
        panel.setAttribute('data-dashboard-opening-panel', 'true');
        panel.innerHTML = `<div class="dashboard-card-header"><div><div class="dashboard-card-title">Saldo Awal</div><div class="dashboard-card-subtitle">Saldo opening terakhir yang sudah finalized</div></div></div><div class="summary-box"><div class="summary-row"><span class="summary-label">Tanggal</span><span class="summary-value" data-dashboard-opening="date">Belum ada</span></div><div class="summary-row"><span class="summary-label">Kas</span><span class="summary-value" data-dashboard-opening="cash">Rp 0</span></div><div class="summary-row"><span class="summary-label">Rekening</span><span class="summary-value" data-dashboard-opening="bank">Rp 0</span></div><div class="summary-row"><span class="summary-label">Valas</span><span class="summary-value" data-dashboard-opening="forex">Rp 0</span></div><div class="summary-total"><div class="summary-total-label">Total Saldo Awal</div><div class="summary-total-value" data-dashboard-opening="gross">Rp 0</div></div></div>`;
        sideStack.prepend(panel);
        return panel;
    };

    const setOpening = (o) => {
        const panel = ensureOpeningPanel();
        if (!panel) return;
        panel.querySelector('[data-dashboard-opening="cash"]')?.replaceChildren(document.createTextNode(money(o.cash)));
        panel.querySelector('[data-dashboard-opening="bank"]')?.replaceChildren(document.createTextNode(money(o.bank)));
        panel.querySelector('[data-dashboard-opening="forex"]')?.replaceChildren(document.createTextNode(money(o.forex)));
        panel.querySelector('[data-dashboard-opening="gross"]')?.replaceChildren(document.createTextNode(money(o.gross)));
        panel.querySelector('[data-dashboard-opening="date"]')?.replaceChildren(document.createTextNode(o.date || 'Belum ada saldo awal'));
    };

    const load = async () => {
        injectBankStyles();
        ensureBankAccountsPanel();
        ensureCashRpCard();
        try {
            const response = await fetch('/dashboard/data?_=' + Date.now(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) {
                showBankError(`Dashboard data gagal dimuat (${response.status}).`);
                writeCard(bankCard(), 0, '<span class="finance-negative">Data Dashboard tidak tersedia</span>');
                return;
            }
            const data = await response.json();
            const p = data.position || {}, o = data.opening || {}, t = data.today || {};
            writeCard(bankCard(), p.bank ?? 0, `<span class="finance-neutral">Credit ${money(t.bank_credit)} · Debit ${money(t.bank_debit)}</span>`);
            setCard('Pembelian', t.purchase ?? 0, `${t.transaction_count ?? 0} transaksi hari ini`);
            setCard('Penjualan', t.sales ?? 0, `${t.transaction_count ?? 0} transaksi hari ini`);
            setCard('Pengeluaran', t.cash_out ?? 0, 'Cash out hari ini');
            setCard('Cash Rp', p.cash ?? 0, 'Saldo tersedia · Kas fisik');
            setPosition(p);
            setOpening(o);
            renderBankAccounts(Array.isArray(data.bank_accounts) ? data.bank_accounts : []);
        } catch (error) {
            console.error('Dashboard financial sync failed', error);
            showBankError('Gagal mengambil data rekening bank. Buka Console browser untuk detail error.');
            writeCard(bankCard(), 0, '<span class="finance-negative">Gagal mengambil saldo bank</span>');
        }
    };

    load();
    setInterval(load, 60000);
})();

(() => {
    const dashboard = document.querySelector('.dashboard');
    if (!dashboard) return;

    const money = (value) => {
        const n = Number(value ?? 0);
        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(n);
    };

    const cards = () => [...dashboard.querySelectorAll('.finance-grid .finance-card')];

    const findCard = (label) => cards().find(card =>
        (card.querySelector('.finance-label')?.textContent || '').trim().replace(/\s+/g, ' ').toLowerCase() === label.toLowerCase()
    );

    // Mutasi Bank is a fixed dashboard card. Prefer an explicit marker,
    // then the text label, then the third finance card used by the dashboard layout.
    const bankCard = () => {
        const explicit = dashboard.querySelector('[data-dashboard-bank-card]');
        if (explicit) return explicit;
        return findCard('Mutasi Bank') || cards()[2] || null;
    };

    const writeCard = (card, value, meta) => {
        if (!card) return;
        card.setAttribute('data-dashboard-bank-card', 'true');
        const valueEl = card.querySelector('.finance-value');
        const metaEl = card.querySelector('.finance-meta');
        if (valueEl) valueEl.textContent = money(value);
        if (metaEl) metaEl.innerHTML = meta;
    };

    const setCard = (label, value, meta = 'Terintegrasi') => {
        const card = findCard(label);
        if (!card) return;
        const valueEl = card.querySelector('.finance-value');
        const metaEl = card.querySelector('.finance-meta');
        if (valueEl) valueEl.textContent = typeof value === 'string' ? value : money(value);
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

    const setPosition = (position) => {
        const panel = ensurePositionPanel();
        if (!panel) return;
        panel.querySelector('[data-dashboard-position="cash"]')?.replaceChildren(document.createTextNode(money(position.cash)));
        panel.querySelector('[data-dashboard-position="bank"]')?.replaceChildren(document.createTextNode(money(position.bank)));
        panel.querySelector('[data-dashboard-position="forex"]')?.replaceChildren(document.createTextNode(money(position.forex)));
        panel.querySelector('[data-dashboard-position="gross"]')?.replaceChildren(document.createTextNode(money(position.gross)));
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

    const setOpening = (opening) => {
        const panel = ensureOpeningPanel();
        if (!panel) return;
        panel.querySelector('[data-dashboard-opening="cash"]')?.replaceChildren(document.createTextNode(money(opening.cash)));
        panel.querySelector('[data-dashboard-opening="bank"]')?.replaceChildren(document.createTextNode(money(opening.bank)));
        panel.querySelector('[data-dashboard-opening="forex"]')?.replaceChildren(document.createTextNode(money(opening.forex)));
        panel.querySelector('[data-dashboard-opening="gross"]')?.replaceChildren(document.createTextNode(money(opening.gross)));
        panel.querySelector('[data-dashboard-opening="date"]')?.replaceChildren(document.createTextNode(opening.date || 'Belum ada saldo awal'));
    };

    const load = async () => {
        const bank = bankCard();
        try {
            const response = await fetch('/dashboard/data?_=' + Date.now(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (!response.ok) {
                writeCard(bank, 0, '<span class="finance-negative">Data Dashboard tidak tersedia</span>');
                return;
            }
            const data = await response.json();
            const position = data.position || {};
            const opening = data.opening || {};
            const today = data.today || {};

            ensureCashRpCard();
            const currentBank = Number(position.bank ?? 0);
            writeCard(bankCard(), currentBank, `<span class="finance-neutral">Saldo awal ${money(opening.bank)} · Credit ${money(today.bank_credit)} · Debit ${money(today.bank_debit)}</span>`);

            setCard('Pembelian', today.purchase ?? 0, `${today.transaction_count ?? 0} transaksi hari ini`);
            setCard('Penjualan', today.sales ?? 0, `${today.transaction_count ?? 0} transaksi hari ini`);
            setCard('Pengeluaran', today.cash_out ?? 0, 'Cash out hari ini');
            setCard('Cash Rp', position.cash ?? 0, 'Saldo tersedia · Kas fisik');
            setPosition(position);
            setOpening(opening);
        } catch (error) {
            console.warn('Dashboard financial sync failed', error);
            writeCard(bankCard(), 0, '<span class="finance-negative">Gagal mengambil saldo bank</span>');
        }
    };

    load();
    setInterval(load, 60000);
})();

(() => {
    const dashboard = document.querySelector('.dashboard');
    if (!dashboard) return;

    const money = (value) => {
        const n = Number(value || 0);
        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(n);
    };

    const cards = [...dashboard.querySelectorAll('.finance-card')];
    const cardByLabel = (label) => cards.find(card =>
        card.querySelector('.finance-label')?.textContent.trim().toLowerCase() === label.toLowerCase()
    );

    const setCard = (label, value, meta = 'Terintegrasi') => {
        const card = cardByLabel(label);
        if (!card) return;
        const valueEl = card.querySelector('.finance-value');
        const metaEl = card.querySelector('.finance-meta');
        if (valueEl) valueEl.textContent = typeof value === 'string' ? value : money(value);
        if (metaEl) metaEl.innerHTML = `<span class="finance-neutral">${meta}</span>`;
    };

    const ensureOpeningPanel = () => {
        let panel = dashboard.querySelector('[data-dashboard-opening-panel]');
        if (panel) return panel;

        const sideStack = dashboard.querySelector('.side-stack');
        if (!sideStack) return null;

        panel = document.createElement('div');
        panel.className = 'dashboard-card';
        panel.setAttribute('data-dashboard-opening-panel', 'true');
        panel.innerHTML = `
            <div class="dashboard-card-header">
                <div>
                    <div class="dashboard-card-title">Saldo Awal</div>
                    <div class="dashboard-card-subtitle">Saldo opening terakhir yang sudah finalized</div>
                </div>
            </div>
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Tanggal</span>
                    <span class="summary-value" data-dashboard-opening="date">Belum ada</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Kas</span>
                    <span class="summary-value" data-dashboard-opening="cash">Rp 0</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Rekening</span>
                    <span class="summary-value" data-dashboard-opening="bank">Rp 0</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Valas</span>
                    <span class="summary-value" data-dashboard-opening="forex">Rp 0</span>
                </div>
                <div class="summary-total">
                    <div class="summary-total-label">Total Saldo Awal</div>
                    <div class="summary-total-value" data-dashboard-opening="gross">Rp 0</div>
                </div>
            </div>
        `;
        sideStack.prepend(panel);
        return panel;
    };

    const setOpening = (opening) => {
        const panel = ensureOpeningPanel();
        if (!panel) return;

        const cash = panel.querySelector('[data-dashboard-opening="cash"]');
        const bank = panel.querySelector('[data-dashboard-opening="bank"]');
        const forex = panel.querySelector('[data-dashboard-opening="forex"]');
        const gross = panel.querySelector('[data-dashboard-opening="gross"]');
        const date = panel.querySelector('[data-dashboard-opening="date"]');

        if (cash) cash.textContent = money(opening.cash);
        if (bank) bank.textContent = money(opening.bank);
        if (forex) forex.textContent = money(opening.forex);
        if (gross) gross.textContent = money(opening.gross);
        if (date) date.textContent = opening.date || 'Belum ada saldo awal';
    };

    const load = async () => {
        try {
            const response = await fetch('/dashboard/data', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();

            setCard('Pembelian', data.today.purchase, `${data.today.transaction_count} transaksi hari ini`);
            setCard('Penjualan', data.today.sales, `${data.today.transaction_count} transaksi hari ini`);
            setCard('Mutasi Bank', data.today.bank_net, `Credit ${money(data.today.bank_credit)} · Debit ${money(data.today.bank_debit)}`);

            // Existing Dashboard cards that have a direct backend source.
            setCard('Pengeluaran', data.today.cash_out, 'Cash out hari ini');
            if (data.closing) {
                setCard('Selisih Rp', data.closing.difference, data.closing.balanced ? 'Balanced' : 'Tidak seimbang');
            }

            setOpening(data.opening || {});

            const total = dashboard.querySelector('.summary-total-value');
            if (total) total.textContent = money(data.position.gross);

            dashboard.dataset.financeSyncedAt = new Date().toISOString();
        } catch (error) {
            console.warn('Dashboard financial sync failed', error);
        }
    };

    load();
    setInterval(load, 60000);
})();

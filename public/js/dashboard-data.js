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

    const setSummary = (label, value) => {
        const rows = [...dashboard.querySelectorAll('.summary-row')];
        const row = rows.find(r => r.querySelector('.summary-label')?.textContent.trim().toLowerCase() === label.toLowerCase());
        if (!row) return;
        const el = row.querySelector('.summary-value');
        if (el) el.textContent = money(value);
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
            setCard('Saldo Kas', data.position.cash, 'Saldo berjalan');
            setCard('Stok Valas', data.position.forex, 'Saldo berjalan · dari opening + transaksi');
            setCard('Closing', data.closing ? (data.closing.balanced ? 'BALANCED' : money(data.closing.difference)) : 'Belum Closing', data.closing ? data.closing.status : 'Belum ada closing hari ini');

            setSummary('Kas', data.position.cash);
            setSummary('Rekening', data.position.bank);
            setSummary('Valas', data.position.forex);

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

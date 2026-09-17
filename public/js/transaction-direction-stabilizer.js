document.addEventListener('DOMContentLoaded', () => {
    const getRows = () => document.querySelectorAll('#itemRows > tr');

    // Modul 02 · ITEM TRANSAKSI: warna hanya saat tombol arah aktif.
    if (!document.getElementById('transaction-direction-colors')) {
        const style = document.createElement('style');
        style.id = 'transaction-direction-colors';
        style.textContent = `
            .trx-row-direction-wrap{display:flex;gap:4px}
            .trx-row-direction-btn{
                border:1px solid #d1dbd6;
                background:#fff;
                color:#66736c;
                border-radius:4px;
                padding:3px 5px;
                font-size:8px;
                font-weight:700;
                cursor:pointer;
            }
            .trx-row-direction-btn[data-row-direction="buy"].active{
                background:#198754;
                color:#fff;
                border-color:#198754;
            }
            .trx-row-direction-btn[data-row-direction="sell"].active{
                background:#dc3545;
                color:#fff;
                border-color:#dc3545;
            }
        `;
        document.head.appendChild(style);
    }

    function ensureRowDirection(tr, index) {
        const cells = tr.querySelectorAll('td');
        const cell = cells[1];
        if (!cell) return;

        cell.querySelector('.row-direction')?.remove();
        let hidden = cell.querySelector('.direction-input');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.className = 'direction-input';
            hidden.name = `items[${index}][direction]`;
            hidden.value = 'buy';
            cell.appendChild(hidden);
        }

        let wrap = cell.querySelector('.trx-row-direction-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'trx-row-direction-wrap';
            wrap.innerHTML = `
                <button type="button" class="trx-row-direction-btn" data-row-direction="buy">BELI</button>
                <button type="button" class="trx-row-direction-btn" data-row-direction="sell">JUAL</button>
            `;
            cell.appendChild(wrap);
        }

        const direction = hidden.value === 'sell' ? 'sell' : 'buy';
        wrap.querySelectorAll('[data-row-direction]').forEach(button => {
            button.classList.toggle('active', button.dataset.rowDirection === direction);
        });
    }

    function ensureAllRows() {
        getRows().forEach((tr, index) => ensureRowDirection(tr, index));
    }

    function repairSoon() {
        [0, 50, 150, 300, 600].forEach(delay => setTimeout(ensureAllRows, delay));
    }

    document.addEventListener('click', event => {
        if (event.target.closest('#addItem')) repairSoon();
    }, true);

    const bodyObserver = new MutationObserver(() => repairSoon());
    bodyObserver.observe(document.body, { childList: true, subtree: true });

    repairSoon();

    document.addEventListener('click', event => {
        const button = event.target.closest('.trx-row-direction-btn');
        if (!button) return;
        const row = button.closest('tr');
        const hidden = row?.querySelector('.direction-input');
        if (!hidden) return;
        hidden.value = button.dataset.rowDirection === 'sell' ? 'sell' : 'buy';
        row.querySelectorAll('.trx-row-direction-btn').forEach(item => {
            item.classList.toggle('active', item === button);
        });
        if (typeof refreshRate === 'function') refreshRate(row);
    });
});
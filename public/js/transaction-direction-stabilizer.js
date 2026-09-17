document.addEventListener('DOMContentLoaded', () => {
    const getRows = () => document.querySelectorAll('#itemRows > tr');

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

    // Handle the existing + Tambah Item action without depending on its internal implementation.
    document.addEventListener('click', event => {
        if (event.target.closest('#addItem')) repairSoon();
    }, true);

    // Catch rows inserted into the current tbody.
    const bodyObserver = new MutationObserver(() => repairSoon());
    bodyObserver.observe(document.body, { childList: true, subtree: true });

    // Initial row and any rows already rendered.
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
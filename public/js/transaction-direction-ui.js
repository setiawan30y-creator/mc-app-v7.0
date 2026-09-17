document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.trx-page');
    const itemRows = document.getElementById('itemRows');
    if (!page || !itemRows) return;

    const style = document.createElement('style');
    style.textContent = `
        .trx-row-direction-ui{display:flex;gap:3px;align-items:center;white-space:nowrap}
        .trx-row-direction-ui .row-direction-btn{border:1px solid #d1dbd6;background:#fff;color:#66736c;border-radius:4px;padding:4px 7px;font-size:9px;font-weight:700;line-height:1;cursor:pointer}
        .trx-row-direction-ui .row-direction-btn.active{background:#26352e;color:#fff;border-color:#26352e}
        .trx-row-direction-ui .row-direction-btn:hover{border-color:#9eafa6}
        .trx-row-direction-hidden{display:none!important}
    `;
    document.head.appendChild(style);

    function currentMode(){
        return page.querySelector('.direction-switch [data-direction-mode].active')?.dataset.directionMode || 'buy';
    }

    function decorateRow(tr){
        const select = tr.querySelector('.trx-row-direction');
        if (!select) {
            tr.querySelector('.trx-row-direction-ui')?.remove();
            return;
        }

        let box = tr.querySelector('.trx-row-direction-ui');
        if (!box) {
            box = document.createElement('div');
            box.className = 'trx-row-direction-ui';
            select.parentNode.insertBefore(box, select);
        }

        const value = select.value === 'sell' ? 'sell' : 'buy';
        box.innerHTML = `
            <button type="button" class="row-direction-btn ${value === 'buy' ? 'active' : ''}" data-row-direction="buy">BELI</button>
            <button type="button" class="row-direction-btn ${value === 'sell' ? 'active' : ''}" data-row-direction="sell">JUAL</button>
        `;

        select.classList.add('trx-row-direction-hidden');
        box.querySelectorAll('[data-row-direction]').forEach(button => {
            button.addEventListener('click', () => {
                select.value = button.dataset.rowDirection;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                decorateRow(tr);
            });
        });
    }

    function sync(){
        const mode = currentMode();
        itemRows.querySelectorAll('tr').forEach(tr => {
            const select = tr.querySelector('.trx-row-direction');
            const ui = tr.querySelector('.trx-row-direction-ui');
            if (mode === 'mixed' && select) {
                decorateRow(tr);
            } else {
                ui?.remove();
                select?.classList.remove('trx-row-direction-hidden');
            }
        });
    }

    const observer = new MutationObserver(() => {
        window.requestAnimationFrame(sync);
    });
    observer.observe(itemRows, { childList: true, subtree: true });

    const switchBox = page.querySelector('.direction-switch');
    if (switchBox) {
        new MutationObserver(sync).observe(switchBox, { childList: true, subtree: true, attributes: true });
        switchBox.addEventListener('click', () => setTimeout(sync, 0));
    }

    setTimeout(sync, 0);
});

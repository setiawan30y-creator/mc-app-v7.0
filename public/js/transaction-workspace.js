document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.trx-page');
    if (!page) return;

    const itemRows = document.getElementById('itemRows');
    const summary = document.getElementById('summaryTotal')?.closest('.trx-summary');
    if (!itemRows || !summary) return;

    const style = document.createElement('style');
    style.textContent = `
        .trx-summary{grid-template-columns:repeat(4,minmax(0,1fr))!important}
        .trx-summary-box.is-balance{border-color:#cbd8d1}
        .trx-summary-box.is-positive{background:#f7fbf9}
        .trx-summary-box.is-negative{background:#fff9f7}
        .trx-row-direction{width:100%;font-size:10px;min-height:29px;height:29px;padding:3px 5px;border:1px solid #d5ded9;border-radius:4px;background:#fff;font-weight:700}
        .trx-direction-note{font-size:9px;margin-top:3px;color:#7a8580}
        @media(max-width:900px){.trx-summary{grid-template-columns:1fr!important}}
    `;
    document.head.appendChild(style);

    // The direction is per item, not global. Keep the existing global buttons only as a
    // quick default for newly-added rows; each row can then be changed independently.
    const switchBox = page.querySelector('.direction-switch');
    if (switchBox) {
        const label = switchBox.parentElement?.querySelector('.trx-label');
        if (label) label.textContent = 'Default Arah Item Baru';
        const note = document.createElement('div');
        note.className = 'trx-direction-note';
        note.textContent = 'Setiap baris dapat BELI atau JUAL sendiri.';
        switchBox.appendChild(note);
    }

    function addDirectionSelect(tr) {
        if (!tr || tr.querySelector('.trx-row-direction')) return;
        const hidden = tr.querySelector('.direction-input');
        const cell = hidden?.closest('td');
        if (!cell) return;
        const current = hidden.value === 'sell' ? 'sell' : 'buy';
        const select = document.createElement('select');
        select.className = 'trx-row-direction';
        select.innerHTML = '<option value="buy">BELI</option><option value="sell">JUAL</option>';
        select.value = current;
        const badge = cell.querySelector('.direction-display');
        if (badge) badge.remove();
        cell.appendChild(select);
        select.addEventListener('change', () => {
            hidden.value = select.value;
            if (typeof refreshRate === 'function') refreshRate(tr);
            calculateBalance();
        });
    }

    function transformRows() {
        itemRows.querySelectorAll('tr').forEach(addDirectionSelect);
    }

    const observer = new MutationObserver(() => transformRows());
    observer.observe(itemRows, {childList:true});
    transformRows();

    // Replace the generic 3-box summary with operational BUY/SELL settlement totals.
    summary.innerHTML = `
        <div class="trx-summary-box">
            <div class="trx-summary-label">Total Jual</div>
            <div class="trx-summary-value" id="summarySell">Rp0</div>
            <div class="trx-summary-note">Valuta customer diserahkan</div>
        </div>
        <div class="trx-summary-box">
            <div class="trx-summary-label">Total Beli</div>
            <div class="trx-summary-value" id="summaryBuy">Rp0</div>
            <div class="trx-summary-note">Valuta customer terima</div>
        </div>
        <div class="trx-summary-box is-balance" id="summaryDifferenceBox">
            <div class="trx-summary-label" id="summaryDifferenceLabel">Selisih Rp</div>
            <div class="trx-summary-value" id="summaryDifference">Rp0</div>
            <div class="trx-summary-note" id="summaryDifferenceNote">Nilai bersih</div>
        </div>
        <div class="trx-summary-box" id="summarySettlementBox">
            <div class="trx-summary-label" id="summarySettlementLabel">Status</div>
            <div class="trx-summary-value" id="summarySettlement">PAS</div>
            <div class="trx-summary-note">Menjadi dasar pembayaran</div>
        </div>
        <span id="summaryItems" hidden>0</span><span id="summaryQty" hidden>0</span>
    `;

    function moneySafe(v) {
        return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:2}).format(Number(v)||0);
    }

    function calculateBalance() {
        let sell = 0, buy = 0;
        itemRows.querySelectorAll('tr').forEach(tr => {
            const qty = Number(tr.querySelector('.qty-input')?.value) || 0;
            const rate = Number(tr.querySelector('.rate-input')?.value) || 0;
            const subtotal = qty * rate;
            const direction = tr.querySelector('.direction-input')?.value || 'buy';
            if (direction === 'sell') sell += subtotal;
            else buy += subtotal;
        });

        const diff = sell - buy;
        const absDiff = Math.abs(diff);
        const differenceBox = document.getElementById('summaryDifferenceBox');
        const settlementBox = document.getElementById('summarySettlementBox');
        document.getElementById('summarySell').textContent = moneySafe(sell);
        document.getElementById('summaryBuy').textContent = moneySafe(buy);
        document.getElementById('summaryDifference').textContent = moneySafe(absDiff);

        differenceBox.classList.remove('is-positive','is-negative','is-balance');
        settlementBox.classList.remove('is-positive','is-negative','is-balance');
        if (diff > 0) {
            document.getElementById('summaryDifferenceLabel').textContent = 'Sisa Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Dibayarkan kepada customer';
            document.getElementById('summarySettlementLabel').textContent = 'Customer Terima';
            document.getElementById('summarySettlement').textContent = moneySafe(diff);
            differenceBox.classList.add('is-positive');
            settlementBox.classList.add('is-positive');
        } else if (diff < 0) {
            document.getElementById('summaryDifferenceLabel').textContent = 'Tambah Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Dibayarkan oleh customer';
            document.getElementById('summarySettlementLabel').textContent = 'Customer Bayar';
            document.getElementById('summarySettlement').textContent = moneySafe(absDiff);
            differenceBox.classList.add('is-negative');
            settlementBox.classList.add('is-negative');
        } else {
            document.getElementById('summaryDifferenceLabel').textContent = 'Selisih Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Tidak ada selisih';
            document.getElementById('summarySettlementLabel').textContent = 'Status';
            document.getElementById('summarySettlement').textContent = 'PAS';
            differenceBox.classList.add('is-balance');
            settlementBox.classList.add('is-balance');
        }
    }

    itemRows.addEventListener('input', calculateBalance);
    itemRows.addEventListener('change', calculateBalance);
    const addButton = document.getElementById('addItem');
    addButton?.addEventListener('click', () => setTimeout(() => { transformRows(); calculateBalance(); }, 0));
    calculateBalance();
});

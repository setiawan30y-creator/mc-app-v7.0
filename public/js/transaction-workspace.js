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
        .direction-switch{display:none!important}
        .row-direction{display:inline-flex!important;gap:3px;align-items:center;white-space:nowrap}
        .row-direction-btn{border:1px solid #cfd9d4;background:#fff;color:#59665f;border-radius:4px;padding:5px 7px;font-size:9px;font-weight:700;cursor:pointer;line-height:1}
        .row-direction-btn.active{background:#26352e;color:#fff;border-color:#26352e}
        .trx-row-direction-wrap{display:none!important}
        .payment-fields.split-mode{grid-template-columns:repeat(2,minmax(0,1fr))!important}
        .payment-fields.split-mode .payment-field{display:block!important}
        .payment-field.payment-cash,.payment-field.payment-transfer{min-width:0}
        .payment-split-note{grid-column:1/-1;font-size:9px;color:#7f8985;padding-top:1px}
        .payment-balance{grid-column:1/-1;border:1px solid #dfe7e2;border-radius:5px;padding:7px 9px;font-size:10px;display:flex;justify-content:space-between;gap:8px;background:#fafcfb}
        .payment-balance.ok{border-color:#cbd8d1;background:#f7fbf9}
        .payment-balance.bad{border-color:#ead0d0;background:#fff7f7;color:#8d3535}
        @media(max-width:900px){.trx-summary{grid-template-columns:1fr!important}.payment-fields.split-mode{grid-template-columns:1fr!important}}
    `;
    document.head.appendChild(style);

    const getRows = () => Array.from(itemRows.querySelectorAll('tr'));

    function getDirection(tr) {
        return tr.querySelector('.direction-input')?.value === 'sell' ? 'sell' : 'buy';
    }

    function setDirection(tr, direction) {
        const hidden = tr.querySelector('.direction-input');
        if (!hidden) return;
        hidden.value = direction;
        hidden.disabled = false;
        tr.querySelectorAll('.row-direction-btn').forEach(button => {
            button.classList.toggle('active', button.dataset.rowDirection === direction);
        });
        if (typeof refreshRate === 'function') refreshRate(tr);
    }

    // Direction buttons are rendered by create.blade.php for every row, including rows added later.
    // Keep this script focused on state/calculation and do not inject a second button set.
    itemRows.addEventListener('click', event => {
        const button = event.target.closest('.row-direction-btn');
        if (!button) return;
        const tr = button.closest('tr');
        if (!tr) return;
        setDirection(tr, button.dataset.rowDirection);
        calculateBalance();
    });

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
        let itemCount = 0, qtyTotal = 0;
        getRows().forEach(tr => {
            const qty = Number(tr.querySelector('.qty-input')?.value) || 0;
            const rate = Number(tr.querySelector('.rate-input')?.value) || 0;
            const subtotal = qty * rate;
            const direction = getDirection(tr);
            if (tr.querySelector('.currency-input, select[name*="currency_id"]')) itemCount++;
            qtyTotal += qty;
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
        document.getElementById('summaryItems').textContent = itemCount;
        document.getElementById('summaryQty').textContent = `${qtyTotal} qty`;

        differenceBox.classList.remove('is-positive','is-negative','is-balance');
        settlementBox.classList.remove('is-positive','is-negative','is-balance');
        if (diff > 0) {
            document.getElementById('summaryDifferenceLabel').textContent = 'Sisa Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Dibayarkan kepada customer';
            document.getElementById('summarySettlementLabel').textContent = 'Customer Terima';
            document.getElementById('summarySettlement').textContent = moneySafe(diff);
            differenceBox.classList.add('is-positive'); settlementBox.classList.add('is-positive');
        } else if (diff < 0) {
            document.getElementById('summaryDifferenceLabel').textContent = 'Tambah Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Dibayarkan oleh customer';
            document.getElementById('summarySettlementLabel').textContent = 'Customer Bayar';
            document.getElementById('summarySettlement').textContent = moneySafe(absDiff);
            differenceBox.classList.add('is-negative'); settlementBox.classList.add('is-negative');
        } else {
            document.getElementById('summaryDifferenceLabel').textContent = 'Selisih Rp';
            document.getElementById('summaryDifferenceNote').textContent = 'Tidak ada selisih';
            document.getElementById('summarySettlementLabel').textContent = 'Status';
            document.getElementById('summarySettlement').textContent = 'PAS';
            differenceBox.classList.add('is-balance'); settlementBox.classList.add('is-balance');
        }

        const hiddenDifference = document.getElementById('calculatedDifference');
        if (hiddenDifference) hiddenDifference.value = diff.toFixed(2);
        updatePaymentRequirement(absDiff);
    }

    const paymentMethodInput = document.getElementById('paymentMethod');
    const paymentMethods = page.querySelectorAll('.payment-method-btn');
    const paymentFields = page.querySelector('.payment-fields');
    const cashInput = page.querySelector('[name="cash_amount"]');
    const transferInput = page.querySelector('[name="transfer_amount"]');
    const paymentError = page.querySelector('.payment-error');
    const bankField = page.querySelector('[name="bank_account_id"]')?.closest('.payment-field');

    function fieldByName(name) {
        const input = page.querySelector(`[name="${name}"]`);
        return input?.closest('.payment-field');
    }
    const cashField = fieldByName('cash_amount');
    const transferField = fieldByName('transfer_amount');

    function updateSplitLayout() {
        const method = paymentMethodInput?.value || 'cash';
        paymentMethods.forEach(btn => btn.classList.toggle('active', btn.dataset.method === method));
        if (!paymentFields) return;
        paymentFields.classList.toggle('split-mode', method === 'split');
        paymentFields.querySelectorAll('.payment-field').forEach(field => field.classList.remove('show'));
        if (method === 'cash') cashField?.classList.add('show');
        if (method === 'transfer') { transferField?.classList.add('show'); bankField?.classList.add('show'); }
        if (method === 'split') { cashField?.classList.add('show'); transferField?.classList.add('show'); bankField?.classList.add('show'); ensureSplitDecorations(); }
    }

    function ensureSplitDecorations() {
        if (!paymentFields) return;
        if (!paymentFields.querySelector('.payment-split-note')) {
            const note = document.createElement('div');
            note.className = 'payment-split-note';
            note.textContent = 'Split selalu menggunakan dua sumber: Cash + Transfer. Total keduanya harus sama dengan kebutuhan IDR.';
            paymentFields.prepend(note);
        }
        if (!paymentFields.querySelector('.payment-balance')) {
            const balance = document.createElement('div');
            balance.className = 'payment-balance';
            balance.innerHTML = '<span>Total dibayar</span><strong id="splitPaidTotal">Rp0</strong>';
            paymentFields.appendChild(balance);
        }
    }

    function updatePaymentRequirement(required) {
        const requiredEl = document.getElementById('paymentRequired');
        if (requiredEl) requiredEl.textContent = moneySafe(required);
        const method = paymentMethodInput?.value || 'cash';
        const total = (Number(cashInput?.value)||0) + (Number(transferInput?.value)||0);
        const balance = paymentFields?.querySelector('.payment-balance');
        if (balance) {
            const totalEl = balance.querySelector('#splitPaidTotal');
            if (totalEl) totalEl.textContent = moneySafe(total);
            const ok = method === 'split' ? Math.abs(total-required) < 0.01 : true;
            balance.classList.toggle('ok', ok); balance.classList.toggle('bad', !ok);
        }
        if (paymentError) {
            if (method === 'split' && required > 0 && Math.abs(total-required) >= 0.01) {
                paymentError.textContent = `Cash + Transfer harus sama dengan ${moneySafe(required)}.`;
                paymentError.style.display = 'block';
            } else paymentError.style.display = 'none';
        }
    }

    function setPaymentMethod(method) {
        if (paymentMethodInput) paymentMethodInput.value = method;
        updateSplitLayout();
        const required = Math.abs(Number(document.getElementById('calculatedDifference')?.value)||0);
        if (method === 'cash') { if (cashInput) cashInput.value = required.toFixed(2); if (transferInput) transferInput.value = ''; }
        else if (method === 'transfer') { if (transferInput) transferInput.value = required.toFixed(2); if (cashInput) cashInput.value = ''; }
        else if (method === 'split') {
            const cash = Number(cashInput?.value)||0, transfer = Number(transferInput?.value)||0;
            if (cash === 0 && transfer === 0 && cashInput) cashInput.value = required.toFixed(2);
        }
        updatePaymentRequirement(required);
    }

    paymentMethods.forEach(btn => btn.addEventListener('click', () => setPaymentMethod(btn.dataset.method)));

    function syncSplit(source) {
        if ((paymentMethodInput?.value || 'cash') !== 'split') return;
        const required = Math.abs(Number(document.getElementById('calculatedDifference')?.value)||0);
        let cash = Number(cashInput?.value)||0, transfer = Number(transferInput?.value)||0;
        if (source === 'cash') { cash = Math.max(0,cash); transfer = Math.max(0,required-cash); if (transferInput) transferInput.value = transfer.toFixed(2); }
        else { transfer = Math.max(0,transfer); cash = Math.max(0,required-transfer); if (cashInput) cashInput.value = cash.toFixed(2); }
        updatePaymentRequirement(required);
    }
    cashInput?.addEventListener('input', () => syncSplit('cash'));
    transferInput?.addEventListener('input', () => syncSplit('transfer'));

    page.querySelector('#transactionForm')?.addEventListener('submit', event => {
        const method = paymentMethodInput?.value || 'cash';
        const required = Math.abs(Number(document.getElementById('calculatedDifference')?.value)||0);
        const cash = Number(cashInput?.value)||0, transfer = Number(transferInput?.value)||0;
        if (method === 'split' && required > 0 && Math.abs((cash + transfer) - required) >= 0.01) {
            event.preventDefault(); updatePaymentRequirement(required); paymentFields?.scrollIntoView({behavior:'smooth',block:'center'}); return;
        }
        if (method === 'cash' && required > 0 && Math.abs(cash-required) >= 0.01) { event.preventDefault(); if (cashInput) cashInput.value = required.toFixed(2); updatePaymentRequirement(required); }
        if (method === 'transfer' && required > 0 && Math.abs(transfer-required) >= 0.01) { event.preventDefault(); if (transferInput) transferInput.value = required.toFixed(2); updatePaymentRequirement(required); }
    });

    updateSplitLayout();
    setPaymentMethod(paymentMethodInput?.value || 'cash');
    itemRows.addEventListener('input', calculateBalance);
    itemRows.addEventListener('change', calculateBalance);
    calculateBalance();
});
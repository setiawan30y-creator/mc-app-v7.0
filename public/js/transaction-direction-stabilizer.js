document.addEventListener('DOMContentLoaded', () => {
    const getRows = () => document.querySelectorAll('#itemRows > tr');

    // Modul 02 · ITEM TRANSAKSI: BELI hijau dan JUAL merah hanya setelah dipilih.
    if (!document.getElementById('transaction-direction-colors')) {
        const style = document.createElement('style');
        style.id = 'transaction-direction-colors';
        style.textContent = `
            .trx-row-direction-wrap{display:flex;gap:4px}
            .trx-row-direction-btn{border:1px solid #d1dbd6;background:#fff;color:#66736c;border-radius:4px;padding:3px 5px;font-size:8px;font-weight:700;cursor:pointer}
            .trx-row-direction-btn[data-row-direction="buy"].active{background:#198754;color:#fff;border-color:#198754}
            .trx-row-direction-btn[data-row-direction="sell"].active{background:#dc3545;color:#fff;border-color:#dc3545}
            .customer-search-results{display:none;margin-top:4px;border:1px solid #d4ddd8;border-radius:5px;background:#fff;max-height:220px;overflow-y:auto;box-shadow:0 3px 10px rgba(30,45,38,.08);position:relative;z-index:20}
            .customer-search-result{display:block;width:100%;border:0;border-bottom:1px solid #edf1ef;background:#fff;text-align:left;padding:7px 9px;cursor:pointer;font-size:11px;color:#3f4b45}
            .customer-search-result:last-child{border-bottom:0}.customer-search-result:hover,.customer-search-result.active{background:#f1f6f3}
            .customer-search-result small{display:block;margin-top:2px;color:#7a8580;font-size:9px}.customer-search-empty{padding:8px 9px;color:#8a948f;font-size:10px}
            #itemRows input[type="number"].qty-input::-webkit-outer-spin-button,#itemRows input[type="number"].qty-input::-webkit-inner-spin-button,#itemRows input[type="number"].rate-input::-webkit-outer-spin-button,#itemRows input[type="number"].rate-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
            #itemRows input[type="number"].qty-input,#itemRows input[type="number"].rate-input{-moz-appearance:textfield;appearance:textfield}
            .quick-customer-row{display:flex;gap:5px;align-items:center;margin-top:5px}.quick-customer-btn{border:1px solid #b9c8c0;background:#f7faf8;color:#42534a;border-radius:5px;padding:5px 9px;font-size:10px;font-weight:700;cursor:pointer}.quick-customer-btn:hover{background:#edf4ef}
        `;
        document.head.appendChild(style);
    }

    // 01 · NASABAH: cari nasabah berdasarkan nama, nomor HP, atau nomor customer.
    const customerSearch = document.getElementById('customerSearch');
    const customerSelect = document.getElementById('customer_id');
    if (customerSearch && customerSelect) {
        const allOptions = Array.from(customerSelect.options).filter(option => option.value);
        const wrapper = customerSearch.closest('.customer-search-wrap') || customerSearch.parentElement;
        const resultBox = document.createElement('div'); resultBox.className = 'customer-search-results'; wrapper.insertAdjacentElement('afterend', resultBox);
        const normalize = value => String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        function renderCustomerResults(query) {
            const raw = String(query || '').trim(), normalizedQuery = normalize(raw); resultBox.innerHTML = '';
            if (!normalizedQuery) { resultBox.style.display = 'none'; return; }
            const matches = allOptions.filter(option => normalize([option.textContent,option.dataset.search,option.dataset.number,option.dataset.phone].join(' ')).includes(normalizedQuery)).slice(0,30);
            if (!matches.length) { resultBox.innerHTML = '<div class="customer-search-empty">Nasabah tidak ditemukan.</div>'; resultBox.style.display='block'; return; }
            matches.forEach(option => { const button=document.createElement('button'); button.type='button'; button.className='customer-search-result'; button.dataset.customerId=option.value; button.innerHTML=`${option.textContent}<small>${option.dataset.phone || 'Nomor HP belum diisi'}${option.dataset.number ? ` · ${option.dataset.number}` : ''}</small>`; resultBox.appendChild(button); }); resultBox.style.display='block';
        }
        function selectCustomer(option) { if(!option)return; customerSelect.value=option.value; customerSearch.value=option.textContent.trim(); resultBox.style.display='none'; customerSelect.dispatchEvent(new Event('change',{bubbles:true})); }
        customerSearch.addEventListener('input',()=>renderCustomerResults(customerSearch.value)); customerSearch.addEventListener('focus',()=>{if(customerSearch.value.trim())renderCustomerResults(customerSearch.value)});
        resultBox.addEventListener('click',event=>{const button=event.target.closest('.customer-search-result');if(!button)return;selectCustomer(allOptions.find(item=>item.value===button.dataset.customerId));});
        document.addEventListener('click',event=>{if(!wrapper.contains(event.target)&&!resultBox.contains(event.target))resultBox.style.display='none';});
        customerSelect.addEventListener('change',()=>{const selected=customerSelect.options[customerSelect.selectedIndex];if(selected&&selected.value&&document.activeElement!==customerSearch)customerSearch.value=selected.textContent.trim();});

        // Tambah Nasabah menggunakan FORM MASTER NASABAH yang sudah ada.
        // Parameter return_to dipakai agar alur dapat dikembalikan ke Transaksi Baru.
        const quickRow=document.createElement('div');
        quickRow.className='quick-customer-row';
        quickRow.innerHTML='<a href="/customers/create?return_to=transaction" class="quick-customer-btn" id="quickAddCustomer">+ Tambah Nasabah</a>';
        customerSelect.parentElement.insertAdjacentElement('afterend',quickRow);
    }

    function ensureRowDirection(tr,index){const cells=tr.querySelectorAll('td'),cell=cells[1];if(!cell)return;cell.querySelector('.row-direction')?.remove();let hidden=cell.querySelector('.direction-input');if(!hidden){hidden=document.createElement('input');hidden.type='hidden';hidden.className='direction-input';hidden.name=`items[${index}][direction]`;hidden.value='';cell.appendChild(hidden)}let wrap=cell.querySelector('.trx-row-direction-wrap');if(!wrap){wrap=document.createElement('div');wrap.className='trx-row-direction-wrap';wrap.innerHTML='<button type="button" class="trx-row-direction-btn" data-row-direction="buy">BELI</button><button type="button" class="trx-row-direction-btn" data-row-direction="sell">JUAL</button>';cell.appendChild(wrap)}const direction=hidden.value==='sell'?'sell':hidden.value==='buy'?'buy':'';wrap.querySelectorAll('[data-row-direction]').forEach(button=>button.classList.toggle('active',direction!==''&&button.dataset.rowDirection===direction))}
    function ensureAllRows(){getRows().forEach((tr,index)=>ensureRowDirection(tr,index))}
    function repairSoon(){[0,50,150,300,600].forEach(delay=>setTimeout(ensureAllRows,delay))}
    document.addEventListener('click',event=>{if(event.target.closest('#addItem'))repairSoon()},true);
    const bodyObserver=new MutationObserver(()=>repairSoon());bodyObserver.observe(document.body,{childList:true,subtree:true});repairSoon();
    document.addEventListener('click',event=>{const button=event.target.closest('.trx-row-direction-btn');if(!button)return;const row=button.closest('tr'),hidden=row?.querySelector('.direction-input');if(!hidden)return;hidden.value=button.dataset.rowDirection==='sell'?'sell':'buy';row.querySelectorAll('.trx-row-direction-btn').forEach(item=>item.classList.toggle('active',item===button));if(typeof refreshRate==='function')refreshRate(row)});
});
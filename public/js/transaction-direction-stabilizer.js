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
            .quick-customer-backdrop{display:none;position:fixed;inset:0;background:rgba(20,30,25,.35);z-index:1050;align-items:center;justify-content:center;padding:16px}.quick-customer-modal{width:min(460px,100%);background:#fff;border-radius:8px;box-shadow:0 12px 35px rgba(0,0,0,.18);overflow:hidden}.quick-customer-head{padding:10px 13px;background:#f4f7f5;border-bottom:1px solid #dfe7e2;display:flex;justify-content:space-between;align-items:center}.quick-customer-title{font-size:12px;font-weight:700;color:#3f4b45}.quick-customer-close{border:0;background:transparent;font-size:18px;color:#718078;cursor:pointer}.quick-customer-body{padding:13px}.quick-customer-field{margin-bottom:9px}.quick-customer-field label{display:block;font-size:10px;font-weight:700;color:#65716b;margin-bottom:3px}.quick-customer-field input,.quick-customer-field select{width:100%;height:32px;border:1px solid #d4ddd8;border-radius:5px;padding:5px 8px;font-size:11px}.quick-customer-actions{display:flex;justify-content:flex-end;gap:6px;margin-top:11px}.quick-customer-actions button{border-radius:5px;padding:6px 10px;font-size:10px;font-weight:700;cursor:pointer}.quick-customer-cancel{border:1px solid #d4ddd8;background:#fff;color:#53635b}.quick-customer-save{border:1px solid #198754;background:#198754;color:#fff}.quick-customer-error{display:none;margin-bottom:8px;padding:7px 8px;border:1px solid #e8caca;background:#fff6f6;color:#8d3535;border-radius:5px;font-size:10px}
        `;
        document.head.appendChild(style);
    }

    // 01 · CUSTOMER: cari customer berdasarkan nama, nomor HP, atau nomor customer.
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
            if (!matches.length) { resultBox.innerHTML = '<div class="customer-search-empty">Customer tidak ditemukan.</div>'; resultBox.style.display='block'; return; }
            matches.forEach(option => { const button=document.createElement('button'); button.type='button'; button.className='customer-search-result'; button.dataset.customerId=option.value; button.innerHTML=`${option.textContent}<small>${option.dataset.phone || 'Nomor HP belum diisi'}${option.dataset.number ? ` · ${option.dataset.number}` : ''}</small>`; resultBox.appendChild(button); }); resultBox.style.display='block';
        }
        function selectCustomer(option) { if(!option)return; customerSelect.value=option.value; customerSearch.value=option.textContent.trim(); resultBox.style.display='none'; customerSelect.dispatchEvent(new Event('change',{bubbles:true})); }
        customerSearch.addEventListener('input',()=>renderCustomerResults(customerSearch.value)); customerSearch.addEventListener('focus',()=>{if(customerSearch.value.trim())renderCustomerResults(customerSearch.value)});
        resultBox.addEventListener('click',event=>{const button=event.target.closest('.customer-search-result');if(!button)return;selectCustomer(allOptions.find(item=>item.value===button.dataset.customerId));});
        document.addEventListener('click',event=>{if(!wrapper.contains(event.target)&&!resultBox.contains(event.target))resultBox.style.display='none';});
        customerSelect.addEventListener('change',()=>{const selected=customerSelect.options[customerSelect.selectedIndex];if(selected&&selected.value&&document.activeElement!==customerSearch)customerSearch.value=selected.textContent.trim();});

        // Tombol + Tambah Customer di Transaksi Baru.
        const quickRow=document.createElement('div'); quickRow.className='quick-customer-row'; quickRow.innerHTML='<button type="button" class="quick-customer-btn" id="quickAddCustomer">+ Tambah Customer</button>'; customerSelect.parentElement.insertAdjacentElement('afterend',quickRow);

        const backdrop=document.createElement('div'); backdrop.className='quick-customer-backdrop'; backdrop.innerHTML=`<div class="quick-customer-modal" role="dialog" aria-modal="true"><div class="quick-customer-head"><span class="quick-customer-title">Tambah Customer Baru</span><button type="button" class="quick-customer-close" id="quickCustomerClose">×</button></div><form class="quick-customer-body" id="quickCustomerForm"><div class="quick-customer-error" id="quickCustomerError"></div><div class="quick-customer-field"><label>Nama Customer *</label><input name="full_name" required maxlength="150" autocomplete="name"></div><div class="quick-customer-field"><label>Nomor HP</label><input name="phone" maxlength="30" autocomplete="tel"></div><div class="quick-customer-field"><label>Jenis Identitas</label><select name="jenis_id"><option value="">Pilih...</option><option value="KTP">KTP</option><option value="PASSPORT">Passport</option><option value="SIM">SIM</option></select></div><div class="quick-customer-field"><label>Nomor Identitas</label><input name="no_ktp" maxlength="100"></div><div class="quick-customer-actions"><button type="button" class="quick-customer-cancel" id="quickCustomerCancel">Batal</button><button type="submit" class="quick-customer-save" id="quickCustomerSave">Simpan Customer</button></div></form></div>`; document.body.appendChild(backdrop);
        const modal=document.getElementById('quickAddCustomer'), form=document.getElementById('quickCustomerForm'), error=document.getElementById('quickCustomerError');
        const csrf=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('#transactionForm input[name="_token"]')?.value;
        function openQuickCustomer(){form.reset();error.style.display='none';backdrop.style.display='flex';form.querySelector('[name="full_name"]').focus();}
        function closeQuickCustomer(){backdrop.style.display='none';}
        modal.addEventListener('click',openQuickCustomer); document.getElementById('quickCustomerClose').addEventListener('click',closeQuickCustomer); document.getElementById('quickCustomerCancel').addEventListener('click',closeQuickCustomer); backdrop.addEventListener('click',e=>{if(e.target===backdrop)closeQuickCustomer();});
        form.addEventListener('submit',async e=>{
            e.preventDefault(); error.style.display='none'; const save=document.getElementById('quickCustomerSave'); save.disabled=true; save.textContent='Menyimpan...';
            try { const response=await fetch('{{ route('transactions.customer-store') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(Object.fromEntries(new FormData(form)))}); const data=await response.json(); if(!response.ok) throw new Error(data.message || Object.values(data.errors||{}).flat().join(' ') || 'Customer gagal disimpan.');
                const c=data.customer; const option=document.createElement('option'); option.value=c.id; option.textContent=`${c.full_name} — ${c.customer_number} — ${c.phone || 'tanpa HP'}`; option.dataset.search=[c.full_name,c.customer_number,c.phone].join(' ').toLowerCase(); option.dataset.number=c.customer_number; option.dataset.phone=c.phone||''; option.dataset.kyc=c.kyc_status||''; option.dataset.idtype=c.jenis_id||''; option.dataset.idnumber=c.no_ktp||''; customerSelect.appendChild(option); allOptions.push(option); selectCustomer(option); closeQuickCustomer();
            } catch(err) { error.textContent=err.message; error.style.display='block'; } finally { save.disabled=false; save.textContent='Simpan Customer'; }
        });
    }

    function ensureRowDirection(tr,index){const cells=tr.querySelectorAll('td'),cell=cells[1];if(!cell)return;cell.querySelector('.row-direction')?.remove();let hidden=cell.querySelector('.direction-input');if(!hidden){hidden=document.createElement('input');hidden.type='hidden';hidden.className='direction-input';hidden.name=`items[${index}][direction]`;hidden.value='';cell.appendChild(hidden)}let wrap=cell.querySelector('.trx-row-direction-wrap');if(!wrap){wrap=document.createElement('div');wrap.className='trx-row-direction-wrap';wrap.innerHTML='<button type="button" class="trx-row-direction-btn" data-row-direction="buy">BELI</button><button type="button" class="trx-row-direction-btn" data-row-direction="sell">JUAL</button>';cell.appendChild(wrap)}const direction=hidden.value==='sell'?'sell':hidden.value==='buy'?'buy':'';wrap.querySelectorAll('[data-row-direction]').forEach(button=>button.classList.toggle('active',direction!==''&&button.dataset.rowDirection===direction))}
    function ensureAllRows(){getRows().forEach((tr,index)=>ensureRowDirection(tr,index))}
    function repairSoon(){[0,50,150,300,600].forEach(delay=>setTimeout(ensureAllRows,delay))}
    document.addEventListener('click',event=>{if(event.target.closest('#addItem'))repairSoon()},true);
    const bodyObserver=new MutationObserver(()=>repairSoon());bodyObserver.observe(document.body,{childList:true,subtree:true});repairSoon();
    document.addEventListener('click',event=>{const button=event.target.closest('.trx-row-direction-btn');if(!button)return;const row=button.closest('tr'),hidden=row?.querySelector('.direction-input');if(!hidden)return;hidden.value=button.dataset.rowDirection==='sell'?'sell':'buy';row.querySelectorAll('.trx-row-direction-btn').forEach(item=>item.classList.toggle('active',item===button));if(typeof refreshRate==='function')refreshRate(row)});
});
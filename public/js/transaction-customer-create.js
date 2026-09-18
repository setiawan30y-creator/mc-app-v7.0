document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('customerSearch');
    const select = document.getElementById('customer_id');
    if (!search || !select) return;

    const searchWrap = search.closest('.customer-search-wrap') || search.parentElement;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-outline-success trx-btn mt-1';
    button.textContent = '+ Tambah Customer';
    searchWrap.parentElement.appendChild(button);

    const modal = document.createElement('div');
    modal.innerHTML = `
        <div id="transactionCustomerModal" style="display:none;position:fixed;inset:0;background:rgba(20,30,25,.35);z-index:1050;align-items:center;justify-content:center;padding:20px">
            <div style="background:#fff;border:1px solid #dfe7e2;border-radius:8px;width:min(520px,100%);box-shadow:0 10px 30px rgba(0,0,0,.15)">
                <div style="padding:12px 14px;border-bottom:1px solid #e1e8e4;display:flex;justify-content:space-between;align-items:center">
                    <strong style="font-size:13px">Tambah Customer</strong>
                    <button type="button" id="closeTransactionCustomerModal" style="border:0;background:transparent;font-size:18px;color:#718078">×</button>
                </div>
                <form id="transactionCustomerForm" style="padding:14px">
                    <div class="mb-2"><label class="trx-label">Nama Customer *</label><input name="full_name" class="form-control trx-control" required></div>
                    <div class="mb-2"><label class="trx-label">Nomor HP</label><input name="phone" class="form-control trx-control" inputmode="tel"></div>
                    <div class="mb-2"><label class="trx-label">Jenis Identitas</label><select name="jenis_id" class="form-select trx-control"><option value="">Pilih...</option><option value="KTP">KTP</option><option value="PASSPORT">Passport</option><option value="SIM">SIM</option></select></div>
                    <div class="mb-2"><label class="trx-label">Nomor Identitas</label><input name="no_ktp" class="form-control trx-control"></div>
                    <div id="transactionCustomerError" style="display:none;color:#8d3535;font-size:10px;margin-top:7px"></div>
                    <div style="display:flex;justify-content:flex-end;gap:6px;margin-top:12px">
                        <button type="button" id="cancelTransactionCustomer" class="btn btn-light trx-btn">Batal</button>
                        <button type="submit" class="btn btn-success trx-btn">Simpan Customer</button>
                    </div>
                </form>
            </div>
        </div>`;
    document.body.appendChild(modal.firstElementChild);

    const overlay = document.getElementById('transactionCustomerModal');
    const form = document.getElementById('transactionCustomerForm');
    const error = document.getElementById('transactionCustomerError');
    const close = () => { overlay.style.display = 'none'; form.reset(); error.style.display = 'none'; error.textContent = ''; };
    const open = () => { overlay.style.display = 'flex'; form.querySelector('[name="full_name"]').focus(); };
    button.addEventListener('click', open);
    document.getElementById('closeTransactionCustomerModal').addEventListener('click', close);
    document.getElementById('cancelTransactionCustomer').addEventListener('click', close);

    form.addEventListener('submit', async event => {
        event.preventDefault();
        error.style.display = 'none';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        const data = Object.fromEntries(new FormData(form).entries());
        try {
            const response = await fetch('/teller/transaction/customer', {
                method: 'POST',
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf || ''},
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Customer gagal disimpan.');
            const c = result.customer;
            const option = new Option(`${c.full_name} — ${c.customer_number} — ${c.phone || 'tanpa HP'}`, c.id, true, true);
            option.dataset.search = `${c.full_name} ${c.customer_number} ${c.phone || ''}`.toLowerCase();
            option.dataset.number = c.customer_number || '';
            option.dataset.phone = c.phone || '';
            option.dataset.kyc = c.kyc_status || '';
            option.dataset.idtype = c.jenis_id || '';
            option.dataset.idnumber = c.no_ktp || '';
            select.appendChild(option);
            select.value = c.id;
            search.value = option.textContent.trim();
            select.dispatchEvent(new Event('change', {bubbles:true}));
            close();
        } catch (e) {
            error.textContent = e.message;
            error.style.display = 'block';
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------
    // MASTER NASABAH -> kembali ke Transaksi Baru
    // ------------------------------------------------------------
    const customerForm = document.getElementById('customerForm');
    const customerParams = new URLSearchParams(window.location.search);

    if (customerForm && customerParams.get('return_to') === 'transaction') {
        customerForm.action = '/teller/transaction/customer';

        let returnInput = customerForm.querySelector('input[name="_return_to"]');
        if (!returnInput) {
            returnInput = document.createElement('input');
            returnInput.type = 'hidden';
            returnInput.name = '_return_to';
            customerForm.appendChild(returnInput);
        }
        returnInput.value = 'transaction';
    }

    // ------------------------------------------------------------
    // TRANSAKSI BARU -> + Tambah Nasabah
    // ------------------------------------------------------------
    const search = document.getElementById('customerSearch');
    const select = document.getElementById('customer_id');
    if (!select) return;

    if (search) {
        const searchWrap = search.closest('.customer-search-wrap') || search.parentElement;
        const button = document.createElement('a');
        button.href = '/customers/create?return_to=transaction';
        button.className = 'btn btn-outline-success trx-btn mt-1 d-inline-block';
        button.textContent = '+ Tambah Nasabah';
        button.style.textDecoration = 'none';
        searchWrap.parentElement.appendChild(button);
    }

    // ------------------------------------------------------------
    // Setelah Master Nasabah disimpan, pilih otomatis customer_id.
    // ------------------------------------------------------------
    const customerId = customerParams.get('customer_id');
    if (customerId) {
        const option = Array.from(select.options)
            .find(item => String(item.value) === String(customerId));

        if (option) {
            option.selected = true;
            select.value = customerId;
            if (search) search.value = option.textContent.trim();
            select.dispatchEvent(new Event('change', { bubbles: true }));

            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('customer_id');
            window.history.replaceState({}, document.title, cleanUrl.toString());
        }
    }
});

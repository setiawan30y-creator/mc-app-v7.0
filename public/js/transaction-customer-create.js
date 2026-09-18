document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('customerSearch');
    const select = document.getElementById('customer_id');
    if (!select) return;

    // Transaksi Baru memakai Master Nasabah sebagai satu-satunya form sumber data.
    // Tombol ini hanya membuka form Master Nasabah dengan konteks kembali ke transaksi.
    if (search) {
        const searchWrap = search.closest('.customer-search-wrap') || search.parentElement;
        const button = document.createElement('a');
        button.href = '/customers/create?return_to=transaction';
        button.className = 'btn btn-outline-success trx-btn mt-1 d-inline-block';
        button.textContent = '+ Tambah Nasabah';
        button.style.textDecoration = 'none';
        searchWrap.parentElement.appendChild(button);
    }

    // Setelah Master Nasabah selesai disimpan, CustomerController mengembalikan
    // ke Transaksi Baru dengan ?customer_id=<id>. Pilih otomatis nasabah tersebut.
    const params = new URLSearchParams(window.location.search);
    const customerId = params.get('customer_id');
    if (customerId) {
        const option = Array.from(select.options).find(item => String(item.value) === String(customerId));
        if (option) {
            option.selected = true;
            select.value = customerId;
            if (search) search.value = option.textContent.trim();
            select.dispatchEvent(new Event('change', { bubbles: true }));

            // Bersihkan parameter setelah pemilihan agar refresh tidak mengulang konteks.
            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('customer_id');
            window.history.replaceState({}, document.title, cleanUrl.toString());
        }
    }
});

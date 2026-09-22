@extends('layouts.app')

@section('title', 'MC Almara — Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.dashboard{display:flex;flex-direction:column;gap:18px}.welcome-box{background:linear-gradient(135deg,#fff,#f4fbf8);border:1px solid #e5e7eb;border-radius:12px;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:20px}.welcome-title{font-size:19px;font-weight:800;color:#0f172a;margin-bottom:5px}.welcome-text{font-size:11px;color:#64748b}.welcome-badge{padding:8px 12px;border-radius:8px;background:#e7f5ef;color:#147957;font-size:10px;font-weight:750}.activity-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.activity-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;min-width:0}.activity-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;font-size:13px;margin-bottom:12px}.activity-label{font-size:10px;color:#64748b;margin-bottom:8px}.activity-value{font-size:18px;font-weight:850;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.activity-meta{margin-top:8px;display:flex;justify-content:space-between;gap:8px;font-size:9px}.positive{color:#15803d;font-weight:700}.negative{color:#b91c1c;font-weight:700}.neutral{color:#64748b;font-weight:650}.dashboard-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden}.dashboard-card-header{padding:15px 17px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}.dashboard-card-title{font-size:12px;font-weight:800;color:#111827}.dashboard-card-subtitle{margin-top:3px;font-size:9px;color:#9ca3af}.position-section{background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden}.position-header{padding:16px 18px;border-bottom:1px solid #e5e7eb}.position-title{font-size:13px;font-weight:850;color:#111827}.position-subtitle{margin-top:3px;font-size:9px;color:#9ca3af}.position-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:16px}.position-card{border:1px solid #e5e7eb;border-radius:10px;padding:17px;background:#fafafa;min-width:0}.position-label{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.03em}.position-value{font-size:22px;font-weight:850;color:#111827;margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.position-meta{margin-top:9px;font-size:9px;color:#64748b}.position-breakdown{margin-top:10px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:9px;color:#64748b;line-height:1.6}.bank-list{padding:0 16px 16px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.bank-item{border:1px solid #edf0f2;border-radius:8px;padding:11px}.bank-name{font-size:10px;font-weight:800;color:#111827}.bank-account{font-size:9px;color:#64748b;margin-top:3px}.bank-balance{font-size:14px;font-weight:800;margin-top:8px}.bank-mutation{font-size:8px;color:#64748b;margin-top:5px}.bank-card-grid{padding:16px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.bank-dashboard-card{border:1px solid #e5e7eb;border-radius:10px;padding:15px;background:#fff;min-width:0}.bank-dashboard-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.bank-dashboard-name{font-size:12px;font-weight:850;color:#111827}.bank-dashboard-account{font-size:9px;color:#64748b;margin-top:3px}.bank-dashboard-status{font-size:8px;padding:4px 7px;border-radius:999px;background:#ecfdf5;color:#15803d;font-weight:750;white-space:nowrap}.bank-dashboard-balance-label{font-size:9px;color:#64748b;margin-top:15px}.bank-dashboard-balance{font-size:21px;font-weight:850;color:#111827;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bank-dashboard-mutation{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px}.bank-dashboard-mutation-box{border-radius:8px;padding:8px;background:#f8fafc}.bank-dashboard-mutation-label{font-size:8px;color:#64748b}.bank-dashboard-mutation-value{font-size:10px;font-weight:800;margin-top:3px}.bank-dashboard-footer{margin-top:10px;padding-top:9px;border-top:1px solid #eef0f2;display:flex;justify-content:space-between;gap:8px;font-size:8px;color:#64748b}.summary{padding:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.summary-box{padding:12px;border-radius:8px;background:#f8fafc}.summary-label{font-size:9px;color:#64748b}.summary-value{font-size:13px;font-weight:800;color:#111827;margin-top:4px}.status{font-size:9px;font-weight:750}.status.ok{color:#15803d}.status.warn{color:#b08a45}@media(max-width:1000px){.activity-grid{grid-template-columns:repeat(2,1fr)}.position-grid,.bank-card-grid{grid-template-columns:repeat(2,1fr)}.bank-list{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.activity-grid,.position-grid,.summary,.bank-card-grid{grid-template-columns:1fr}.bank-list{grid-template-columns:1fr}.welcome-box{align-items:flex-start;flex-direction:column}.position-value,.bank-dashboard-balance{font-size:20px}}
</style>
@endpush

@section('content')
<div class="dashboard">
    <div class="welcome-box">
        <div><div class="welcome-title">Selamat datang kembali</div><div class="welcome-text">Dashboard ERP menampilkan aktivitas hari ini dan posisi saldo terakhir dari ledger.</div></div>
        <div class="welcome-badge" id="dashboard-status">● Terhubung</div>
    </div>

    <div class="activity-grid">
        <div class="activity-card"><div class="activity-icon">↓</div><div class="activity-label">Pembelian</div><div class="activity-value" data-dashboard="purchase">Rp 0</div><div class="activity-meta"><span class="positive">Hari ini</span><span class="neutral" data-dashboard="purchase-count">0 transaksi</span></div></div>
        <div class="activity-card"><div class="activity-icon">↑</div><div class="activity-label">Penjualan</div><div class="activity-value" data-dashboard="sales">Rp 0</div><div class="activity-meta"><span class="positive">Hari ini</span><span class="neutral" data-dashboard="sales-count">0 transaksi</span></div></div>
        <div class="activity-card"><div class="activity-icon">⇄</div><div class="activity-label">Mutasi Bank</div><div class="activity-value" data-dashboard="bank-net">Rp 0</div><div class="activity-meta"><span class="positive" data-dashboard="bank-credit-small">+ Rp 0</span><span class="negative" data-dashboard="bank-debit-small">- Rp 0</span></div><div class="activity-meta"><span class="neutral">Mutasi hari ini</span><span class="neutral" data-dashboard="bank-mutation-count">0 mutasi</span></div></div>
        <div class="activity-card"><div class="activity-icon">◆</div><div class="activity-label">Pengeluaran</div><div class="activity-value" data-dashboard="expense">Rp 0</div><div class="activity-meta"><span class="negative">Hari ini</span><span class="neutral" data-dashboard="expense-count">0 transaksi</span></div></div>
    </div>

    <div class="position-section">
        <div class="position-header"><div class="position-title">Posisi Kas, Bank dan Valas</div><div class="position-subtitle">Saldo akhir berdasarkan saldo awal dan mutasi ledger</div></div>
        <div class="position-grid">
            <div class="position-card"><div class="position-label">Posisi Kas</div><div class="position-value" data-dashboard="cash-balance">Rp 0</div><div class="position-meta">Sisa Kas Rp</div><div class="position-breakdown"><span class="positive" data-dashboard="cash-in">+ Rp 0</span> Cash IN &nbsp; <span class="negative" data-dashboard="cash-out">- Rp 0</span> Cash OUT</div></div>
            <div class="position-card"><div class="position-label">Posisi Bank</div><div class="position-value" data-dashboard="bank-balance">Rp 0</div><div class="position-meta">Total saldo seluruh rekening aktif</div><div class="position-breakdown"><span class="positive" data-dashboard="bank-credit">+ Rp 0</span> Credit &nbsp; <span class="negative" data-dashboard="bank-debit">- Rp 0</span> Debit</div></div>
            <div class="position-card"><div class="position-label">Posisi Valas</div><div class="position-value" data-dashboard="forex-balance">Rp 0</div><div class="position-meta">Nilai sisa stok valas dalam Rupiah</div><div class="position-breakdown" data-dashboard="forex-detail">Saldo awal + pembelian − penjualan</div></div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header"><div><div class="dashboard-card-title">Kas & Bank — Posisi Setiap Rekening</div><div class="dashboard-card-subtitle">Satu kartu untuk setiap akun bank aktif. Saldo dihitung dari saldo awal dan mutasi rekening.</div></div></div>
        <div class="bank-card-grid" id="dashboard-bank-cards"></div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header"><div><div class="dashboard-card-title">Ringkasan Hari Ini</div><div class="dashboard-card-subtitle">Pergerakan saldo berdasarkan ledger</div></div></div>
        <div class="summary">
            <div class="summary-box"><div class="summary-label">Saldo Awal Kas</div><div class="summary-value" data-dashboard="opening-cash">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Saldo Awal Bank</div><div class="summary-value" data-dashboard="opening-bank">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Saldo Awal Valas</div><div class="summary-value" data-dashboard="opening-forex">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Total Posisi</div><div class="summary-value" data-dashboard="gross">Rp 0</div></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID',{maximumFractionDigits:2}).format(Number(value||0));
    const set = (key,value) => document.querySelectorAll('[data-dashboard="'+key+'"]').forEach(el=>el.textContent=money(value));
    const setText = (key,value) => document.querySelectorAll('[data-dashboard="'+key+'"]').forEach(el=>el.textContent=value);
    async function refreshDashboard(){
        try{
            const response=await fetch('{{ route('dashboard.data') }}',{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},cache:'no-store'});
            if(!response.ok) throw new Error('Dashboard data '+response.status);
            const data=await response.json();
            set('purchase',data.today?.purchase); set('sales',data.today?.sales);
            set('cash-balance',data.position?.cash); set('bank-balance',data.position?.bank); set('forex-balance',data.position?.forex); set('gross',data.position?.gross);
            set('cash-in',data.today?.cash_in); set('cash-out',data.today?.cash_out);
            set('bank-credit',data.today?.bank_credit); set('bank-debit',data.today?.bank_debit); set('bank-net',data.today?.bank_net);
            set('bank-credit-small',data.today?.bank_credit); set('bank-debit-small',data.today?.bank_debit);
            set('expense',data.today?.expense); set('opening-cash',data.opening?.cash); set('opening-bank',data.opening?.bank); set('opening-forex',data.opening?.forex);
            setText('purchase-count',(data.today?.purchase_count||0)+' transaksi');
            setText('sales-count',(data.today?.sales_count||0)+' transaksi');
            setText('expense-count',(data.today?.expense_count||0)+' transaksi');
            setText('bank-mutation-count',(data.today?.bank_mutation_count||0)+' mutasi');
            const forexDetail=document.querySelector('[data-dashboard="forex-detail"]);
            if(forexDetail){forexDetail.textContent='Saldo awal '+money(data.opening?.forex)+' + beli '+money(data.today?.purchase)+' − jual '+money(data.today?.sales);}
            const cards=document.getElementById('dashboard-bank-cards');
            cards.innerHTML='';
            (data.bank_accounts||[]).forEach(bank=>{
                const item=document.createElement('div'); item.className='bank-dashboard-card';
                const status=bank.is_active===false ? 'Nonaktif' : 'Aktif';
                item.innerHTML='<div class="bank-dashboard-head"><div><div class="bank-dashboard-name">'+escapeHtml(bank.bank_name||'Bank')+'</div><div class="bank-dashboard-account">'+escapeHtml(bank.account_number||'')+(bank.account_name?' · '+escapeHtml(bank.account_name):'')+'</div></div><div class="bank-dashboard-status">'+status+'</div></div>'
                    +'<div class="bank-dashboard-balance-label">Saldo Rekening</div><div class="bank-dashboard-balance">'+money(bank.balance)+'</div>'
                    +'<div class="bank-dashboard-mutation"><div class="bank-dashboard-mutation-box"><div class="bank-dashboard-mutation-label">Credit Hari Ini</div><div class="bank-dashboard-mutation-value positive">+ '+money(bank.today_credit)+'</div></div><div class="bank-dashboard-mutation-box"><div class="bank-dashboard-mutation-label">Debit Hari Ini</div><div class="bank-dashboard-mutation-value negative">- '+money(bank.today_debit)+'</div></div></div>'
                    +'<div class="bank-dashboard-footer"><span>'+((bank.today_mutation_count||0))+' mutasi hari ini</span><span>Net '+money(bank.today_net)+'</span></div>';
                cards.appendChild(item);
            });
            if(!cards.children.length) cards.innerHTML='<div class="bank-dashboard-card"><div class="bank-dashboard-name">Belum ada akun bank aktif</div><div class="bank-dashboard-account">Tambahkan rekening melalui Kas & Bank.</div></div>';
            const status=document.getElementById('dashboard-status');
            if(status){status.textContent='● Diperbarui '+new Date().toLocaleTimeString('id-ID');status.className='welcome-badge';}
        }catch(error){
            const status=document.getElementById('dashboard-status'); if(status){status.textContent='● Gagal memuat saldo';status.className='welcome-badge';}
            console.error(error);
        }
    }
    function escapeHtml(value){return String(value??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
    refreshDashboard(); setInterval(refreshDashboard,10000);
})();
</script>
@endpush

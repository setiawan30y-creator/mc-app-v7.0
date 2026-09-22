@extends('layouts.app')

@section('title', 'MC Almara — Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.dashboard{display:flex;flex-direction:column;gap:18px}.welcome-box{background:linear-gradient(135deg,#fff,#f4fbf8);border:1px solid #e5e7eb;border-radius:12px;padding:20px 22px;display:flex;align-items:center;justify-content:space-between;gap:20px}.welcome-title{font-size:19px;font-weight:800;color:#0f172a;margin-bottom:5px}.welcome-text{font-size:11px;color:#64748b}.welcome-badge{padding:8px 12px;border-radius:8px;background:#e7f5ef;color:#147957;font-size:10px;font-weight:750}.finance-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}.finance-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:15px;min-width:0}.finance-icon{width:27px;height:27px;border-radius:7px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;font-size:12px;margin-bottom:12px}.finance-label{font-size:10px;color:#64748b;margin-bottom:8px}.finance-value{font-size:16px;font-weight:800;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.finance-meta{margin-top:8px;display:flex;justify-content:space-between;gap:6px;font-size:9px}.finance-positive{color:#15803d;font-weight:700}.finance-negative{color:#b91c1c;font-weight:700}.finance-neutral{color:#64748b;font-weight:650}.dashboard-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden}.dashboard-card-header{padding:15px 17px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}.dashboard-card-title{font-size:12px;font-weight:800;color:#111827}.dashboard-card-subtitle{margin-top:3px;font-size:9px;color:#9ca3af}.balance-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:16px}.balance-card{border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fafafa}.balance-label{font-size:10px;color:#64748b}.balance-value{font-size:22px;font-weight:850;color:#111827;margin-top:7px}.balance-meta{display:flex;gap:18px;margin-top:9px;font-size:9px}.balance-in{color:#15803d;font-weight:700}.balance-out{color:#b91c1c;font-weight:700}.bank-list{padding:0 16px 16px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.bank-item{border:1px solid #edf0f2;border-radius:8px;padding:11px}.bank-name{font-size:10px;font-weight:800;color:#111827}.bank-account{font-size:9px;color:#64748b;margin-top:3px}.bank-balance{font-size:14px;font-weight:800;margin-top:8px}.bank-mutation{font-size:8px;color:#64748b;margin-top:5px}.summary{padding:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.summary-box{padding:12px;border-radius:8px;background:#f8fafc}.summary-label{font-size:9px;color:#64748b}.summary-value{font-size:13px;font-weight:800;color:#111827;margin-top:4px}.status{font-size:9px;font-weight:750}.status.ok{color:#15803d}.status.warn{color:#b08a45}@media(max-width:1200px){.finance-grid{grid-template-columns:repeat(3,1fr)}.bank-list{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.finance-grid{grid-template-columns:repeat(2,1fr)}.balance-grid,.summary{grid-template-columns:1fr}.bank-list{grid-template-columns:1fr}.welcome-box{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="dashboard">
    <div class="welcome-box">
        <div><div class="welcome-title">Selamat datang kembali</div><div class="welcome-text">Saldo operasional akan mengikuti transaksi Cash, Transfer dan Split secara real-time.</div></div>
        <div class="welcome-badge">● Operasional Aktif</div>
    </div>

    <div class="finance-grid">
        <div class="finance-card"><div class="finance-icon">↓</div><div class="finance-label">Pembelian</div><div class="finance-value" data-dashboard="purchase">Rp 0</div><div class="finance-meta"><span class="finance-positive">Hari ini</span><span class="finance-neutral" data-dashboard="transaction-count">0 transaksi</span></div></div>
        <div class="finance-card"><div class="finance-icon">↑</div><div class="finance-label">Penjualan</div><div class="finance-value" data-dashboard="sales">Rp 0</div><div class="finance-meta"><span class="finance-positive">Hari ini</span><span class="finance-neutral">IDR</span></div></div>
        <div class="finance-card"><div class="finance-icon">💵</div><div class="finance-label">Kas Rp — Saldo Akhir</div><div class="finance-value" data-dashboard="cash-balance">Rp 0</div><div class="finance-meta"><span class="finance-positive" data-dashboard="cash-in">+ Rp 0</span><span class="finance-negative" data-dashboard="cash-out">- Rp 0</span></div></div>
        <div class="finance-card"><div class="finance-icon">🏦</div><div class="finance-label">Bank — Saldo Akhir</div><div class="finance-value" data-dashboard="bank-balance">Rp 0</div><div class="finance-meta"><span class="finance-positive" data-dashboard="bank-credit">+ Rp 0</span><span class="finance-negative" data-dashboard="bank-debit">- Rp 0</span></div></div>
        <div class="finance-card"><div class="finance-icon">⇄</div><div class="finance-label">Mutasi Bank Hari Ini</div><div class="finance-value" data-dashboard="bank-net">Rp 0</div><div class="finance-meta"><span class="finance-positive" data-dashboard="bank-credit-small">+ Rp 0</span><span class="finance-negative" data-dashboard="bank-debit-small">- Rp 0</span></div></div>
        <div class="finance-card"><div class="finance-icon">◆</div><div class="finance-label">Total Posisi Rp</div><div class="finance-value" data-dashboard="gross">Rp 0</div><div class="finance-meta"><span class="finance-neutral">Kas + Bank + Valas</span><span class="finance-neutral">Saldo</span></div></div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header"><div><div class="dashboard-card-title">Saldo Operasional</div><div class="dashboard-card-subtitle">Sisa saldo setelah transaksi terakhir</div></div><div class="status ok" id="dashboard-status">● Terhubung</div></div>
        <div class="balance-grid">
            <div class="balance-card"><div class="balance-label">SISA KAS RP</div><div class="balance-value" data-dashboard="cash-balance-large">Rp 0</div><div class="balance-meta"><span class="balance-in" data-dashboard="cash-in-large">IN + Rp 0</span><span class="balance-out" data-dashboard="cash-out-large">OUT - Rp 0</span></div></div>
            <div class="balance-card"><div class="balance-label">SISA SALDO BANK</div><div class="balance-value" data-dashboard="bank-balance-large">Rp 0</div><div class="balance-meta"><span class="balance-in" data-dashboard="bank-credit-large">IN + Rp 0</span><span class="balance-out" data-dashboard="bank-debit-large">OUT - Rp 0</span></div></div>
        </div>
        <div class="bank-list" id="dashboard-bank-list"></div>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header"><div><div class="dashboard-card-title">Ringkasan Hari Ini</div><div class="dashboard-card-subtitle">Pergerakan saldo berdasarkan ledger</div></div></div>
        <div class="summary">
            <div class="summary-box"><div class="summary-label">Saldo Awal Kas</div><div class="summary-value" data-dashboard="opening-cash">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Saldo Awal Bank</div><div class="summary-value" data-dashboard="opening-bank">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Net Kas Hari Ini</div><div class="summary-value" data-dashboard="cash-net">Rp 0</div></div>
            <div class="summary-box"><div class="summary-label">Net Bank Hari Ini</div><div class="summary-value" data-dashboard="bank-net-summary">Rp 0</div></div>
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
            set('cash-balance',data.position?.cash); set('cash-balance-large',data.position?.cash);
            set('bank-balance',data.position?.bank); set('bank-balance-large',data.position?.bank); set('gross',data.position?.gross);
            set('cash-in',data.today?.cash_in); set('cash-in-large',data.today?.cash_in); set('cash-out',data.today?.cash_out); set('cash-out-large',data.today?.cash_out);
            set('bank-credit',data.today?.bank_credit); set('bank-credit-small',data.today?.bank_credit); set('bank-credit-large',data.today?.bank_credit);
            set('bank-debit',data.today?.bank_debit); set('bank-debit-small',data.today?.bank_debit); set('bank-debit-large',data.today?.bank_debit);
            set('bank-net',data.today?.bank_net); set('cash-net',data.today?.cash_net); set('bank-net-summary',data.today?.bank_net);
            set('opening-cash',data.opening?.cash); set('opening-bank',data.opening?.bank);
            setText('transaction-count',(data.today?.transaction_count||0)+' transaksi');
            const list=document.getElementById('dashboard-bank-list');
            list.innerHTML='';
            (data.bank_accounts||[]).forEach(bank=>{
                const item=document.createElement('div'); item.className='bank-item';
                item.innerHTML='<div class="bank-name">'+escapeHtml(bank.bank_name||'Bank')+'</div><div class="bank-account">'+escapeHtml(bank.account_number||'')+'</div><div class="bank-balance">'+money(bank.balance)+'</div><div class="bank-mutation">Hari ini: + '+money(bank.today_credit)+' / - '+money(bank.today_debit)+'</div>';
                list.appendChild(item);
            });
            if(!list.children.length) list.innerHTML='<div class="bank-item"><div class="bank-name">Belum ada rekening aktif</div><div class="bank-account">Tambahkan rekening pada Pengaturan Bank</div></div>';
            document.getElementById('dashboard-status').textContent='● Data diperbarui '+new Date().toLocaleTimeString('id-ID');
        }catch(error){
            const status=document.getElementById('dashboard-status'); if(status){status.textContent='● Gagal memuat saldo';status.className='status warn';}
            console.error(error);
        }
    }
    function escapeHtml(value){return String(value??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
    refreshDashboard(); setInterval(refreshDashboard,10000);
})();
</script>
@endpush

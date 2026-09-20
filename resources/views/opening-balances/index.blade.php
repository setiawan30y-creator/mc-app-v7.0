@extends('layouts.app')

@section('title', 'Saldo Awal · MC Almara')
@section('page-title', 'Saldo Awal')

@section('content')
<div class="ob-page">
    <header class="ob-header">
        <div><div class="ob-eyebrow">SETUP / OPENING BALANCE</div><h1>Saldo Awal</h1><p>Masukkan posisi kas, rekening, dan valas sebelum operasional dimulai.</p></div>
        <form method="GET" class="ob-date"><label>Tanggal Mulai</label><input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"></form>
    </header>

    @if(session('success')) <div class="ob-alert">✓ {{ session('success') }}</div> @endif
    @if($errors->any()) <div class="ob-error">{{ $errors->first() }}</div> @endif

    <div class="ob-summary" id="obSummary">
        <div class="ob-summary-card cash"><div class="ob-summary-icon">Rp</div><div><span>Total Rp / Kas</span><strong id="summaryCash">Rp 0</strong></div></div>
        <div class="ob-summary-card bank"><div class="ob-summary-icon">▣</div><div><span>Total Rekening</span><strong id="summaryBank">Rp 0</strong></div></div>
        <div class="ob-summary-card forex"><div class="ob-summary-icon">◎</div><div><span>Total Valas</span><strong id="summaryForex">Rp 0</strong></div></div>
        <div class="ob-summary-card gross"><div class="ob-summary-icon">Σ</div><div><span>Total Gross</span><strong id="summaryGross">Rp 0</strong></div></div>
        <div class="ob-summary-chart">
            <div class="ob-chart-head"><div><b>Komposisi Saldo Awal</b><span>Nilai dalam Rupiah</span></div><strong id="summaryGrossMini">Rp 0</strong></div>
            <div class="ob-bar"><i id="barCash"></i><i id="barBank"></i><i id="barForex"></i></div>
            <div class="ob-legend"><span><i class="dot cash-dot"></i>Kas <b id="legendCash">0%</b></span><span><i class="dot bank-dot"></i>Rekening <b id="legendBank">0%</b></span><span><i class="dot forex-dot"></i>Valas <b id="legendForex">0%</b></span></div>
        </div>
    </div>

    <form method="POST" action="{{ route('opening-balances.store') }}" id="openingForm">
        @csrf
        <input type="hidden" name="balance_date" value="{{ $date }}">
        <div class="ob-tabs" role="tablist">
            <button type="button" class="active" data-tab="cash">Rp / Kas</button>
            <button type="button" data-tab="bank">Rekening</button>
            <button type="button" data-tab="forex">Valas</button>
        </div>

        <section class="ob-panel active" data-panel="cash">
            <div class="ob-panel-head"><div><h2>Saldo Awal Rp / Kas</h2><p>Uang tunai fisik yang tersedia saat usaha mulai.</p></div><span class="ob-badge">RUPIAH</span></div>
            <div class="ob-cash-grid"><label>Saldo Kas Awal (Rp)<input id="cashAmount" name="cash_amount" type="number" min="0" step="0.01" value="{{ old('cash_amount', $balances->where('balance_type', 'cash')->sum('amount_rp')) }}" placeholder="0"></label><label>Keterangan<input name="cash_notes" type="text" value="{{ old('cash_notes', optional($balances->firstWhere('balance_type', 'cash'))->notes) }}" placeholder="Contoh: Kas operasional awal"></label></div>
            <div class="ob-example">Saldo ini menjadi titik awal rekonsiliasi kas dan Closing Rp. Bukan transaksi penjualan/pembelian.</div>
        </section>

        <section class="ob-panel" data-panel="bank">
            <div class="ob-panel-head"><div><h2>Saldo Awal Rekening</h2><p>Isi saldo rekening bank yang sudah terdaftar di Master Rekening.</p></div><button type="button" class="ob-add" id="addBank">+ Tambah Rekening</button></div>
            <div class="ob-table-wrap"><table class="ob-table"><thead><tr><th>Rekening</th><th>Currency</th><th>Saldo Awal</th><th>Keterangan</th><th></th></tr></thead><tbody id="bankRows"></tbody></table></div>
        </section>

        <section class="ob-panel" data-panel="forex">
            <div class="ob-panel-head"><div><h2>Saldo Awal Valas</h2><p>Input per currency dan pecahan. Nilai Rp otomatis = Qty × Kurs Awal.</p></div><button type="button" class="ob-add" id="addForex">+ Tambah Pecahan</button></div>
            <div class="ob-table-wrap"><table class="ob-table forex-table"><thead><tr><th>Currency / Pecahan</th><th>Qty</th><th>Kurs Awal (Rp)</th><th>Nilai Rp</th><th></th></tr></thead><tbody id="forexRows"></tbody></table></div>
        </section>

        <div class="ob-bottom"><div><strong>Saldo awal menjadi fondasi operasional.</strong><span>Setelah disimpan, angka ini digunakan sebagai titik awal perhitungan stok, kas, rekening, dan rekonsiliasi.</span></div><button type="submit" class="ob-save">Simpan Saldo Awal</button></div>
    </form>
</div>

<template id="bankTemplate"><tr><td><select name="banks[__INDEX__][id]"><option value="">Pilih rekening</option>@foreach($banks as $bank)<option value="{{ $bank->id }}">{{ $bank->bank_name }} · {{ $bank->account_number }}</option>@endforeach</select></td><td class="muted">Otomatis</td><td><input class="bank-amount" name="banks[__INDEX__][amount]" type="number" min="0" step="0.01" placeholder="0"></td><td><input name="banks[__INDEX__][notes]" type="text" placeholder="Opsional"></td><td><button type="button" class="remove">×</button></td></tr></template>
<template id="forexTemplate"><tr><td><select name="forex[__INDEX__][denomination_id]"><option value="">Pilih pecahan</option>@foreach($denominations as $denom)<option value="{{ $denom->id }}">{{ $denom->variant?->currency?->code ?? '—' }} · {{ $denom->display_label }} · {{ $denom->variant?->name ?? '' }}</option>@endforeach</select></td><td><input class="fx-qty" name="forex[__INDEX__][quantity]" type="number" min="0" step="0.0001" placeholder="0"></td><td><input class="fx-rate" name="forex[__INDEX__][rate]" type="number" min="0" step="0.000001" placeholder="0"></td><td class="fx-rp">Rp 0</td><td><button type="button" class="remove">×</button></td></tr></template>

<style>
.ob-page{width:100%;max-width:1400px;margin:0 auto;padding:14px 16px;color:var(--ui-text);box-sizing:border-box}.ob-header{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:12px}.ob-eyebrow{font-size:8px;letter-spacing:1.2px;font-weight:800;color:var(--ui-text-muted);margin-bottom:4px}.ob-header h1{margin:0;font-size:20px;color:var(--ui-primary-dark)}.ob-header p{margin:4px 0 0;font-size:10px;color:var(--ui-text-muted)}.ob-date{display:flex;align-items:center;gap:8px}.ob-date label{font-size:9px;font-weight:800;color:var(--ui-text-secondary)}.ob-date input{height:34px;border:1px solid var(--ui-border);border-radius:var(--ui-input-radius);background:var(--ui-card-bg);color:var(--ui-text);padding:0 9px;font-size:9px}.ob-alert,.ob-error{padding:9px 12px;border-radius:var(--ui-button-radius);font-size:9px;font-weight:750;margin-bottom:10px}.ob-alert{background:var(--ui-surface-soft);border:1px solid var(--ui-border);color:var(--ui-primary)}.ob-error{background:#fff3f1;border:1px solid #efc9c3;color:#a34b40}
.ob-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:10px}.ob-summary-card{min-height:72px;display:flex;align-items:center;gap:10px;padding:11px 12px;box-sizing:border-box;background:var(--ui-card-bg);border:1px solid var(--ui-card-border,var(--ui-border));border-radius:var(--ui-card-radius);box-shadow:var(--ui-card-shadow);position:relative;overflow:hidden}.ob-summary-card:after{content:'';position:absolute;left:0;bottom:0;height:2px;width:100%;background:var(--ui-border)}.ob-summary-card.cash:after{background:var(--ui-primary)}.ob-summary-card.bank:after{background:var(--ui-icon)}.ob-summary-card.forex:after{background:var(--ui-gold)}.ob-summary-card.gross:after{background:var(--ui-primary-dark)}.ob-summary-icon{width:32px;height:32px;display:grid;place-items:center;flex:0 0 32px;border-radius:8px;background:var(--ui-surface-soft);color:var(--ui-icon);font-size:11px;font-weight:900}.ob-summary-card.cash .ob-summary-icon{color:var(--ui-primary)}.ob-summary-card.forex .ob-summary-icon{color:var(--ui-gold)}.ob-summary-card.gross .ob-summary-icon{color:var(--ui-primary-dark)}.ob-summary-card span{display:block;color:var(--ui-text-muted);font-size:8px;font-weight:750}.ob-summary-card strong{display:block;margin-top:4px;color:var(--ui-text);font-size:13px;font-weight:900;font-variant-numeric:tabular-nums}.ob-summary-chart{grid-column:1/-1;padding:11px 13px;background:var(--ui-card-bg);border:1px solid var(--ui-card-border,var(--ui-border));border-radius:var(--ui-card-radius);box-shadow:var(--ui-card-shadow)}.ob-chart-head{display:flex;align-items:center;justify-content:space-between;gap:10px}.ob-chart-head b{display:block;font-size:9px}.ob-chart-head span{display:block;margin-top:2px;color:var(--ui-text-muted);font-size:7px}.ob-chart-head>strong{font-size:11px;color:var(--ui-primary-dark)}.ob-bar{display:flex;width:100%;height:9px;overflow:hidden;border-radius:99px;margin:10px 0 7px;background:var(--ui-surface-soft)}.ob-bar i{display:block;height:100%;min-width:0;transition:width .2s ease}.ob-bar i:nth-child(1){background:var(--ui-primary)}.ob-bar i:nth-child(2){background:var(--ui-icon)}.ob-bar i:nth-child(3){background:var(--ui-gold)}.ob-legend{display:flex;gap:16px;flex-wrap:wrap;color:var(--ui-text-muted);font-size:7px}.ob-legend span{display:inline-flex;align-items:center;gap:5px}.ob-legend b{color:var(--ui-text-secondary)}.dot{width:6px;height:6px;border-radius:50%;display:inline-block}.cash-dot{background:var(--ui-primary)}.bank-dot{background:var(--ui-icon)}.forex-dot{background:var(--ui-gold)}
.ob-tabs{display:flex;gap:5px;margin-bottom:10px}.ob-tabs button{height:34px;padding:0 16px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-surface-soft);color:var(--ui-text-secondary);font-size:9px;font-weight:800;cursor:pointer}.ob-tabs button.active{background:var(--ui-primary);border-color:var(--ui-primary);color:#fff}.ob-panel{display:none;background:var(--ui-card-bg);border:1px solid var(--ui-card-border,var(--ui-border));border-radius:var(--ui-card-radius);box-shadow:var(--ui-card-shadow);overflow:hidden}.ob-panel.active{display:block}.ob-panel-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:14px;border-bottom:1px solid var(--ui-border)}.ob-panel-head h2{margin:0;font-size:12px}.ob-panel-head p{margin:3px 0 0;color:var(--ui-text-muted);font-size:9px}.ob-badge{padding:5px 8px;border-radius:999px;background:var(--ui-surface-soft);color:var(--ui-primary);font-size:7px;font-weight:900}.ob-add{height:30px;padding:0 10px;border:1px solid var(--ui-border);border-radius:var(--ui-button-radius);background:var(--ui-surface-soft);color:var(--ui-primary);font-size:8px;font-weight:800;cursor:pointer}.ob-cash-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:15px}.ob-cash-grid label{display:flex;flex-direction:column;gap:6px;font-size:9px;font-weight:800;color:var(--ui-text-secondary)}.ob-cash-grid input,.ob-table input,.ob-table select{height:34px;box-sizing:border-box;border:1px solid var(--ui-border);border-radius:var(--ui-input-radius);background:var(--ui-surface);color:var(--ui-text);padding:0 8px;font-size:9px;min-width:0}.ob-example{margin:0 15px 15px;padding:9px 10px;border-radius:7px;background:var(--ui-surface-soft);color:var(--ui-text-muted);font-size:8px;line-height:1.5}.ob-table-wrap{overflow:auto}.ob-table{width:100%;border-collapse:separate;border-spacing:0;min-width:720px}.ob-table th{padding:9px 10px;text-align:left;background:var(--ui-surface-soft);border-bottom:1px solid var(--ui-border);color:var(--ui-text-muted);font-size:7px;font-weight:900;white-space:nowrap}.ob-table td{padding:7px 10px;border-bottom:1px solid var(--ui-border);font-size:8px;color:var(--ui-text-secondary);background:var(--ui-card-bg)}.ob-table td input,.ob-table td select{width:100%}.ob-table .muted{color:var(--ui-text-muted);font-size:8px}.remove{width:28px;height:28px;border:0;border-radius:6px;background:var(--ui-surface-soft);color:var(--ui-text-muted);cursor:pointer;font-size:16px}.remove:hover{color:#a34b40}.fx-rp{min-width:125px;text-align:right;font-variant-numeric:tabular-nums;font-weight:800;color:var(--ui-primary-dark)!important}.ob-bottom{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:10px;padding:12px 2px}.ob-bottom strong{display:block;font-size:9px}.ob-bottom span{display:block;margin-top:3px;color:var(--ui-text-muted);font-size:8px}.ob-save{height:36px;padding:0 16px;border:0;border-radius:var(--ui-button-radius);background:var(--ui-primary);color:#fff;font-size:9px;font-weight:900;cursor:pointer;white-space:nowrap}
@media(max-width:900px){.ob-summary{grid-template-columns:1fr 1fr}.ob-summary-chart{grid-column:1/-1}}@media(max-width:700px){.ob-page{padding:10px}.ob-header{align-items:stretch;flex-direction:column}.ob-date{justify-content:space-between}.ob-date input{flex:1}.ob-cash-grid{grid-template-columns:1fr}.ob-bottom{align-items:stretch;flex-direction:column}.ob-save{width:100%}}@media(max-width:480px){.ob-summary{grid-template-columns:1fr}.ob-summary-chart{grid-column:auto}.ob-legend{gap:9px}}
</style>
<script>
(function(){
 const tabs=document.querySelectorAll('.ob-tabs button'), panels=document.querySelectorAll('.ob-panel');
 const bankRows=document.getElementById('bankRows'), forexRows=document.getElementById('forexRows');
 let bi=0, fi=0;
 const savedBalances=@json($balances->map(fn($b)=>[
   'balance_type'=>$b->balance_type,
   'bank_account_id'=>$b->bank_account_id,
   'currency_denomination_id'=>$b->currency_denomination_id,
   'quantity'=>$b->quantity,
   'rate'=>$b->rate,
   'amount_rp'=>$b->amount_rp,
   'notes'=>$b->notes,
 ])->values());
 tabs.forEach(btn=>btn.addEventListener('click',()=>{tabs.forEach(x=>x.classList.remove('active'));panels.forEach(x=>x.classList.remove('active'));btn.classList.add('active');document.querySelector('[data-panel="'+btn.dataset.tab+'"]').classList.add('active')}));
 function add(tpl,wrap,index){wrap.insertAdjacentHTML('beforeend',document.getElementById(tpl).innerHTML.replaceAll('__INDEX__',index));return wrap.lastElementChild;}
 document.getElementById('addBank').addEventListener('click',()=>add('bankTemplate',bankRows,bi++));
 document.getElementById('addForex').addEventListener('click',()=>add('forexTemplate',forexRows,fi++));
 function money(v){return 'Rp '+new Intl.NumberFormat('id-ID',{minimumFractionDigits:0,maximumFractionDigits:2}).format(v||0)}
 function updateSummary(){
   const cash=parseFloat(document.getElementById('cashAmount')?.value)||0;
   let bank=0; document.querySelectorAll('.bank-amount').forEach(x=>bank+=parseFloat(x.value)||0);
   let forex=0; document.querySelectorAll('#forexRows tr').forEach(tr=>{const q=parseFloat(tr.querySelector('.fx-qty')?.value)||0,r=parseFloat(tr.querySelector('.fx-rate')?.value)||0;forex+=q*r});
   const gross=cash+bank+forex, pct=v=>gross?(v/gross*100):0;
   document.getElementById('summaryCash').textContent=money(cash);document.getElementById('summaryBank').textContent=money(bank);document.getElementById('summaryForex').textContent=money(forex);document.getElementById('summaryGross').textContent=money(gross);document.getElementById('summaryGrossMini').textContent=money(gross);
   document.getElementById('barCash').style.width=pct(cash)+'%';document.getElementById('barBank').style.width=pct(bank)+'%';document.getElementById('barForex').style.width=pct(forex)+'%';
   document.getElementById('legendCash').textContent=Math.round(pct(cash))+'%';document.getElementById('legendBank').textContent=Math.round(pct(bank))+'%';document.getElementById('legendForex').textContent=Math.round(pct(forex))+'%';
 }
 function restoreBank(item){const tr=add('bankTemplate',bankRows,bi++);const select=tr.querySelector('select');const amount=tr.querySelector('.bank-amount');const notes=tr.querySelector('input[name$="[notes]"]');if(select)select.value=item.bank_account_id??'';if(amount)amount.value=item.amount_rp??'';if(notes)notes.value=item.notes??'';}
 function restoreForex(item){const tr=add('forexTemplate',forexRows,fi++);const select=tr.querySelector('select');const qty=tr.querySelector('.fx-qty');const rate=tr.querySelector('.fx-rate');if(select)select.value=item.currency_denomination_id??'';if(qty)qty.value=item.quantity??'';if(rate)rate.value=item.rate??'';const rp=tr.querySelector('.fx-rp');if(rp)rp.textContent=money((parseFloat(item.quantity)||0)*(parseFloat(item.rate)||0));}
 document.addEventListener('click',e=>{if(e.target.matches('.remove')){e.target.closest('tr').remove();updateSummary()}});
 document.addEventListener('input',e=>{if(e.target.matches('.fx-qty,.fx-rate')){const tr=e.target.closest('tr'),q=parseFloat(tr.querySelector('.fx-qty').value)||0,r=parseFloat(tr.querySelector('.fx-rate').value)||0;tr.querySelector('.fx-rp').textContent=money(q*r)}if(e.target.matches('.fx-qty,.fx-rate,.bank-amount,#cashAmount'))updateSummary()});
 const savedCash=savedBalances.find(x=>x.balance_type==='cash');
 if(savedCash){const cash=document.getElementById('cashAmount');if(cash)cash.value=savedCash.amount_rp??'';const notes=document.querySelector('input[name="cash_notes"]');if(notes)notes.value=savedCash.notes??'';}
 const savedBanks=savedBalances.filter(x=>x.balance_type==='bank');
 const savedForex=savedBalances.filter(x=>x.balance_type==='forex');
 if(savedBanks.length){savedBanks.forEach(restoreBank)}else{add('bankTemplate',bankRows,bi++);}
 if(savedForex.length){savedForex.forEach(restoreForex)}else{add('forexTemplate',forexRows,fi++);}
 updateSummary();
})();
</script>
@endsection

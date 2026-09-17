@extends('layouts.app')

@section('content')
<style>
    .trx-page{padding:16px 20px 28px;min-height:calc(100vh - 70px);background:#f7f9f8}
    .trx-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:12px}
    .trx-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#718078}
    .trx-title{margin:2px 0 0;font-size:21px;font-weight:700;line-height:1.2}
    .trx-subtitle{margin-top:4px;font-size:12px;color:#77827d}
    .trx-btn{font-size:11px;padding:7px 10px;border-radius:6px}
    .trx-paper{background:#fff;border:1px solid #dfe7e2;border-radius:7px;overflow:hidden;margin-bottom:10px}
    .trx-section-head{padding:8px 11px;background:#f4f7f5;border-bottom:1px solid #dfe7e2;font-size:11px;font-weight:700;color:#425049}
    .trx-section-body{padding:11px}
    .trx-label{font-size:10px;font-weight:600;color:#65716b;margin-bottom:3px}
    .trx-control{font-size:12px!important;min-height:32px!important;height:32px!important;padding:4px 8px!important;border-radius:5px!important;border-color:#d4ddd8!important}
    .trx-control:focus{border-color:#8fa49a!important;box-shadow:0 0 0 2px rgba(110,140,125,.12)!important}
    .customer-meta{display:flex;flex-wrap:wrap;gap:5px 12px;margin-top:5px;font-size:10px;color:#7a8580}
    .customer-meta strong{color:#46524c}
    .customer-search-wrap{position:relative}
    .customer-search-icon{position:absolute;right:9px;top:8px;color:#8a9690;font-size:11px;pointer-events:none}
    .customer-history{display:none;margin-top:9px;border-top:1px solid #e4ebe7;padding-top:8px}
    .customer-history-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:5px}
    .customer-history-title{font-size:10px;font-weight:700;color:#4b5952;text-transform:uppercase;letter-spacing:.04em}
    .customer-history-note{font-size:9px;color:#8a948f}
    .customer-history-table{width:100%;border-collapse:collapse;font-size:10px}
    .customer-history-table th,.customer-history-table td{border-bottom:1px solid #e5ebe8;padding:4px 6px;vertical-align:middle}
    .customer-history-table th{font-size:9px;color:#738079;text-transform:uppercase;background:#fafcfb;white-space:nowrap}
    .customer-history-table td.amount{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    .customer-history-table .empty{text-align:center;color:#89938e;padding:9px}
    .direction-switch{display:none!important}
    .direction-btn{border:1px solid #cfd9d4;background:#fff;color:#526059;border-radius:5px;padding:6px 13px;font-size:10px;font-weight:700;cursor:pointer}
    .direction-btn.active{background:#26352e;color:#fff;border-color:#26352e}
    .row-direction{display:none!important}
    .row-direction-btn{border:1px solid #d1dbd6;background:#fff;color:#66736c;border-radius:4px;padding:3px 5px;font-size:8px;font-weight:700;cursor:pointer}
    .row-direction-btn.active{background:#26352e;color:#fff;border-color:#26352e}
    .trx-table{width:100%;border-collapse:collapse;font-size:11px}
    .trx-table th{background:#f8faf9;border:1px solid #dfe7e2;padding:6px 7px;font-size:9px;text-transform:uppercase;letter-spacing:.04em;color:#68746e;white-space:nowrap}
    .trx-table td{border:1px solid #dfe7e2;padding:5px 6px;vertical-align:middle}
    .trx-table input,.trx-table select{width:100%;font-size:11px;min-height:29px;height:29px;padding:3px 6px;border:1px solid #d5ded9;border-radius:4px;background:#fff}
    .trx-table .currency-cell{min-width:105px}.trx-table .variant-cell{min-width:145px}.trx-table .denom-cell{min-width:125px}.trx-table .qty-cell{min-width:85px}.trx-table .rate-cell{min-width:115px}.trx-table .amount-cell{min-width:125px;text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    .remove-row{border:0;background:transparent;color:#9a3b3b;font-size:15px;line-height:1;cursor:pointer;padding:3px 6px}
    .trx-add{margin-top:7px;border:1px dashed #b9c8c0;background:#fbfdfc;color:#53635b;border-radius:5px;padding:6px 9px;font-size:10px;font-weight:700;cursor:pointer}
    .trx-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
    .trx-summary-box{border:1px solid #dfe7e2;border-radius:5px;padding:8px 10px;background:#fff}
    .trx-summary-label{font-size:9px;color:#7b8681;text-transform:uppercase;letter-spacing:.05em}.trx-summary-value{margin-top:2px;font-size:16px;font-weight:700;font-variant-numeric:tabular-nums}.trx-summary-note{font-size:9px;color:#89938f;margin-top:2px}
    .trx-difference{margin-top:8px;border:1px solid #dfe7e2;border-radius:5px;padding:9px 10px;display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fafcfb}
    .trx-difference-label{font-size:10px;font-weight:700;color:#536159;text-transform:uppercase}.trx-difference-value{font-size:17px;font-weight:800;font-variant-numeric:tabular-nums}.trx-difference-note{font-size:9px;color:#7f8985}
    .payment-grid{display:grid;grid-template-columns:220px 1fr;gap:10px;align-items:start}
    .payment-methods{display:flex;gap:5px;flex-wrap:wrap}.payment-method-btn{border:1px solid #cfd9d4;background:#fff;color:#526059;border-radius:5px;padding:7px 10px;font-size:10px;font-weight:700;cursor:pointer}.payment-method-btn.active{background:#26352e;color:#fff;border-color:#26352e}
    .payment-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}.payment-field{display:none}.payment-field.show{display:block}.payment-total{font-size:17px;font-weight:800;font-variant-numeric:tabular-nums}.payment-help{font-size:9px;color:#7f8985;margin-top:3px}.payment-error{font-size:10px;color:#8d3535;margin-top:5px}
    .trx-actions{display:flex;justify-content:flex-end;gap:6px}
    .trx-error{padding:8px 10px;margin-bottom:10px;border:1px solid #e8caca;background:#fff6f6;color:#8d3535;border-radius:6px;font-size:11px}
    @media(max-width:900px){.trx-page{padding:12px}.trx-summary{grid-template-columns:1fr 1fr}.trx-table-wrap{overflow-x:auto}.trx-table{min-width:1100px}.customer-history{overflow-x:auto}.customer-history-table{min-width:650px}.payment-grid{grid-template-columns:1fr}.payment-fields{grid-template-columns:1fr 1fr}}
</style>

<div class="trx-page">
    <div class="trx-head">
        <div><div class="trx-eyebrow">Teller · Operasional</div><h1 class="trx-title">Transaksi Baru</h1><div class="trx-subtitle">Satu halaman untuk customer, item, ringkasan, pembayaran dan penyimpanan transaksi.</div></div>
        <a href="{{ route('teller.index') }}" class="btn btn-outline-secondary trx-btn">← Kembali</a>
    </div>

    @if($errors->any())
        <div class="trx-error"><strong>Transaksi belum tersimpan.</strong><ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm">
        @csrf
        <div class="trx-paper">
            <div class="trx-section-head">01 · CUSTOMER</div>
            <div class="trx-section-body">
                <div class="row g-2">
                    <div class="col-lg-5">
                        <label class="trx-label">Cari Customer</label>
                        <div class="customer-search-wrap"><input type="search" id="customerSearch" class="form-control trx-control" placeholder="Cari nama, nomor customer, atau nomor HP..." autocomplete="off"><span class="customer-search-icon">⌕</span></div>
                        <div style="margin-top:5px"><select name="customer_id" id="customer_id" class="form-select trx-control" required><option value="">Pilih customer...</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-search="{{ strtolower($customer->full_name.' '.$customer->customer_number.' '.$customer->phone) }}" data-number="{{ $customer->customer_number }}" data-phone="{{ $customer->phone }}" data-kyc="{{ $customer->kyc_status }}" data-idtype="{{ $customer->jenis_id }}" data-idnumber="{{ $customer->no_ktp }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->full_name }} — {{ $customer->customer_number }} — {{ $customer->phone ?: 'tanpa HP' }}</option>@endforeach</select></div>
                        <div id="customerMeta" class="customer-meta"></div>
                    </div>
                    <div class="col-lg-2"><label class="trx-label">Tanggal Transaksi</label><input type="date" name="transaction_date" class="form-control trx-control" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required></div>
                    <div class="col-lg-2"><label class="trx-label">Sumber Dana</label><select name="fund_source_type" class="form-select trx-control"><option value="">Pilih sumber dana...</option>@foreach(['Gaji / Penghasilan'=>'gaji','Usaha / Bisnis'=>'usaha','Tabungan'=>'tabungan','Investasi'=>'investasi','Penjualan Aset'=>'penjualan_aset','Warisan / Hibah'=>'warisan_hibah','Pinjaman'=>'pinjaman','Lainnya'=>'lainnya'] as $label=>$value)<option value="{{ $value }}" {{ old('fund_source_type') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-lg-3"><label class="trx-label">Tujuan Transaksi</label><select name="transaction_purpose_type" class="form-select trx-control"><option value="">Pilih tujuan...</option>@foreach(['Perjalanan / Travel'=>'travel','Pendidikan'=>'pendidikan','Bisnis / Usaha'=>'bisnis','Investasi'=>'investasi','Keluarga'=>'keluarga','Kesehatan'=>'kesehatan','Pembayaran'=>'pembayaran','Lainnya'=>'lainnya'] as $label=>$value)<option value="{{ $value }}" {{ old('transaction_purpose_type') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                </div>
                <div id="customerHistory" class="customer-history"><div class="customer-history-head"><span class="customer-history-title">History Transaksi Customer</span><span class="customer-history-note" id="customerHistoryNote"></span></div><div class="customer-history-wrap"><table class="customer-history-table"><thead><tr><th>No Transaksi</th><th>Tanggal</th><th>Arah</th><th>Currency</th><th>Total</th><th>Status</th></tr></thead><tbody id="customerHistoryRows"><tr><td colspan="6" class="empty">Pilih customer untuk melihat history.</td></tr></tbody></table></div></div>
            </div>
        </div>

        <div class="trx-paper">
            <div class="trx-section-head d-flex justify-content-between align-items-center"><span>02 · ITEM TRANSAKSI</span><span style="font-size:9px;color:#7d8983;font-weight:500">Setiap baris punya arah BELI / JUAL sendiri</span></div>
            <div class="trx-section-body">
                <div class="trx-table-wrap"><table class="trx-table"><thead><tr><th>No</th><th>Arah</th><th>Currency</th><th>Series / Variant</th><th>Denomination</th><th>Qty</th><th>Rate</th><th>Subtotal</th><th></th></tr></thead><tbody id="itemRows"></tbody></table></div>
                <button type="button" class="trx-add" id="addItem">+ Tambah Item</button>
            </div>
        </div>

        <div class="trx-paper">
            <div class="trx-section-head">03 · RINGKASAN & SELISIH RP</div>
            <div class="trx-section-body">
                <div class="trx-summary">
                    <div class="trx-summary-box"><div class="trx-summary-label">Total Jual</div><div class="trx-summary-value" id="summarySell">Rp0</div><div class="trx-summary-note">Customer menyerahkan valuta</div></div>
                    <div class="trx-summary-box"><div class="trx-summary-label">Total Beli</div><div class="trx-summary-value" id="summaryBuy">Rp0</div><div class="trx-summary-note">Customer membeli valuta</div></div>
                    <div class="trx-summary-box"><div class="trx-summary-label">Jumlah Item</div><div class="trx-summary-value" id="summaryItems">0</div><div class="trx-summary-note" id="summaryQty">0 qty</div></div>
                    <div class="trx-summary-box"><div class="trx-summary-label">Total Item</div><div class="trx-summary-value" id="summaryTotal">Rp0</div><div class="trx-summary-note">akumulasi semua item</div></div>
                </div>
                <div class="trx-difference"><div><div class="trx-difference-label">Selisih Rupiah</div><div class="trx-difference-note" id="differenceNote">Belum ada item.</div></div><div class="trx-difference-value" id="summaryDifference">Rp0</div></div>
                <input type="hidden" name="calculated_difference" id="calculatedDifference" value="0">
            </div>
        </div>

        <div class="trx-paper">
            <div class="trx-section-head">04 · PEMBAYARAN / SETTLEMENT IDR</div>
            <div class="trx-section-body">
                <div class="payment-grid">
                    <div><div class="trx-label">Kebutuhan IDR</div><div class="payment-total" id="paymentRequired">Rp0</div><div class="payment-help" id="paymentHelp">Jika selisih positif, customer menerima sisa Rp.</div></div>
                    <div>
                        <div class="trx-label">Metode</div>
                        <div class="payment-methods">
                            <button type="button" class="payment-method-btn active" data-method="cash">CASH</button>
                            <button type="button" class="payment-method-btn" data-method="transfer">TRANSFER</button>
                            <button type="button" class="payment-method-btn" data-method="split">SPLIT</button>
                        </div>
                        <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                        <div class="payment-fields mt-2">
                            <div class="payment-field" id="cashField"><label class="trx-label">Cash</label><input type="number" name="cash_amount" id="cashAmount" class="form-control trx-control" min="0" step="0.01" value="0"></div>
                            <div class="payment-field" id="transferField"><label class="trx-label">Transfer</label><input type="number" name="transfer_amount" id="transferAmount" class="form-control trx-control" min="0" step="0.01" value="0"></div>
                            <div class="payment-field" id="bankField"><label class="trx-label">Rekening Tujuan</label><select name="bank_account_id" id="bankAccount" class="form-select trx-control"><option value="">Pilih rekening...</option>@foreach($bankAccounts as $bank)<option value="{{ $bank->id }}">{{ $bank->bank_name }} · {{ $bank->account_number }}{{ $bank->account_name ? ' · '.$bank->account_name : '' }}</option>@endforeach</select></div>
                        </div>
                        <div class="payment-fields mt-2">
                            <div class="payment-field" id="referenceField"><label class="trx-label">Referensi Transfer</label><input type="text" name="transfer_reference" class="form-control trx-control" maxlength="150"></div>
                            <div class="payment-field" id="payerField"><label class="trx-label">Nama Pengirim</label><input type="text" name="payer_name" class="form-control trx-control" maxlength="150"></div>
                            <div class="payment-field" id="externalField"><label class="trx-label">ID Transfer</label><input type="text" name="transfer_external_id" class="form-control trx-control" maxlength="150"></div>
                        </div>
                        <div class="payment-error" id="paymentError"></div>
                    </div>
                </div>
                <div style="margin-top:8px"><label class="trx-label">Catatan Pembayaran</label><textarea name="payment_notes" class="form-control" rows="2" style="font-size:11px;resize:vertical" placeholder="Catatan cash/transfer..."></textarea></div>
            </div>
        </div>

        <div class="trx-paper"><div class="trx-section-head">05 · CATATAN TRANSAKSI</div><div class="trx-section-body"><textarea name="notes" class="form-control" rows="2" style="font-size:11px;resize:vertical" placeholder="Catatan transaksi...">{{ old('notes') }}</textarea></div></div>
        <div class="trx-actions"><a href="{{ route('teller.index') }}" class="btn btn-light trx-btn">Batal</a><button type="submit" class="btn btn-dark trx-btn">Simpan Transaksi</button></div>
    </form>
</div>

<script>
const currencies=@json($currencies),rates=@json($rates),oldItems=@json(old('items',[])),customerHistoryUrl=@json(route('transactions.customer-history',['customer'=>'CUSTOMER_ID']));
let itemIndex=0,currentDirection='buy';
function money(v){return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:2}).format(Number(v)||0)}
function esc(v){return String(v??'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]))}
function findCurrency(id){return currencies.find(c=>String(c.id)===String(id))}
function findVariant(cid,id){const c=findCurrency(cid);return c?.variants?.find(v=>String(v.id)===String(id))}
function getRates(cid,vid,did){return rates.filter(r=>String(r.currency_id)===String(cid)&&String(r.currency_variant_id)===String(vid)&&(r.currency_denomination_id===null||String(r.currency_denomination_id)===String(did||'')))}
function selectRate(cid,vid,did,direction){const list=getRates(cid,vid,did),exact=list.find(r=>r.currency_denomination_id!==null&&String(r.currency_denomination_id)===String(did)),generic=list.find(r=>r.currency_denomination_id===null),r=exact||generic;return r?{id:r.id,value:direction==='buy'?r.buy_rate:r.sell_rate}:null}
function currencyOptions(selected=''){return '<option value="">Pilih...</option>'+currencies.map(c=>`<option value="${c.id}" ${String(c.id)===String(selected)?'selected':''}>${esc(c.code)} — ${esc(c.name)}</option>`).join('')}
function variantOptions(cid,selected=''){const c=findCurrency(cid);return '<option value="">Pilih...</option>'+(c?.variants||[]).map(v=>`<option value="${v.id}" ${String(v.id)===String(selected)?'selected':''}>${esc(v.name)}${v.code?' · '+esc(v.code):''}</option>`).join('')}
function denominationOptions(cid,vid,selected=''){const v=findVariant(cid,vid);return '<option value="">Semua / tidak spesifik</option>'+(v?.denominations||[]).map(d=>`<option value="${d.id}" ${String(d.id)===String(selected)?'selected':''}>${Number(d.value).toLocaleString('id-ID')} · ${esc(d.type)}</option>`).join('')}
function addRow(data={}){const i=itemIndex++,direction=data.direction||currentDirection,tr=document.createElement('tr');tr.dataset.index=i;tr.innerHTML=`<td class="row-no text-center">${i+1}</td><td><input type="hidden" name="items[${i}][direction]" class="direction-input" value="${esc(direction)}"><div class="row-direction"><button type="button" class="row-direction-btn ${direction==='buy'?'active':''}" data-row-direction="buy">BELI</button><button type="button" class="row-direction-btn ${direction==='sell'?'active':''}" data-row-direction="sell">JUAL</button></div></td><td class="currency-cell"><select name="items[${i}][currency_id]" class="currency-select" required>${currencyOptions(data.currency_id)}</select></td><td class="variant-cell"><select name="items[${i}][currency_variant_id]" class="variant-select" required>${variantOptions(data.currency_id,data.currency_variant_id)}</select></td><td class="denom-cell"><select name="items[${i}][currency_denomination_id]" class="denom-select">${denominationOptions(data.currency_id,data.currency_variant_id,data.currency_denomination_id)}</select></td><td class="qty-cell"><input type="number" name="items[${i}][quantity]" class="qty-input" min="0.0001" step="0.0001" value="${esc(data.quantity||'')}" required></td><td class="rate-cell"><input type="number" name="items[${i}][rate]" class="rate-input" step="0.00000001" min="0.00000001" value="${esc(data.rate||'')}" required><input type="hidden" name="items[${i}][rate_snapshot_id]" class="snapshot-input" value="${esc(data.rate_snapshot_id||'')}"></td><td class="amount-cell subtotal">Rp0</td><td><button type="button" class="remove-row" title="Hapus">×</button></td>`;document.getElementById('itemRows').appendChild(tr);bindRow(tr);refreshVariant(tr,data.currency_variant_id);refreshDenomination(tr,data.currency_denomination_id);refreshRate(tr);if(data.rate)tr.querySelector('.rate-input').value=data.rate;calculate()}
function refreshVariant(tr,selected=''){const cid=tr.querySelector('.currency-select').value;tr.querySelector('.variant-select').innerHTML=variantOptions(cid,selected);refreshDenomination(tr)}
function refreshDenomination(tr,selected=''){const cid=tr.querySelector('.currency-select').value,vid=tr.querySelector('.variant-select').value;tr.querySelector('.denom-select').innerHTML=denominationOptions(cid,vid,selected);refreshRate(tr)}
function refreshRate(tr){const cid=tr.querySelector('.currency-select').value,vid=tr.querySelector('.variant-select').value,did=tr.querySelector('.denom-select').value,direction=tr.querySelector('.direction-input').value,r=selectRate(cid,vid,did,direction),rate=tr.querySelector('.rate-input'),snap=tr.querySelector('.snapshot-input');if(r){rate.value=r.value;snap.value=r.id}else{rate.value='';snap.value=''}calculate()}
function setRowDirection(tr,direction){tr.querySelector('.direction-input').value=direction;tr.querySelectorAll('.row-direction-btn').forEach(b=>b.classList.toggle('active',b.dataset.rowDirection===direction));refreshRate(tr)}
function bindRow(tr){tr.querySelector('.currency-select').addEventListener('change',()=>refreshVariant(tr));tr.querySelector('.variant-select').addEventListener('change',()=>refreshDenomination(tr));tr.querySelector('.denom-select').addEventListener('change',()=>refreshRate(tr));tr.querySelector('.qty-input').addEventListener('input',calculate);tr.querySelector('.rate-input').addEventListener('input',()=>{tr.querySelector('.snapshot-input').value='';calculate()});tr.querySelectorAll('.row-direction-btn').forEach(b=>b.addEventListener('click',()=>setRowDirection(tr,b.dataset.rowDirection)));tr.querySelector('.remove-row').addEventListener('click',()=>{tr.remove();renumber();calculate()})}
function renumber(){document.querySelectorAll('#itemRows tr').forEach((tr,n)=>tr.querySelector('.row-no').textContent=n+1)}
function calculate(){let sell=0,buy=0,qty=0,count=0,total=0;document.querySelectorAll('#itemRows tr').forEach(tr=>{const q=Number(tr.querySelector('.qty-input').value)||0,r=Number(tr.querySelector('.rate-input').value)||0,s=q*r,d=tr.querySelector('.direction-input').value;tr.querySelector('.subtotal').textContent=money(s);qty+=q;total+=s;count++;if(d==='sell')sell+=s;else buy+=s});const diff=sell-buy;document.getElementById('summarySell').textContent=money(sell);document.getElementById('summaryBuy').textContent=money(buy);document.getElementById('summaryItems').textContent=count;document.getElementById('summaryQty').textContent=new Intl.NumberFormat('id-ID',{maximumFractionDigits:4}).format(qty)+' qty';document.getElementById('summaryTotal').textContent=money(total);document.getElementById('summaryDifference').textContent=money(Math.abs(diff));document.getElementById('calculatedDifference').value=diff.toFixed(2);document.getElementById('paymentRequired').textContent=money(Math.max(0,-diff));document.getElementById('differenceNote').textContent=diff>0?'Sisa Rp dibayarkan kepada customer.':diff<0?'Customer wajib membayar tambahan Rp.':'Nilai BELI dan JUAL seimbang.';document.getElementById('paymentHelp').textContent=diff>0?'Tidak ada pembayaran customer. Sisa Rp menjadi pencairan/settlement customer.':'Pilih CASH, TRANSFER, atau SPLIT untuk pembayaran customer.';updatePaymentFields(diff)}
function updatePaymentFields(diff){const required=Math.max(0,-diff),method=document.getElementById('paymentMethod').value;document.getElementById('cashAmount').value=method==='cash'?required:method==='split'?document.getElementById('cashAmount').value:0;document.getElementById('transferAmount').value=method==='transfer'?required:method==='split'?document.getElementById('transferAmount').value:0;document.querySelectorAll('.payment-field').forEach(x=>x.classList.remove('show'));if(required<=0)return;if(method==='cash'){document.getElementById('cashField').classList.add('show')}else if(method==='transfer'){document.getElementById('transferField').classList.add('show');document.getElementById('bankField').classList.add('show');document.getElementById('referenceField').classList.add('show');document.getElementById('payerField').classList.add('show');document.getElementById('externalField').classList.add('show')}else{document.getElementById('cashField').classList.add('show');document.getElementById('transferField').classList.add('show');document.getElementById('bankField').classList.add('show');document.getElementById('referenceField').classList.add('show');document.getElementById('payerField').classList.add('show');document.getElementById('externalField').classList.add('show')}}
function validatePayment(){const diff=Number(document.getElementById('calculatedDifference').value)||0,required=Math.max(0,-diff),method=document.getElementById('paymentMethod').value,cash=Number(document.getElementById('cashAmount').value)||0,transfer=Number(document.getElementById('transferAmount').value)||0,err=document.getElementById('paymentError');err.textContent='';if(required<=0)return true;if(!method){err.textContent='Pilih metode pembayaran.';return false}if(Math.round((cash+transfer)*100)!==Math.round(required*100)){err.textContent='Cash + Transfer harus tepat '+money(required)+'.';return false}if(transfer>0&&!document.getElementById('bankAccount').value){err.textContent='Pilih rekening tujuan untuk transfer.';return false}return true}
function loadCustomerHistory(id){const box=document.getElementById('customerHistory'),rows=document.getElementById('customerHistoryRows'),note=document.getElementById('customerHistoryNote');if(!id){box.style.display='none';return}box.style.display='block';rows.innerHTML='<tr><td colspan="6" class="empty">Memuat history...</td></tr>';note.textContent='';fetch(customerHistoryUrl.replace('CUSTOMER_ID',encodeURIComponent(id)),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>{if(!r.ok)throw new Error();return r.json()}).then(data=>{note.textContent=`${data.history.length} transaksi terakhir`;if(!data.history.length){rows.innerHTML='<tr><td colspan="6" class="empty">Belum ada transaksi untuk customer ini.</td></tr>';return}rows.innerHTML=data.history.map(x=>`<tr><td><strong>${esc(x.transaction_no)}</strong></td><td>${esc(x.date||'-')}</td><td>${esc(x.direction||'-')}</td><td>${esc(x.currency||'-')}</td><td class="amount">${money(x.total)}</td><td>${esc(x.status||'-')}</td></tr>`).join('')}).catch(()=>{rows.innerHTML='<tr><td colspan="6" class="empty">History belum dapat dimuat.</td></tr>'})}
document.querySelectorAll('.direction-btn').forEach(btn=>btn.addEventListener('click',()=>{currentDirection=btn.dataset.direction;document.querySelectorAll('.direction-btn').forEach(b=>b.classList.toggle('active',b===btn))}));
document.querySelectorAll('.payment-method-btn').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.toggle('active',b===btn));document.getElementById('paymentMethod').value=btn.dataset.method;calculate()}));
document.getElementById('cashAmount').addEventListener('input',()=>{document.getElementById('paymentMethod').value='split';document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.toggle('active',b.dataset.method==='split'));document.getElementById('paymentError').textContent='';});
document.getElementById('transferAmount').addEventListener('input',()=>{document.getElementById('paymentMethod').value='split';document.querySelectorAll('.payment-method-btn').forEach(b=>b.classList.toggle('active',b.dataset.method==='split'));document.getElementById('paymentError').textContent='';});
document.getElementById('addItem').addEventListener('click',()=>addRow());document.getElementById('customer_id').addEventListener('change',function(){const o=this.selectedOptions[0];document.getElementById('customerMeta').innerHTML=o?.value?`No: <strong>${esc(o.dataset.number)}</strong> · KYC: <strong>${esc(o.dataset.kyc||'-')}</strong> · ID: <strong>${esc(o.dataset.idtype||'-')} ${esc(o.dataset.idnumber||'')}</strong> · HP: <strong>${esc(o.dataset.phone||'-')}</strong>`:'';loadCustomerHistory(this.value)});document.getElementById('customerSearch').addEventListener('input',function(){const q=this.value.trim().toLowerCase(),select=document.getElementById('customer_id');Array.from(select.options).forEach((option,index)=>{if(index===0)return;option.hidden=!!q&&!String(option.dataset.search||'').includes(q)});const current=select.selectedOptions[0];if(q&&current?.hidden){select.value='';select.dispatchEvent(new Event('change'))}});document.getElementById('transactionForm').addEventListener('submit',e=>{if(!validatePayment())e.preventDefault()});
if(oldItems.length)oldItems.forEach(x=>addRow(x));else addRow();document.getElementById('customer_id').dispatchEvent(new Event('change'));calculate();
</script>
@endsection

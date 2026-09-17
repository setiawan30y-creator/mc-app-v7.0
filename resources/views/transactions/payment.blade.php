@extends('layouts.app')

@section('content')
<div class="trx-page payment-page">
    <div class="trx-header">
        <div>
            <div class="trx-eyebrow">TELLER · PEMBAYARAN</div>
            <h1 class="trx-title">Pembayaran Transaksi</h1>
            <div class="trx-subtitle">{{ $trx->transaction_no }} · {{ $trx->customer?->full_name }}</div>
        </div>
        <a href="{{ route('teller.index') }}" class="btn btn-light trx-btn">← Teller</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="trx-paper">
        <div class="trx-section-head">01 · RINGKASAN TAGIHAN</div>
        <div class="trx-section-body">
            <div class="payment-summary">
                <div><span>Transaksi</span><strong>{{ $trx->transaction_no }}</strong></div>
                <div><span>Customer</span><strong>{{ $trx->customer?->full_name }}</strong></div>
                <div><span>Settlement IDR</span><strong>{{ $paymentDirection === 'customer_pays' ? 'CUSTOMER BAYAR' : ($paymentDirection === 'customer_receives' ? 'CUSTOMER TERIMA' : 'TANPA SELISIH IDR') }}</strong></div>
                <div><span>Kebutuhan IDR</span><strong>{{ number_format($requiredAmount, 2, ',', '.') }}</strong></div>
                <div><span>Sudah Dibayar</span><strong>{{ number_format($paidAmount, 2, ',', '.') }}</strong></div>
                <div class="payment-total"><span>Sisa</span><strong>Rp {{ number_format($remainingAmount, 2, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('transactions.payments.store', $trx->id) }}" id="paymentForm">
        @csrf
        <div class="trx-paper">
            <div class="trx-section-head">02 · METODE PEMBAYARAN</div>
            <div class="trx-section-body">
                <div class="payment-methods">
                    <label class="payment-method active" data-method="cash">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <span><b>CASH</b><small>Pembayaran tunai</small></span>
                    </label>
                    <label class="payment-method" data-method="transfer">
                        <input type="radio" name="payment_method" value="transfer">
                        <span><b>TRANSFER</b><small>Melalui rekening bank</small></span>
                    </label>
                    <label class="payment-method" data-method="split">
                        <input type="radio" name="payment_method" value="split">
                        <span><b>SPLIT</b><small>Cash + Transfer</small></span>
                    </label>
                </div>

                <div class="payment-fields">
                    <div class="payment-field cash-field">
                        <label class="trx-label">Nominal Cash</label>
                        <input type="number" name="cash_amount" id="cashAmount" class="form-control trx-control" min="0" step="0.01" value="{{ $remainingAmount }}">
                    </div>
                    <div class="payment-field transfer-field" style="display:none">
                        <label class="trx-label">Nominal Transfer</label>
                        <input type="number" name="transfer_amount" id="transferAmount" class="form-control trx-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="payment-field transfer-field" style="display:none">
                        <label class="trx-label">Rekening Tujuan</label>
                        <select name="bank_account_id" class="form-select trx-control">
                            <option value="">Pilih rekening...</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} · {{ $bank->account_number }} · {{ $bank->account_name }} · {{ $bank->currency?->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="payment-field transfer-field" style="display:none">
                        <label class="trx-label">Referensi Transfer</label>
                        <input name="transfer_reference" class="form-control trx-control" maxlength="150" placeholder="No. referensi / berita transfer">
                    </div>
                    <div class="payment-field transfer-field" style="display:none">
                        <label class="trx-label">Nama Pengirim</label>
                        <input name="payer_name" class="form-control trx-control" maxlength="150" placeholder="Nama sesuai rekening">
                    </div>
                </div>

                <div class="split-info" id="splitInfo" style="display:none">
                    Total Cash + Transfer harus tepat: <strong>Rp {{ number_format($remainingAmount, 2, ',', '.') }}</strong>
                    <span id="paymentBalance"></span>
                </div>
            </div>
        </div>

        <div class="trx-paper">
            <div class="trx-section-head">03 · KONFIRMASI</div>
            <div class="trx-section-body">
                <label class="trx-label">Catatan Pembayaran</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="trx-actions">
            <a href="{{ route('teller.index') }}" class="btn btn-light trx-btn">Batal</a>
            <button type="submit" class="btn btn-dark trx-btn" {{ $remainingAmount <= 0 ? 'disabled' : '' }}>Konfirmasi Pembayaran →</button>
        </div>
    </form>
</div>

<style>
.payment-summary{display:grid;grid-template-columns:repeat(6,1fr);gap:8px}
.payment-summary>div{border:1px solid #dfe7e2;padding:9px 10px;background:#fafcfb}
.payment-summary span{display:block;font-size:9px;color:#78837e;text-transform:uppercase;letter-spacing:.04em}
.payment-summary strong{display:block;margin-top:3px;font-size:12px}
.payment-summary .payment-total{background:#f3f7f5}
.payment-methods{display:flex;gap:8px;margin-bottom:12px}
.payment-method{flex:1;border:1px solid #dfe7e2;padding:10px;cursor:pointer;background:#fff}
.payment-method.active{border-color:#8d9b94;background:#f5f8f6}
.payment-method input{margin-right:7px}
.payment-method b{font-size:12px}.payment-method small{display:block;color:#7b8681;font-size:9px;margin-left:21px;margin-top:2px}
.payment-fields{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.payment-field .trx-label{margin-bottom:3px}
.split-info{margin-top:10px;padding:8px 10px;border:1px dashed #cbd8d1;background:#fafcfb;font-size:11px}
#paymentBalance{margin-left:10px;font-weight:700}
@media(max-width:900px){.payment-summary{grid-template-columns:repeat(2,1fr)}.payment-methods{flex-wrap:wrap}.payment-method{min-width:150px}.payment-fields{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.payment-summary{grid-template-columns:1fr}.payment-fields{grid-template-columns:1fr}}
</style>

<script>
const requiredAmount = {{ $remainingAmount }};
const methods = document.querySelectorAll('.payment-method');
const cashFields = document.querySelectorAll('.cash-field');
const transferFields = document.querySelectorAll('.transfer-field');
const cashInput = document.getElementById('cashAmount');
const transferInput = document.getElementById('transferAmount');
const splitInfo = document.getElementById('splitInfo');
const balance = document.getElementById('paymentBalance');
function syncPayment(){
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
    methods.forEach(x=>x.classList.toggle('active',x.dataset.method===method));
    cashFields.forEach(x=>x.style.display=method==='transfer'?'none':'block');
    transferFields.forEach(x=>x.style.display=method==='cash'?'none':'block');
    if(method==='cash'){cashInput.value=requiredAmount;transferInput.value=0;}
    if(method==='transfer'){cashInput.value=0;transferInput.value=requiredAmount;}
    if(method==='split') splitInfo.style.display='block'; else splitInfo.style.display='none';
    updateBalance();
}
function updateBalance(){
    const method=document.querySelector('input[name="payment_method"]:checked')?.value;
    if(method!=='split') return;
    const total=(Number(cashInput.value)||0)+(Number(transferInput.value)||0), diff=Math.round((total-requiredAmount)*100)/100;
    balance.textContent=diff===0?'✓ PAS':(diff>0?' Kelebihan Rp '+new Intl.NumberFormat('id-ID').format(diff):' Kurang Rp '+new Intl.NumberFormat('id-ID').format(Math.abs(diff)));
}
methods.forEach(x=>x.addEventListener('click',()=>{x.querySelector('input').checked=true;syncPayment();}));
cashInput.addEventListener('input',updateBalance);transferInput.addEventListener('input',updateBalance);syncPayment();
</script>
@endsection

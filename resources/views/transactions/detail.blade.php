@extends('layouts.app')

@section('content')
<style>
.detail-page{padding:16px 20px 28px;background:#f7f9f8;min-height:calc(100vh - 70px)}
.detail-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:12px}
.detail-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;color:#718078;text-transform:uppercase}
.detail-title{font-size:21px;font-weight:700;margin:2px 0}.detail-subtitle{font-size:12px;color:#77827d}
.detail-actions{display:flex;gap:7px}.detail-btn{font-size:11px;padding:7px 10px}
.detail-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:10px}.detail-card{background:#fff;border:1px solid #dfe7e2;border-radius:7px;overflow:hidden;margin-bottom:10px}
.detail-card h2{font-size:12px;margin:0;padding:10px 12px;background:#f4f7f5;border-bottom:1px solid #dfe7e2}.detail-body{padding:11px 12px}
.meta{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}.label{font-size:9px;color:#7b8781;text-transform:uppercase;letter-spacing:.05em}.value{font-size:12px;font-weight:600;color:#34423a;margin-top:2px}
.badge{display:inline-flex;padding:3px 7px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase;background:#eef2ef;color:#56635c}.buy{background:#edf7ef;color:#28733b}.sell{background:#fdf0ef;color:#a43e35}.paid{background:#edf7ef;color:#28733b}.pending{background:#fff6e6;color:#916b1d}
.table{width:100%;border-collapse:collapse;font-size:11px}.table th{font-size:9px;text-transform:uppercase;color:#65716b;background:#f8faf9;text-align:left;padding:7px;border-bottom:1px solid #e3e9e5}.table td{padding:8px 7px;border-bottom:1px solid #edf1ef;vertical-align:top}.right{text-align:right;font-variant-numeric:tabular-nums}.muted{font-size:9px;color:#89938e;margin-top:2px}
.erp-ok{display:flex;align-items:center;gap:8px;padding:8px 9px;border:1px solid #dfe7e2;border-radius:5px;margin-bottom:6px;font-size:11px}.erp-ok strong{color:#28733b}.erp-empty{font-size:11px;color:#89938e;padding:4px 0}
@media(max-width:900px){.detail-grid{grid-template-columns:1fr}.meta{grid-template-columns:1fr}}
</style>

<div class="detail-page">
    <div class="detail-head">
        <div>
            <div class="detail-eyebrow">TRANSACTION · DETAIL</div>
            <h1 class="detail-title">{{ $trx->transaction_no }}</h1>
            <div class="detail-subtitle">{{ optional($trx->transaction_date)->format('d/m/Y H:i') }} · {{ $trx->customer?->full_name ?? 'Tanpa nasabah' }}</div>
        </div>
        <div class="detail-actions">
            <a href="{{ route('transactions.history') }}" class="btn btn-light detail-btn">← Riwayat</a>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary detail-btn">+ Transaksi Baru</a>
        </div>
    </div>

    <div class="detail-grid">
        <div>
            <div class="detail-card">
                <h2>Informasi Transaksi</h2>
                <div class="detail-body">
                    <div class="meta">
                        <div><div class="label">Status</div><div class="value"><span class="badge {{ $trx->status === 'paid' ? 'paid' : 'pending' }}">{{ strtoupper((string)$trx->status) }}</span></div></div>
                        <div><div class="label">Settlement</div><div class="value">{{ strtoupper((string)$trx->settlement_status) }}</div></div>
                        <div><div class="label">Nasabah</div><div class="value">{{ $trx->customer?->full_name ?? '-' }}</div><div class="muted">{{ $trx->customer?->customer_number ?? '' }} {{ $trx->customer?->phone ? '· '.$trx->customer->phone : '' }}</div></div>
                        <div><div class="label">Dibuat oleh</div><div class="value">{{ $trx->createdBy?->name ?? '-' }}</div></div>
                        <div><div class="label">Sumber Dana</div><div class="value">{{ $trx->source_of_funds ?: '-' }}</div></div>
                        <div><div class="label">Tujuan Transaksi</div><div class="value">{{ $trx->transaction_purpose ?: '-' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2>Item Valas</h2>
                <div class="detail-body" style="padding:0">
                    <table class="table"><thead><tr><th>Valas</th><th>Arah</th><th class="right">Qty</th><th class="right">Rate</th><th class="right">Subtotal</th></tr></thead><tbody>
                    @forelse($trx->items as $item)
                        <tr>
                            <td><strong>{{ $item->currency?->code ?? '-' }}</strong><div class="muted">{{ $item->currencyVariant?->name ?? '' }} {{ $item->currencyDenomination?->value ? '· '.$item->currencyDenomination->value : '' }}</div></td>
                            <td><span class="badge {{ $item->direction === 'buy' ? 'buy' : 'sell' }}">{{ strtoupper($item->direction) }}</span></td>
                            <td class="right">{{ number_format((float)$item->quantity, 4, ',', '.') }}</td>
                            <td class="right">{{ number_format((float)$item->rate, 4, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format((float)$item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="erp-empty">Tidak ada item.</td></tr>
                    @endforelse
                    <tr><td colspan="4" class="right"><strong>Total</strong></td><td class="right"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td></tr>
                    </tbody></table>
                </div>
            </div>

            <div class="detail-card">
                <h2>Payment</h2>
                <div class="detail-body" style="padding:0"><table class="table"><thead><tr><th>Metode</th><th>Rekening / Referensi</th><th>Status</th><th class="right">Nominal</th></tr></thead><tbody>
                @forelse($trx->payments as $payment)
                    <tr><td><strong>{{ strtoupper($payment->payment_method) }}</strong></td><td>{{ $payment->bankAccount?->bank_name ?? '-' }}<div class="muted">{{ $payment->transfer_reference ?: '-' }}</div></td><td><span class="badge {{ $payment->payment_status === 'paid' ? 'paid' : 'pending' }}">{{ strtoupper($payment->payment_status) }}</span></td><td class="right">Rp {{ number_format((float)$payment->amount, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="erp-empty">Belum ada payment.</td></tr>
                @endforelse
                <tr><td colspan="3" class="right"><strong>Total Paid</strong></td><td class="right"><strong>Rp {{ number_format($paid, 0, ',', '.') }}</strong></td></tr>
                </tbody></table></div>
            </div>
        </div>

        <div>
            <div class="detail-card">
                <h2>ERP Posting</h2>
                <div class="detail-body">
                    <div class="erp-ok"><span>✓</span><strong>Payment</strong><span class="muted">{{ $trx->payments->where('payment_status','!=','failed')->count() }} posting</span></div>
                    <div class="erp-ok"><span>✓</span><strong>Cash</strong><span class="muted">{{ $trx->cashMovements->count() }} movement</span></div>
                    <div class="erp-ok"><span>✓</span><strong>Bank</strong><span class="muted">{{ $bankMutations->count() }} mutation</span></div>
                    <div class="erp-ok"><span>✓</span><strong>Stok Valas</strong><span class="muted">{{ $inventoryMovements->count() }} movement</span></div>
                </div>
            </div>

            <div class="detail-card">
                <h2>Cash Movement</h2>
                <div class="detail-body" style="padding:0"><table class="table"><thead><tr><th>Arah</th><th>Valuta</th><th class="right">Qty</th><th class="right">Nominal</th></tr></thead><tbody>
                @forelse($trx->cashMovements as $movement)
                    <tr><td>{{ strtoupper($movement->direction) }}</td><td>{{ $movement->currency?->code ?? '-' }}</td><td class="right">{{ number_format((float)$movement->quantity,4,',','.') }}</td><td class="right">Rp {{ number_format((float)$movement->amount,0,',','.') }}</td></tr>
                @empty<tr><td colspan="4" class="erp-empty">Tidak ada cash movement.</td></tr>@endforelse
                </tbody></table></div>
            </div>

            <div class="detail-card">
                <h2>Mutasi Bank</h2>
                <div class="detail-body" style="padding:0"><table class="table"><thead><tr><th>Bank</th><th>Referensi</th><th class="right">Debit</th><th class="right">Credit</th></tr></thead><tbody>
                @forelse($bankMutations as $mutation)
                    <tr><td>{{ $mutation->bankAccount?->bank_name ?? '-' }}</td><td>{{ $mutation->reference ?: '-' }}<div class="muted">{{ $mutation->reconciliation_status }}</div></td><td class="right">Rp {{ number_format((float)$mutation->debit,0,',','.') }}</td><td class="right">Rp {{ number_format((float)$mutation->credit,0,',','.') }}</td></tr>
                @empty<tr><td colspan="4" class="erp-empty">Tidak ada mutasi bank yang terhubung.</td></tr>@endforelse
                </tbody></table></div>
            </div>

            <div class="detail-card">
                <h2>Stok Valas</h2>
                <div class="detail-body" style="padding:0"><table class="table"><thead><tr><th>Valuta</th><th>Arah</th><th class="right">Qty</th><th class="right">Saldo Qty</th></tr></thead><tbody>
                @forelse($inventoryMovements as $movement)
                    <tr><td><strong>{{ $movement->inventory?->currency?->code ?? '-' }}</strong><div class="muted">{{ $movement->inventory?->currencyVariant?->name ?? '' }}</div></td><td>{{ strtoupper($movement->direction) }}</td><td class="right">{{ number_format((float)$movement->quantity,4,',','.') }}</td><td class="right">{{ number_format((float)$movement->balance_quantity,4,',','.') }}</td></tr>
                @empty<tr><td colspan="4" class="erp-empty">Tidak ada stock movement yang terhubung.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
@endsection

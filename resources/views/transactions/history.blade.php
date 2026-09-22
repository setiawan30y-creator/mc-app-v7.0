@extends('layouts.app')

@section('content')
<style>
    .history-page{padding:16px 20px 28px;min-height:calc(100vh - 70px);background:#f7f9f8}
    .history-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:12px}
    .history-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#718078}
    .history-title{margin:2px 0 0;font-size:21px;font-weight:700;line-height:1.2}
    .history-subtitle{margin-top:4px;font-size:12px;color:#77827d}
    .history-actions{display:flex;gap:7px}
    .history-btn{font-size:11px;padding:7px 10px;border-radius:6px;text-decoration:none}
    .history-card{background:#fff;border:1px solid #dfe7e2;border-radius:7px;overflow:hidden;margin-bottom:10px}
    .history-filter{padding:11px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:8px;align-items:end}
    .history-label{font-size:10px;font-weight:600;color:#65716b;margin-bottom:3px}
    .history-control{font-size:12px!important;min-height:32px!important;height:32px!important;padding:4px 8px!important;border-radius:5px!important;border-color:#d4ddd8!important;width:100%}
    .history-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:10px}
    .history-stat{background:#fff;border:1px solid #dfe7e2;border-radius:7px;padding:10px 12px}
    .history-stat-label{font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:#7b8781}
    .history-stat-value{font-size:19px;font-weight:700;color:#34423a;margin-top:2px}
    .history-table-wrap{overflow:auto}
    .history-table{width:100%;border-collapse:collapse;font-size:11px;min-width:1050px}
    .history-table th{background:#f4f7f5;color:#59665f;font-size:9px;text-transform:uppercase;letter-spacing:.04em;text-align:left;padding:8px 9px;border-bottom:1px solid #dfe7e2;white-space:nowrap}
    .history-table td{padding:9px;border-bottom:1px solid #edf1ef;color:#45514b;vertical-align:top}
    .history-table tr:hover td{background:#fafcfb}
    .trx-no{font-weight:700;color:#34423a}
    .muted{font-size:9px;color:#89938e;margin-top:2px}
    .badge{display:inline-flex;padding:3px 6px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;background:#eef2ef;color:#56635c}
    .badge.buy{background:#edf7ef;color:#28733b}.badge.sell{background:#fdf0ef;color:#a43e35}.badge.paid{background:#edf7ef;color:#28733b}.badge.pending{background:#fff6e6;color:#916b1d}
    .items-line{display:flex;flex-wrap:wrap;gap:3px 7px}
    .item-chip{font-size:9px;background:#f3f6f4;border:1px solid #e0e7e3;border-radius:4px;padding:3px 5px}
    .money{text-align:right;font-variant-numeric:tabular-nums;font-weight:600}
    .pagination{padding:10px 11px;display:flex;justify-content:space-between;align-items:center;font-size:10px;color:#7a8580}
    .pagination a{color:#46564e;text-decoration:none;margin-left:8px}
    @media(max-width:900px){.history-filter{grid-template-columns:1fr 1fr}.history-summary{grid-template-columns:1fr 1fr}.history-head{flex-direction:column}}
</style>

<div class="history-page">
    <div class="history-head">
        <div>
            <div class="history-eyebrow">TRANSACTION · LEDGER</div>
            <h1 class="history-title">Riwayat Transaksi</h1>
            <div class="history-subtitle">Riwayat BUY, SELL, pembayaran, dan status transaksi dalam periode aktif.</div>
        </div>
        <div class="history-actions">
            <a href="{{ route('transactions.create') }}" class="btn btn-primary history-btn">+ Transaksi Baru</a>
        </div>
    </div>

    <div class="history-summary">
        <div class="history-stat"><div class="history-stat-label">Total Transaksi</div><div class="history-stat-value">{{ number_format($summary['count']) }}</div></div>
        <div class="history-stat"><div class="history-stat-label">Pembelian</div><div class="history-stat-value">{{ number_format($summary['buy']) }}</div></div>
        <div class="history-stat"><div class="history-stat-label">Penjualan</div><div class="history-stat-value">{{ number_format($summary['sell']) }}</div></div>
        <div class="history-stat"><div class="history-stat-label">Paid</div><div class="history-stat-value">{{ number_format($summary['paid']) }}</div></div>
    </div>

    <div class="history-card">
        <form method="GET" class="history-filter">
            <div><div class="history-label">Cari</div><input class="form-control history-control" name="q" value="{{ request('q') }}" placeholder="No transaksi / nama / no nasabah"></div>
            <div><div class="history-label">Dari</div><input type="date" class="form-control history-control" name="date_from" value="{{ request('date_from') }}"></div>
            <div><div class="history-label">Sampai</div><input type="date" class="form-control history-control" name="date_to" value="{{ request('date_to') }}"></div>
            <div><div class="history-label">Arah</div><select class="form-select history-control" name="direction"><option value="">Semua</option><option value="buy" @selected(request('direction') === 'buy')>BUY</option><option value="sell" @selected(request('direction') === 'sell')>SELL</option></select></div>
            <div><div class="history-label">Pembayaran</div><select class="form-select history-control" name="payment_method"><option value="">Semua</option><option value="cash" @selected(request('payment_method') === 'cash')>Cash</option><option value="transfer" @selected(request('payment_method') === 'transfer')>Transfer</option></select></div>
            <div><button class="btn btn-primary history-btn" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="history-card">
        <div class="history-table-wrap">
            <table class="history-table">
                <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Nasabah</th><th>Item Valas</th><th>Arah</th><th>Pembayaran</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $directions = $transaction->items->pluck('direction')->filter()->unique()->values();
                        $total = $transaction->items->sum(fn($item) => (float)$item->quantity * (float)$item->rate);
                        $payments = $transaction->payments->where('payment_status', '!=', 'failed');
                    @endphp
                    <tr>
                        <td>{{ optional($transaction->transaction_date)->format('d/m/Y') }}<div class="muted">{{ optional($transaction->transaction_date)->format('H:i') }}</div></td>
                        <td><div class="trx-no">{{ $transaction->transaction_no }}</div><div class="muted">{{ strtoupper((string)$transaction->settlement_status) }}</div></td>
                        <td><strong>{{ $transaction->customer?->full_name ?? '-' }}</strong><div class="muted">{{ $transaction->customer?->customer_number ?? '' }}</div></td>
                        <td><div class="items-line">@foreach($transaction->items as $item)<span class="item-chip">{{ $item->currency?->code ?? '-' }} {{ number_format((float)$item->quantity, 2, ',', '.') }} × {{ number_format((float)$item->rate, 2, ',', '.') }}</span>@endforeach</div></td>
                        <td>@foreach($directions as $direction)<span class="badge {{ $direction === 'buy' ? 'buy' : 'sell' }}">{{ strtoupper($direction) }}</span> @endforeach</td>
                        <td>@forelse($payments as $payment)<div>{{ strtoupper($payment->payment_method) }} <strong>Rp {{ number_format((float)$payment->amount, 0, ',', '.') }}</strong></div>@empty<span class="muted">Belum ada payment</span>@endforelse</td>
                        <td><span class="badge {{ $transaction->status === 'paid' ? 'paid' : 'pending' }}">{{ strtoupper((string)$transaction->status) }}</span></td>
                        <td class="money">Rp {{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:28px;color:#8a948f">Belum ada transaksi sesuai filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="pagination"><span>Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }}</span><span>{!! $transactions->links() !!}</span></div>
        @endif
    </div>
</div>
@endsection

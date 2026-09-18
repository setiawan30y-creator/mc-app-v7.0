@extends('layouts.app')

@section('title', 'Stok Valas Hari Ini · MC Almara')
@section('page-title', 'Stok Valas Hari Ini')

@section('content')
<div class="fx-stock-page">
    <header class="fx-header">
        <div>
            <div class="fx-eyebrow">TREASURY / INVENTORY</div>
            <div class="fx-heading-row">
                <div>
                    <h1>Stok Valas Hari Ini</h1>
                    <p>Ringkasan stok per currency dan pecahan: stok awal, pembelian, penjualan, dan saldo akhir.</p>
                </div>
                <span class="fx-status"><i></i> Otomatis dari transaksi</span>
            </div>
        </div>
        <form method="GET" class="fx-date-form">
            <label for="stock-date">Tanggal</label>
            <input id="stock-date" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
        </form>
    </header>

    <div class="fx-summary">
        <div class="fx-card"><div class="fx-icon">◎</div><div><small>MATA UANG</small><strong>{{ $summary['currencies'] }}</strong><em>currency dalam laporan</em></div></div>
        <div class="fx-card"><div class="fx-icon">▤</div><div><small>PECahan</small><strong>{{ $summary['denominations'] }}</strong><em>posisi pecahan</em></div></div>
        <div class="fx-card"><div class="fx-icon">#</div><div><small>TOTAL QTY AKHIR</small><strong>{{ number_format($summary['units'], 0, ',', '.') }}</strong><em>lembar / keping</em></div></div>
        <div class="fx-card value"><div class="fx-icon">Rp</div><div><small>NILAI SALDO AKHIR</small><strong>Rp {{ number_format($summary['value'], 2, ',', '.') }}</strong><em>nilai persediaan</em></div></div>
    </div>

    <section class="fx-panel">
        <div class="fx-panel-head">
            <div><h2>Rekap Stok Valas</h2><p>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p></div>
            <span class="fx-info">Pembelian & penjualan dari transaksi berstatus paid/completed</span>
        </div>
        <div class="fx-table-wrap">
            <table class="fx-table">
                <thead>
                    <tr>
                        <th rowspan="2">Currency</th>
                        <th rowspan="2">Pecahan</th>
                        <th colspan="3" class="group opening">Stok Awal</th>
                        <th colspan="3" class="group purchase">Pembelian</th>
                        <th colspan="3" class="group sales">Penjualan</th>
                        <th colspan="3" class="group ending">Saldo Akhir</th>
                    </tr>
                    <tr class="subhead">
                        <th>Qty</th><th>Kurs</th><th>Rp</th>
                        <th>Qty</th><th>Kurs</th><th>Rp</th>
                        <th>Qty</th><th>Kurs</th><th>Rp</th>
                        <th>Qty</th><th>Kurs</th><th>Rp</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td><b class="code">{{ $row->currency_code }}</b><span class="muted">{{ $row->currency_name }}</span></td>
                        <td><strong>{{ $row->denomination_label }}</strong><span class="muted">{{ $row->variant_name }}</span></td>
                        <td class="num">{{ number_format($row->opening['qty'], 0, ',', '.') }}</td>
                        <td class="num">{{ $row->opening['rate'] !== null ? number_format($row->opening['rate'], 2, ',', '.') : '—' }}</td>
                        <td class="num">{{ $row->opening['rp'] !== null ? number_format($row->opening['rp'], 2, ',', '.') : '—' }}</td>
                        <td class="num">{{ number_format($row->purchase['qty'], 0, ',', '.') }}</td>
                        <td class="num">{{ $row->purchase['rate'] !== null ? number_format($row->purchase['rate'], 2, ',', '.') : '—' }}</td>
                        <td class="num">{{ number_format($row->purchase['rp'], 2, ',', '.') }}</td>
                        <td class="num">{{ number_format($row->sales['qty'], 0, ',', '.') }}</td>
                        <td class="num">{{ $row->sales['rate'] !== null ? number_format($row->sales['rate'], 2, ',', '.') : '—' }}</td>
                        <td class="num">{{ number_format($row->sales['rp'], 2, ',', '.') }}</td>
                        <td class="num ending-qty"><strong>{{ number_format($row->ending['qty'], 0, ',', '.') }}</strong></td>
                        <td class="num ending-rate"><strong>{{ $row->ending['rate'] !== null ? number_format($row->ending['rate'], 2, ',', '.') : '—' }}</strong></td>
                        <td class="num ending-rp"><strong>{{ $row->ending['rp'] !== null ? number_format($row->ending['rp'], 2, ',', '.') : '—' }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="14"><div class="fx-empty"><div>◎</div><strong>Belum ada data stok</strong><span>Belum ada saldo stok sebelumnya atau transaksi valas yang sudah dibayar untuk tanggal ini.</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="fx-note">
        <strong>Catatan:</strong> stok normal tidak diinput manual. Pembelian dan penjualan berasal dari item transaksi yang sudah <b>paid/completed</b>. Koreksi fisik/audit akan dibuat sebagai <b>Adjustment</b> terpisah agar tidak bercampur dengan pembelian atau penjualan.
    </div>
</div>

<style>
.fx-stock-page{max-width:1600px;margin:0 auto;padding:22px 24px 40px;color:var(--ui-text,#243a31)}
.fx-header{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:18px}.fx-eyebrow{font-size:9px;font-weight:800;letter-spacing:1.3px;color:var(--ui-text-muted,#8a9791);margin-bottom:6px}.fx-heading-row{display:flex;align-items:center;gap:12px}.fx-heading-row h1{margin:0;color:var(--ui-primary-dark,#174d3a);font-size:24px;line-height:1.15}.fx-heading-row p{margin:6px 0 0;color:var(--ui-text-muted,#74827c);font-size:11px}.fx-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid var(--ui-border,#dcece3);border-radius:999px;background:var(--ui-surface-soft,#f5fbf8);color:var(--ui-primary,#287153);font-size:9px;font-weight:800;white-space:nowrap}.fx-status i{width:6px;height:6px;border-radius:50%;background:var(--ui-primary,#37a36f)}.fx-date-form{display:flex;align-items:center;gap:8px}.fx-date-form label{font-size:10px;font-weight:750;color:var(--ui-text-secondary,#65756e)}.fx-date-form input{height:36px;padding:0 9px;border:1px solid var(--ui-border,#dce5df);border-radius:var(--ui-input-radius,8px);background:var(--ui-card-bg,#fff);color:var(--ui-text,#30473d);font-size:10px}
.fx-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;margin-bottom:15px}.fx-card{min-height:88px;display:flex;align-items:center;gap:12px;padding:14px 15px;border:1px solid var(--ui-card-border,var(--ui-border,#e3ebe7));border-radius:var(--ui-card-radius,9px);background:var(--ui-card-bg,#fff);box-shadow:var(--ui-card-shadow,none)}.fx-icon{width:34px;height:34px;display:flex;align-items:center;justify-content:center;flex:0 0 34px;border-radius:var(--ui-button-radius,8px);background:var(--ui-surface-soft,#eef6f2);color:var(--ui-icon,var(--ui-primary,#176b50));font-size:14px;font-weight:800}.fx-card small{display:block;font-size:8px;letter-spacing:.65px;font-weight:800;color:var(--ui-text-muted,#819089)}.fx-card strong{display:block;margin-top:4px;font-size:19px;line-height:1.1;color:var(--ui-primary-dark,var(--ui-text,#174d3a))}.fx-card em{display:block;margin-top:4px;color:var(--ui-text-muted,#9aa59f);font-size:9px;font-style:normal}
.fx-panel{background:var(--ui-card-bg,#fff);border:1px solid var(--ui-card-border,var(--ui-border,#e3ebe7));border-radius:var(--ui-card-radius,10px);overflow:hidden;box-shadow:var(--ui-card-shadow,none)}.fx-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid var(--ui-border,#e9efeb)}.fx-panel-head h2{margin:0;font-size:13px;font-weight:800;color:var(--ui-text,#214d3d)}.fx-panel-head p{margin:3px 0 0;color:var(--ui-text-muted,#8a9791);font-size:9px}.fx-info{padding:5px 8px;border-radius:6px;background:var(--ui-surface-soft,#f4f7f5);color:var(--ui-text-secondary,#718078);font-size:8px;font-weight:750}.fx-table-wrap{overflow:auto}.fx-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1280px}.fx-table th{background:var(--ui-surface-soft,#f7f9f8);color:var(--ui-text-secondary,#6f7d76);border-bottom:1px solid var(--ui-border,#e6ece9);border-right:1px solid var(--ui-border,#e6ece9);padding:8px 9px;text-align:center;font-size:8px;font-weight:800;letter-spacing:.35px;white-space:nowrap}.fx-table th:first-child,.fx-table th:nth-child(2){text-align:left}.fx-table th.group{font-size:9px;letter-spacing:.6px}.fx-table th.opening{color:var(--ui-text-secondary,#64736c)}.fx-table th.purchase{color:var(--ui-primary,#176b50)}.fx-table th.sales{color:var(--ui-gold,#a77a19)}.fx-table th.ending{color:var(--ui-primary-dark,#174d3a)}.fx-table .subhead th{font-size:7px;text-transform:uppercase}.fx-table td{padding:10px 9px;border-bottom:1px solid var(--ui-border,#edf1ef);border-right:1px solid var(--ui-border,#f0f3f1);color:var(--ui-text-secondary,#41524a);font-size:9px;vertical-align:middle;white-space:nowrap}.fx-table tbody tr:last-child td{border-bottom:0}.fx-table tbody tr:hover{background:var(--ui-surface-soft,#fbfdfc)}.fx-table .num{text-align:right;font-variant-numeric:tabular-nums}.fx-table .code{display:block;color:var(--ui-primary-dark,var(--ui-primary,#174d3a));font-size:10px}.fx-table .muted{display:block;margin-top:2px;color:var(--ui-text-muted,#8a9791);font-size:8px;font-weight:400}.fx-table .ending-qty,.fx-table .ending-rate,.fx-table .ending-rp{background:var(--ui-surface-soft,#f6faf8)}.fx-table .ending-rp{color:var(--ui-primary-dark,#174d3a)}.fx-empty{min-height:180px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:25px;color:var(--ui-text-muted,#87948e)}.fx-empty div{width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:50%;margin-bottom:8px;background:var(--ui-surface-soft,#f0f6f3);color:var(--ui-icon,var(--ui-primary,#6c9181));font-size:18px}.fx-empty strong{font-size:12px;color:var(--ui-text-secondary,#53645c)}.fx-empty span{max-width:480px;margin-top:5px;font-size:9px;line-height:1.5}.fx-note{margin-top:12px;padding:10px 13px;border:1px solid var(--ui-border,#e3ebe7);border-radius:var(--ui-button-radius,8px);background:var(--ui-surface-soft,#f7f9f8);color:var(--ui-text-secondary,#64736c);font-size:9px;line-height:1.55}.fx-note strong{color:var(--ui-text,#30473d)}
@media(max-width:900px){.fx-stock-page{padding:18px 14px 30px}.fx-header{align-items:stretch;flex-direction:column}.fx-heading-row{align-items:flex-start;flex-direction:column}.fx-date-form{justify-content:space-between}.fx-date-form input{flex:1}.fx-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.fx-info{display:none}}
@media(max-width:520px){.fx-summary{grid-template-columns:1fr}.fx-heading-row h1{font-size:20px}}
</style>
@endsection

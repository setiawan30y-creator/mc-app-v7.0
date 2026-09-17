@extends('layouts.app')

@section('title', 'Gantungan')

@section('content')
<style>
    .ledger-page{max-width:1450px;margin:0 auto;padding:24px 22px 44px;color:#17211b}
    .ledger-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:16px}
    .ledger-eyebrow{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#8a7040;margin-bottom:4px}
    .ledger-title{margin:0;font-size:23px;line-height:1.2;font-weight:800}
    .ledger-subtitle{margin:5px 0 0;font-size:12px;color:#6b7280}
    .ledger-btn{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:8px 13px;border:1px solid #cfd6d1;border-radius:7px;background:#fff;color:#17211b;text-decoration:none;font-size:11px;font-weight:800}
    .ledger-btn:hover{background:#f6f7f6;color:#17211b;text-decoration:none}
    .ledger-summary{display:grid;grid-template-columns:repeat(3,1fr);border:1px solid #cfd6d1;background:#fff;margin-bottom:14px}
    .ledger-summary-item{padding:10px 14px;border-right:1px solid #dfe3e0}
    .ledger-summary-item:last-child{border-right:0}
    .ledger-label{font-size:9px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#737b76;margin-bottom:3px}
    .ledger-number{font-size:17px;line-height:1.2;font-weight:800}
    .ledger-paper{background:#fff;border:1px solid #c9d0cb;box-shadow:0 1px 2px rgba(0,0,0,.035)}
    .ledger-filter{padding:9px 11px;border-bottom:1px solid #cfd6d1;background:#fafbfa}
    .ledger-filter form{display:grid;grid-template-columns:minmax(220px,1.6fr) 140px 170px 145px 80px;gap:7px;margin:0}
    .ledger-control{height:34px;width:100%;padding:6px 9px;border:1px solid #cbd2cd;border-radius:4px;background:#fff;color:#27312b;font-size:11px;outline:0}
    .ledger-control:focus{border-color:#8fa99a;box-shadow:0 0 0 2px rgba(20,121,87,.07)}
    .ledger-filter button{height:34px;border:1px solid #27312b;border-radius:4px;background:#27312b;color:#fff;font-size:11px;font-weight:800;cursor:pointer}
    .ledger-alert{padding:8px 11px;margin-bottom:10px;border:1px solid #b9dec5;background:#f1fbf4;color:#23613a;font-size:11px}
    .ledger-table-wrap{overflow-x:auto}
    .ledger-table{width:100%;min-width:920px;border-collapse:collapse;table-layout:auto}
    .ledger-table th{padding:7px 9px;border-bottom:2px solid #aeb8b1;background:#f5f7f5;color:#59635c;font-size:9px;line-height:1.2;font-weight:800;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
    .ledger-table td{padding:7px 9px;border-bottom:1px solid #dfe3e0;color:#27312b;font-size:11px;line-height:1.25;vertical-align:middle;white-space:nowrap}
    .ledger-table tbody tr:hover{background:#fafcfb}
    .ledger-table tbody tr:last-child td{border-bottom:0}
    .ledger-no{font-weight:800;color:#176b4d;text-decoration:none}
    .ledger-no:hover{text-decoration:underline}
    .ledger-title-cell{font-weight:700;max-width:250px;white-space:normal}
    .ledger-muted{color:#7a827d}
    .ledger-money{font-variant-numeric:tabular-nums;font-weight:700;text-align:right}
    .ledger-outstanding{font-weight:800}
    .ledger-status{display:inline-flex;align-items:center;min-width:62px;justify-content:center;padding:3px 6px;border:1px solid #cbd2cd;border-radius:3px;font-size:8px;font-weight:900;letter-spacing:.06em}
    .ledger-status-open{background:#f4f5f4;color:#5d665f}
    .ledger-status-partial{background:#fff8e8;border-color:#e7d5a4;color:#85651d}
    .ledger-status-settled{background:#eef9f1;border-color:#b8d8c0;color:#287044}
    .ledger-empty{padding:28px 12px!important;text-align:center;color:#858d87!important;font-size:11px!important}
    .ledger-pagination{padding:8px 10px;border-top:1px solid #dfe3e0;font-size:11px}
    .ledger-pagination nav{margin:0}
    @media(max-width:850px){.ledger-filter form{grid-template-columns:1fr 1fr}.ledger-filter form>div:first-child{grid-column:1/-1}.ledger-summary{grid-template-columns:1fr}.ledger-summary-item{border-right:0;border-bottom:1px solid #dfe3e0}.ledger-summary-item:last-child{border-bottom:0}.ledger-head{align-items:stretch}.ledger-btn{white-space:nowrap}}
    @media(max-width:560px){.ledger-page{padding:18px 10px 30px}.ledger-head{flex-direction:column}.ledger-filter form{grid-template-columns:1fr}.ledger-filter form>div:first-child{grid-column:auto}}
</style>

<div class="ledger-page">
    <div class="ledger-head">
        <div>
            <div class="ledger-eyebrow">Kas &amp; Operasional</div>
            <h1 class="ledger-title">Gantungan</h1>
            <p class="ledger-subtitle">Daftar uang/tagihan outstanding untuk rekonsiliasi Closing.</p>
        </div>
        <a href="{{ route('gantungan.create') }}" class="ledger-btn">+ Gantungan Baru</a>
    </div>

    @if(session('success'))
        <div class="ledger-alert">{{ session('success') }}</div>
    @endif

    <div class="ledger-summary">
        <div class="ledger-summary-item"><div class="ledger-label">Outstanding Aktif</div><div class="ledger-number">Rp {{ number_format($summary['open'],0,',','.') }}</div></div>
        <div class="ledger-summary-item"><div class="ledger-label">Jumlah Aktif</div><div class="ledger-number">{{ $summary['count'] }}</div></div>
        <div class="ledger-summary-item"><div class="ledger-label">Total Sudah Dilunasi</div><div class="ledger-number">Rp {{ number_format($summary['settled'],0,',','.') }}</div></div>
    </div>

    <div class="ledger-paper">
        <div class="ledger-filter">
            <form method="GET">
                <div><input name="q" value="{{ request('q') }}" class="ledger-control" placeholder="Cari nomor / judul / PIC"></div>
                <div><select name="status" class="ledger-control"><option value="">Semua Status</option><option value="open" @selected(request('status')==='open')>Open</option><option value="partial" @selected(request('status')==='partial')>Partial</option><option value="settled" @selected(request('status')==='settled')>Settled</option></select></div>
                <div><select name="category" class="ledger-control"><option value="">Semua Kategori</option><option value="employee" @selected(request('category')==='employee')>Karyawan</option><option value="branch" @selected(request('category')==='branch')>Cabang / Tempat Lain</option><option value="supplier" @selected(request('category')==='supplier')>Supplier</option><option value="operational" @selected(request('category')==='operational')>Operasional</option><option value="other" @selected(request('category')==='other')>Lainnya</option></select></div>
                <div><input type="date" name="date" value="{{ request('date') }}" class="ledger-control"></div>
                <div><button type="submit">Filter</button></div>
            </form>
        </div>

        <div class="ledger-table-wrap">
            <table class="ledger-table">
                <thead>
                    <tr><th>No. Gantungan</th><th>Tanggal</th><th>Kategori</th><th>Judul</th><th>PIC</th><th class="text-end">Nominal</th><th class="text-end">Outstanding</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($gantungans as $item)
                        @php
                            $statusClass = $item->status === 'settled' ? 'ledger-status-settled' : ($item->status === 'partial' ? 'ledger-status-partial' : 'ledger-status-open');
                            $categoryLabel = ['employee'=>'Karyawan','branch'=>'Cabang / Tempat Lain','supplier'=>'Supplier','operational'=>'Operasional','other'=>'Lainnya'][$item->category] ?? $item->category;
                        @endphp
                        <tr>
                            <td><a href="{{ route('gantungan.show',$item) }}" class="ledger-no">{{ $item->gantungan_no }}</a></td>
                            <td>{{ $item->business_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $categoryLabel }}</td>
                            <td class="ledger-title-cell">{{ $item->title }}</td>
                            <td class="ledger-muted">{{ $item->counterparty_name ?: '-' }}</td>
                            <td class="ledger-money">Rp {{ number_format($item->amount,0,',','.') }}</td>
                            <td class="ledger-money ledger-outstanding">Rp {{ number_format($item->outstanding_amount,0,',','.') }}</td>
                            <td><span class="ledger-status {{ $statusClass }}">{{ strtoupper($item->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ledger-empty">Belum ada data Gantungan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ledger-pagination">{{ $gantungans->links() }}</div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Gantungan')

@section('content')
<style>
    .gantungan-page{max-width:1280px;margin:0 auto;padding:28px 24px 48px}.gantungan-header{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:22px}.gantungan-eyebrow{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ui-gold,#b8944f);margin-bottom:5px}.gantungan-title{margin:0;font-size:25px;line-height:1.2;font-weight:800;color:var(--ui-text,#0f172a)}.gantungan-subtitle{margin:7px 0 0;color:var(--ui-muted,#64748b);font-size:13px}.gantungan-actions{display:flex;gap:8px;flex-wrap:wrap}.g-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:9px 14px;border-radius:10px;border:1px solid var(--ui-border,#e5e7eb);background:var(--ui-card-bg,#fff);color:var(--ui-text,#0f172a);text-decoration:none;font-size:12px;font-weight:700}.g-btn:hover{text-decoration:none;filter:brightness(.98)}.g-alert{border-radius:12px;padding:12px 15px;margin-bottom:16px;font-size:13px;border:1px solid}.g-alert-success{background:#ecfdf3;border-color:#bbf7d0;color:#166534}.g-alert-danger{background:#fef2f2;border-color:#fecaca;color:#991b1b}.g-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(300px,.8fr);gap:18px;align-items:start}.g-card{background:var(--ui-card-bg,#fff);border:1px solid var(--ui-border,#e5e7eb);border-radius:15px;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.035)}.g-card-head{padding:16px 18px;border-bottom:1px solid var(--ui-border,#e5e7eb);display:flex;align-items:center;justify-content:space-between;gap:12px}.g-card-head h2{margin:0;font-size:14px;font-weight:800;color:var(--ui-text,#0f172a)}.g-card-head p{margin:3px 0 0;font-size:11px;color:var(--ui-muted,#64748b)}.g-card-body{padding:18px}.g-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0;border:1px solid var(--ui-border,#e5e7eb);border-radius:12px;overflow:hidden}.g-info{padding:14px 15px;min-height:72px;border-right:1px solid var(--ui-border,#e5e7eb);border-bottom:1px solid var(--ui-border,#e5e7eb)}.g-info:nth-child(3n){border-right:0}.g-info:nth-last-child(-n+3){border-bottom:0}.g-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.045em;color:var(--ui-muted,#64748b);margin-bottom:5px}.g-value{font-size:13px;font-weight:650;color:var(--ui-text,#0f172a);word-break:break-word}.g-value-money{font-size:17px;font-weight:800}.g-description{margin-top:16px;padding:14px 15px;background:var(--ui-bg,#f5f7f6);border-radius:11px}.g-description .g-value{line-height:1.6;font-weight:500;white-space:pre-wrap}.g-status{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.04em}.g-status-open{background:#f1f5f9;color:#475569}.g-status-partial{background:#fff7ed;color:#9a3412}.g-status-settled{background:#ecfdf3;color:#166534}.g-summary{display:grid;gap:10px}.g-money-box{padding:15px;border:1px solid var(--ui-border,#e5e7eb);border-radius:12px}.g-money-box .g-label{margin-bottom:6px}.g-money-box strong{display:block;font-size:20px;line-height:1.2;color:var(--ui-text,#0f172a)}.g-money-box.outstanding{background:#fffbeb;border-color:#fde68a}.g-money-box.outstanding strong{color:#92400e}.g-form-row{margin-bottom:13px}.g-form-row:last-child{margin-bottom:0}.g-form-row label{display:block;margin-bottom:6px;font-size:11px;font-weight:750;color:var(--ui-text,#0f172a)}.g-form-control{width:100%;min-height:40px;padding:9px 11px;border:1px solid var(--ui-border,#dbe1e7);border-radius:9px;background:#fff;color:var(--ui-text,#0f172a);font-size:12px;outline:none}.g-form-control:focus{border-color:var(--ui-primary,#147957);box-shadow:0 0 0 3px rgba(20,121,87,.09)}textarea.g-form-control{min-height:74px;resize:vertical}.g-help{margin-top:5px;color:var(--ui-muted,#64748b);font-size:10px}.g-submit{width:100%;margin-top:4px;min-height:42px;border:0;border-radius:10px;background:var(--ui-primary,#147957);color:#fff;font-size:12px;font-weight:800;cursor:pointer}.g-submit:hover{filter:brightness(.95)}.g-history{margin-top:18px}.g-table-wrap{overflow-x:auto}.g-table{width:100%;border-collapse:collapse;min-width:680px}.g-table th{padding:11px 14px;text-align:left;background:var(--ui-bg,#f8fafc);color:var(--ui-muted,#64748b);border-bottom:1px solid var(--ui-border,#e5e7eb);font-size:10px;text-transform:uppercase;letter-spacing:.045em;white-space:nowrap}.g-table td{padding:12px 14px;border-bottom:1px solid var(--ui-border,#eef0f2);color:var(--ui-text,#0f172a);font-size:12px;vertical-align:middle}.g-table tbody tr:last-child td{border-bottom:0}.g-empty{padding:28px 18px;text-align:center;color:var(--ui-muted,#64748b);font-size:12px}.g-note{margin-top:12px;font-size:10px;color:var(--ui-muted,#64748b);line-height:1.5}@media(max-width:900px){.g-grid{grid-template-columns:1fr}.g-info-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.g-info:nth-child(3n){border-right:1px solid var(--ui-border,#e5e7eb)}.g-info:nth-child(2n){border-right:0}.g-info:nth-last-child(-n+3){border-bottom:1px solid var(--ui-border,#e5e7eb)}.g-info:last-child,.g-info:nth-last-child(2){border-bottom:0}}@media(max-width:600px){.gantungan-page{padding:20px 14px 36px}.gantungan-header{flex-direction:column}.gantungan-actions{width:100%}.g-btn{flex:1}.g-info-grid{grid-template-columns:1fr}.g-info,.g-info:nth-child(2n),.g-info:nth-child(3n){border-right:0;border-bottom:1px solid var(--ui-border,#e5e7eb)}.g-info:last-child{border-bottom:0}}
</style>

<div class="gantungan-page">
    <div class="gantungan-header">
        <div>
            <div class="gantungan-eyebrow">Kas &amp; Operasional</div>
            <h1 class="gantungan-title">Detail Gantungan</h1>
            <p class="gantungan-subtitle">{{ $gantungan->gantungan_no }} &nbsp;·&nbsp; Catatan dana yang masih outstanding</p>
        </div>
        <div class="gantungan-actions"><a href="{{ route('gantungan.index') }}" class="g-btn">← Kembali</a></div>
    </div>

    @if(session('success'))<div class="g-alert g-alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="g-alert g-alert-danger">{{ $errors->first() }}</div>@endif

    @php
        $statusClass = $gantungan->status === 'settled' ? 'g-status-settled' : ($gantungan->status === 'partial' ? 'g-status-partial' : 'g-status-open');
        $statusLabel = ['open'=>'OPEN','partial'=>'PARTIAL','settled'=>'SETTLED'][$gantungan->status] ?? strtoupper($gantungan->status);
        $categoryLabel = ['employee'=>'Karyawan','branch'=>'Cabang / Tempat Lain','supplier'=>'Supplier','operational'=>'Operasional','other'=>'Lainnya'][$gantungan->category] ?? $gantungan->category;
    @endphp

    <div class="g-grid">
        <section class="g-card">
            <div class="g-card-head"><div><h2>Informasi Gantungan</h2><p>Identitas dan posisi saldo gantungan saat ini.</p></div><span class="g-status {{ $statusClass }}">{{ $statusLabel }}</span></div>
            <div class="g-card-body">
                <div class="g-info-grid">
                    <div class="g-info"><div class="g-label">Judul</div><div class="g-value">{{ $gantungan->title }}</div></div>
                    <div class="g-info"><div class="g-label">Kategori</div><div class="g-value">{{ $categoryLabel }}</div></div>
                    <div class="g-info"><div class="g-label">Tanggal Bisnis</div><div class="g-value">{{ $gantungan->business_date?->format('d/m/Y') ?? '-' }}</div></div>
                    <div class="g-info"><div class="g-label">Nama / PIC</div><div class="g-value">{{ $gantungan->counterparty_name ?: '-' }}</div></div>
                    <div class="g-info"><div class="g-label">Jatuh Tempo</div><div class="g-value">{{ $gantungan->due_date?->format('d/m/Y') ?? '-' }}</div></div>
                    <div class="g-info"><div class="g-label">Referensi</div><div class="g-value">{{ $gantungan->reference ?: '-' }}</div></div>
                    <div class="g-info"><div class="g-label">Nominal Awal</div><div class="g-value g-value-money">Rp {{ number_format($gantungan->amount,0,',','.') }}</div></div>
                    <div class="g-info"><div class="g-label">Sudah Dilunasi</div><div class="g-value g-value-money">Rp {{ number_format($gantungan->settled_amount,0,',','.') }}</div></div>
                    <div class="g-info"><div class="g-label">Outstanding</div><div class="g-value g-value-money">Rp {{ number_format($gantungan->outstanding_amount,0,',','.') }}</div></div>
                </div>
                <div class="g-description"><div class="g-label">Keterangan</div><div class="g-value">{{ $gantungan->description ?: '-' }}</div></div>
            </div>
        </section>

        <aside class="g-summary">
            <div class="g-card">
                <div class="g-card-head"><div><h2>Ringkasan Saldo</h2><p>Posisi dana gantungan</p></div></div>
                <div class="g-card-body">
                    <div class="g-money-box"><div class="g-label">Nominal Awal</div><strong>Rp {{ number_format($gantungan->amount,0,',','.') }}</strong></div>
                    <div class="g-money-box"><div class="g-label">Sudah Dilunasi</div><strong>Rp {{ number_format($gantungan->settled_amount,0,',','.') }}</strong></div>
                    <div class="g-money-box outstanding"><div class="g-label">Sisa Outstanding</div><strong>Rp {{ number_format($gantungan->outstanding_amount,0,',','.') }}</strong></div>
                </div>
            </div>

            @if($gantungan->isOpen())
            <div class="g-card">
                <div class="g-card-head"><div><h2>Catat Pelunasan</h2><p>Masukkan dana yang sudah diterima kembali.</p></div></div>
                <div class="g-card-body">
                    <form method="POST" action="{{ route('gantungan.settle',$gantungan) }}">
                        @csrf
                        <div class="g-form-row"><label for="settle-amount">Nominal Pelunasan</label><input id="settle-amount" type="number" name="amount" max="{{ $gantungan->outstanding_amount }}" min="0.01" step="0.01" class="g-form-control" value="{{ old('amount') }}" required><div class="g-help">Maksimal Rp {{ number_format($gantungan->outstanding_amount,0,',','.') }}</div></div>
                        <div class="g-form-row"><label for="settle-method">Metode</label><select id="settle-method" name="method" class="g-form-control" required><option value="cash" @selected(old('method','cash') === 'cash')>Cash</option><option value="transfer" @selected(old('method') === 'transfer')>Transfer</option><option value="other" @selected(old('method') === 'other')>Lainnya</option></select></div>
                        <div class="g-form-row"><label for="settle-reference">Referensi</label><input id="settle-reference" name="reference" value="{{ old('reference') }}" class="g-form-control" placeholder="No. bukti / transfer / referensi"></div>
                        <div class="g-form-row"><label for="settle-notes">Catatan</label><textarea id="settle-notes" name="notes" class="g-form-control" rows="3" placeholder="Catatan pelunasan...">{{ old('notes') }}</textarea></div>
                        <button type="submit" class="g-submit">Simpan Pelunasan</button>
                    </form>
                </div>
            </div>
            @else
            <div class="g-card"><div class="g-card-body"><strong style="font-size:13px;">Gantungan sudah selesai.</strong><div class="g-note">Tidak ada pelunasan baru karena saldo outstanding sudah Rp 0.</div></div></div>
            @endif
        </aside>
    </div>

    <section class="g-card g-history">
        <div class="g-card-head"><div><h2>Riwayat Pelunasan</h2><p>Semua penerimaan yang sudah dicatat untuk gantungan ini.</p></div></div>
        <div class="g-table-wrap"><table class="g-table"><thead><tr><th>Tanggal</th><th>Metode</th><th class="text-end">Nominal</th><th>Referensi</th><th>Catatan</th><th>Oleh</th></tr></thead><tbody>
            @forelse($gantungan->settlements as $item)
            <tr><td>{{ $item->settled_at?->format('d/m/Y H:i') ?? '-' }}</td><td>{{ strtoupper($item->method) }}</td><td class="text-end fw-bold">Rp {{ number_format($item->amount,0,',','.') }}</td><td>{{ $item->reference ?: '-' }}</td><td>{{ $item->notes ?: '-' }}</td><td>{{ $item->createdBy->name ?? '-' }}</td></tr>
            @empty
            <tr><td colspan="6" class="g-empty">Belum ada pelunasan untuk gantungan ini.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>
@endsection

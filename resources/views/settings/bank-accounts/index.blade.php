@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <style>
        .bank-account-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:18px}
        .bank-account-card{background:#fff;border:1px solid #e5e7eb;border-radius:9px;padding:11px;box-shadow:0 1px 5px rgba(15,23,42,.04)}
        .bank-account-head{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
        .bank-account-name{font-size:12px;font-weight:800;color:#111827}.bank-account-number{font-size:9px;color:#64748b;margin-top:2px;line-height:1.25}
        .bank-account-status{font-size:7px;padding:3px 6px;border-radius:999px;background:#ecfdf5;color:#15803d;font-weight:800}.bank-account-status.off{background:#f3f4f6;color:#64748b}
        .bank-account-balance-label{font-size:8px;color:#64748b;margin-top:10px}.bank-account-balance{font-size:17px;font-weight:850;color:#111827;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .bank-account-meta{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:8px}.bank-account-meta-box{background:#f8fafc;border-radius:6px;padding:6px}.bank-account-meta-label{font-size:7px;color:#64748b}.bank-account-meta-value{font-size:9px;font-weight:800;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .bank-account-footer{display:flex;justify-content:space-between;gap:5px;border-top:1px solid #eef0f2;margin-top:8px;padding-top:7px;font-size:7px;color:#64748b;white-space:nowrap;overflow:hidden}.bank-account-footer span{overflow:hidden;text-overflow:ellipsis}
        .bank-account-actions{display:flex;justify-content:flex-end;gap:4px;margin-top:8px}.bank-account-actions .btn{font-size:8px;padding:3px 7px}
        .mutation-section{margin-top:18px}.mutation-title{font-size:17px;font-weight:800;color:#111827}.mutation-subtitle{font-size:11px;color:#64748b}.mutation-table th{font-size:10px;white-space:nowrap}.mutation-table td{font-size:11px;vertical-align:middle}.mutation-bank{font-weight:800;color:#111827}.mutation-account{font-size:9px;color:#64748b;margin-top:2px}.mutation-ref{font-family:monospace;font-size:10px}.mutation-credit{color:#15803d;font-weight:800}.mutation-debit{color:#dc2626;font-weight:800}.mutation-balance{font-weight:800;color:#111827}.mutation-empty{padding:40px 20px;text-align:center;color:#64748b}.mutation-filter{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-top:14px}
        @media(max-width:1200px){.bank-account-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:900px){.bank-account-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.bank-account-grid{grid-template-columns:1fr}}
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-1">Kas & Bank</h1><p class="text-muted mb-0">Kelola rekening bank, posisi saldo, dan riwayat mutasi seluruh rekening.</p></div>
        <a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">+ Tambah Rekening</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if($accounts->isEmpty())
        <div class="card shadow-sm border-0"><div class="card-body text-center py-5"><div class="fs-1 mb-3">🏦</div><h5>Belum ada rekening bank</h5><p class="text-muted">Tambahkan rekening bank pertama untuk mulai mencatat mutasi.</p><a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">Tambah Rekening Bank</a></div></div>
    @else
        <div class="bank-account-grid">
            @foreach($accounts as $account)
                @php
                    $todayCredit = $account->mutations->sum(fn($mutation) => (float) $mutation->credit);
                    $todayDebit = $account->mutations->sum(fn($mutation) => (float) $mutation->debit);
                    $todayNet = $todayCredit - $todayDebit;
                    $todayCount = $account->mutations->count();
                @endphp
                <div class="bank-account-card">
                    <div class="bank-account-head"><div><div class="bank-account-name">{{ $account->bank_name }}</div><div class="bank-account-number">{{ $account->account_number }} · {{ $account->account_name }}</div></div><span class="bank-account-status {{ $account->is_active ? '' : 'off' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                    <div class="bank-account-balance-label">Saldo Rekening</div><div class="bank-account-balance">Rp {{ number_format((float) $account->calculated_balance, 2, ',', '.') }}</div>
                    <div class="bank-account-meta"><div class="bank-account-meta-box"><div class="bank-account-meta-label">Credit Hari Ini</div><div class="bank-account-meta-value text-success">+ Rp {{ number_format($todayCredit, 2, ',', '.') }}</div></div><div class="bank-account-meta-box"><div class="bank-account-meta-label">Debit Hari Ini</div><div class="bank-account-meta-value text-danger">- Rp {{ number_format($todayDebit, 2, ',', '.') }}</div></div></div>
                    <div class="bank-account-footer"><span>{{ $todayCount }} mutasi hari ini</span><span>Net {{ $todayNet >= 0 ? '+' : '-' }} Rp {{ number_format(abs($todayNet), 2, ',', '.') }}</span></div>
                    <div class="bank-account-actions"><a href="{{ route('settings.bank-accounts.show', $account->id) }}" class="btn btn-sm btn-outline-primary">Detail</a><a href="{{ route('settings.bank-accounts.edit', $account->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a></div>
                </div>
            @endforeach
        </div>

        <div class="mutation-section card shadow-sm border-0"><div class="card-body"><div class="mutation-title">Riwayat Mutasi Bank</div><div class="mutation-subtitle">Gunakan form ini untuk memilih akun bank yang ingin dilihat. Kosongkan akun untuk melihat semua bank.</div>
            <form method="GET" action="{{ route('settings.bank-accounts.index') }}" class="mutation-filter"><div class="row g-2 align-items-end"><div class="col-lg-5 col-md-6"><label for="bank_account_id" class="form-label mb-1">Akun Bank</label><select name="bank_account_id" id="bank_account_id" class="form-select"><option value="">Semua Bank</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected($selectedBankAccount?->id === $account->id)>{{ $account->bank_name }} — {{ $account->account_number }} — {{ $account->account_name }}</option>@endforeach</select></div><div class="col-lg-2 col-md-3"><label for="date_from" class="form-label mb-1">Dari</label><input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}"></div><div class="col-lg-2 col-md-3"><label for="date_to" class="form-label mb-1">Sampai</label><input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}"></div><div class="col-lg-3 col-md-12 d-flex gap-2"><button type="submit" class="btn btn-primary flex-fill">Lihat Mutasi</button><a href="{{ route('settings.bank-accounts.index') }}" class="btn btn-outline-secondary">Reset</a></div></div></form>
        </div><div class="card-body border-top">
            @if($selectedBankAccount)<div class="alert alert-primary py-2 mb-3">Menampilkan mutasi: <strong>{{ $selectedBankAccount->bank_name }}</strong> — {{ $selectedBankAccount->account_number }}</div>@endif
            @if($mutations->isEmpty())<div class="mutation-empty">Tidak ada mutasi sesuai filter.</div>@else<div class="table-responsive"><table class="table table-hover mutation-table mb-0"><thead class="table-light"><tr><th>Tanggal</th><th>Bank / Rekening</th><th>Referensi</th><th>Keterangan</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Saldo</th><th>Status</th></tr></thead><tbody>@foreach($mutations as $mutation)<tr><td>{{ optional($mutation->transaction_date)->format('d/m/Y H:i') ?? '-' }}</td><td><div class="mutation-bank">{{ $mutation->bankAccount?->bank_name ?? 'Bank' }}</div><div class="mutation-account">{{ $mutation->bankAccount?->account_number ?? '-' }}</div></td><td class="mutation-ref">{{ $mutation->reference ?: '-' }}</td><td>{{ $mutation->description ?: ($mutation->source ?: '-') }}</td><td class="text-end mutation-debit">{{ (float) $mutation->debit > 0 ? '- Rp '.number_format((float)$mutation->debit, 2, ',', '.') : '-' }}</td><td class="text-end mutation-credit">{{ (float) $mutation->credit > 0 ? '+ Rp '.number_format((float)$mutation->credit, 2, ',', '.') : '-' }}</td><td class="text-end mutation-balance">Rp {{ number_format((float)$mutation->balance, 2, ',', '.') }}</td><td>@if($mutation->reconciliation_status === 'matched')<span class="badge bg-success">Matched</span>@elseif($mutation->reconciliation_status === 'manual')<span class="badge bg-info text-dark">Manual</span>@elseif($mutation->reconciliation_status === 'unmatched')<span class="badge bg-warning text-dark">Unmatched</span>@else<span class="badge bg-secondary">{{ $mutation->reconciliation_status ?: '-' }}</span>@endif</td></tr>@endforeach</tbody></table></div><div class="pt-3">{{ $mutations->links() }}</div>@endif
        </div></div>
    @endif
</div>
@endsection

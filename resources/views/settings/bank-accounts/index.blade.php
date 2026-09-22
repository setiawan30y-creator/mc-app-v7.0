@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <style>
        .bank-account-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:22px}
        .bank-account-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(15,23,42,.04)}
        .bank-account-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
        .bank-account-name{font-size:14px;font-weight:800;color:#111827}.bank-account-number{font-size:10px;color:#64748b;margin-top:4px}
        .bank-account-status{font-size:8px;padding:4px 8px;border-radius:999px;background:#ecfdf5;color:#15803d;font-weight:800}.bank-account-status.off{background:#f3f4f6;color:#64748b}
        .bank-account-balance-label{font-size:9px;color:#64748b;margin-top:18px}.bank-account-balance{font-size:24px;font-weight:850;color:#111827;margin-top:4px}
        .bank-account-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px}.bank-account-meta-box{background:#f8fafc;border-radius:8px;padding:9px}.bank-account-meta-label{font-size:8px;color:#64748b}.bank-account-meta-value{font-size:11px;font-weight:800;margin-top:3px}
        .bank-account-footer{display:flex;justify-content:space-between;border-top:1px solid #eef0f2;margin-top:12px;padding-top:10px;font-size:8px;color:#64748b}
        @media(max-width:1000px){.bank-account-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.bank-account-grid{grid-template-columns:1fr}}
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Kas & Bank</h1>
            <p class="text-muted mb-0">Kelola rekening bank dan lihat posisi setiap akun.</p>
        </div>
        <a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">+ Tambah Rekening</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if($accounts->isEmpty())
        <div class="card shadow-sm border-0"><div class="card-body text-center py-5">
            <div class="fs-1 mb-3">🏦</div><h5>Belum ada rekening bank</h5>
            <p class="text-muted">Tambahkan rekening bank pertama untuk mulai mencatat mutasi.</p>
            <a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">Tambah Rekening Bank</a>
        </div></div>
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
                    <div class="bank-account-head">
                        <div>
                            <div class="bank-account-name">{{ $account->bank_name }}</div>
                            <div class="bank-account-number">{{ $account->account_number }} · {{ $account->account_name }}</div>
                        </div>
                        <span class="bank-account-status {{ $account->is_active ? '' : 'off' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    <div class="bank-account-balance-label">Saldo Rekening</div>
                    <div class="bank-account-balance">Rp {{ number_format((float) $account->calculated_balance, 2, ',', '.') }}</div>

                    <div class="bank-account-meta">
                        <div class="bank-account-meta-box"><div class="bank-account-meta-label">Credit Hari Ini</div><div class="bank-account-meta-value text-success">+ Rp {{ number_format($todayCredit, 2, ',', '.') }}</div></div>
                        <div class="bank-account-meta-box"><div class="bank-account-meta-label">Debit Hari Ini</div><div class="bank-account-meta-value text-danger">- Rp {{ number_format($todayDebit, 2, ',', '.') }}</div></div>
                    </div>

                    <div class="bank-account-footer">
                        <span>{{ $todayCount }} mutasi hari ini</span>
                        <span>Net {{ $todayNet >= 0 ? '+' : '-' }} Rp {{ number_format(abs($todayNet), 2, ',', '.') }}</span>
                    </div>

                    <div class="mt-3 text-end">
                        <a href="{{ route('settings.bank-accounts.show', $account->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        <a href="{{ route('settings.bank-accounts.edit', $account->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Bank</th><th>Nama Rekening</th><th>Nomor Rekening</th><th>Mata Uang</th><th>Status</th><th>Saldo Berjalan</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->bank_name }}</strong>@if($account->bank_code)<div class="small text-muted">{{ $account->bank_code }}</div>@endif</td>
                                <td>{{ $account->account_name }}</td><td><code>{{ $account->account_number }}</code></td>
                                <td>@if($account->currency)<strong>{{ $account->currency->code }}</strong><div class="small text-muted">{{ $account->currency->name }}</div>@else-@endif</td>
                                <td>@if($account->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                                <td><strong>{{ number_format((float)$account->calculated_balance, 2, ',', '.') }}</strong></td>
                                <td class="text-end"><div class="btn-group"><a href="{{ route('settings.bank-accounts.show', $account->id) }}" class="btn btn-sm btn-outline-primary">Detail</a><a href="{{ route('settings.bank-accounts.edit', $account->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a></div></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

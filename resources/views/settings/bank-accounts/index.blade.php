@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Rekening Bank</h1>
            <p class="text-muted mb-0">Kelola rekening bank yang digunakan untuk transaksi Transfer.</p>
        </div>

        <a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">
            + Tambah Rekening
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($accounts->isEmpty())
                <div class="text-center py-5">
                    <div class="fs-1 mb-3">🏦</div>
                    <h5>Belum ada rekening bank</h5>
                    <p class="text-muted">Tambahkan rekening bank pertama untuk mulai mencatat mutasi.</p>
                    <a href="{{ route('settings.bank-accounts.create') }}" class="btn btn-primary">
                        Tambah Rekening Bank
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bank</th>
                                <th>Nama Rekening</th>
                                <th>Nomor Rekening</th>
                                <th>Mata Uang</th>
                                <th>Status</th>
                                <th>Saldo Berjalan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $account)
                                <tr>
                                    <td>
                                        <strong>{{ $account->bank_name }}</strong>
                                        @if($account->bank_code)
                                            <div class="small text-muted">{{ $account->bank_code }}</div>
                                        @endif
                                    </td>

                                    <td>{{ $account->account_name }}</td>

                                    <td>
                                        <code>{{ $account->account_number }}</code>
                                    </td>

                                    <td>
                                        @if($account->currency)
                                            <strong>{{ $account->currency->code }}</strong>
                                            <div class="small text-muted">
                                                {{ $account->currency->name }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($account->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>
                                            {{ number_format((float)$account->calculated_balance, 2, ',', '.') }}
                                        </strong>
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('settings.bank-accounts.show', $account->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>

                                            <a href="{{ route('settings.bank-accounts.edit', $account->id) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

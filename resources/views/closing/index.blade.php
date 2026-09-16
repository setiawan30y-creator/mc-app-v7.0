@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Closing Operasional</h1>
            <div class="text-muted small">Rekonsiliasi kas, bank, valuta, dan gantungan per shift.</div>
        </div>
        <a href="{{ route('closing.create') }}" class="btn btn-dark">+ Buat Closing</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('closing.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Bisnis</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Shift</label>
                    <select name="shift" class="form-select">
                        <option value="">Semua Shift</option>
                        <option value="morning" @selected(request('shift') === 'morning')>Pagi</option>
                        <option value="afternoon" @selected(request('shift') === 'afternoon')>Sore</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-dark flex-fill">Filter</button>
                    <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Closing</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Tipe</th>
                        <th class="text-end">Kas Fisik</th>
                        <th class="text-end">Gantungan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($closings as $closing)
                    <tr>
                        <td class="fw-semibold">{{ $closing->closing_no }}</td>
                        <td>{{ optional($closing->business_date)->format('d-m-Y') }}</td>
                        <td>{{ $closing->shift === 'morning' ? 'Pagi' : 'Sore' }}</td>
                        <td>{{ $closing->closing_type === 'shift_handover' ? 'Shift / Handover' : 'End of Day' }}</td>
                        <td class="text-end">Rp {{ number_format((float) $closing->physical_cash_amount, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format((float) $closing->hanging_amount, 0, ',', '.') }}</td>
                        <td><span class="badge text-bg-light border">{{ strtoupper($closing->status) }}</span></td>
                        <td class="text-end"><a href="{{ route('closing.show', $closing) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">Belum ada closing.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($closings->hasPages())
            <div class="card-footer bg-white">{{ $closings->links() }}</div>
        @endif
    </div>
</div>
@endsection

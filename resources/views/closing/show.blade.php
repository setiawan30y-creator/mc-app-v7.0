@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">{{ $closing->closing_no }}</h1>
            <div class="text-muted small">{{ optional($closing->business_date)->format('d-m-Y') }} · {{ $closing->shift === 'morning' ? 'Shift Pagi' : 'Shift Sore' }} · {{ $closing->closing_type === 'shift_handover' ? 'Shift / Handover' : 'End of Day' }}</div>
        </div>
        <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Kas Sistem</div><div class="fs-5 fw-bold">Rp {{ number_format((float) $closing->expected_cash_amount, 0, ',', '.') }}</div><div class="small text-muted">Engine belum dihubungkan</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Kas Fisik</div><div class="fs-5 fw-bold">Rp {{ number_format((float) $closing->physical_cash_amount, 0, ',', '.') }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Gantungan</div><div class="fs-5 fw-bold">Rp {{ number_format((float) $closing->hanging_amount, 0, ',', '.') }}</div><div class="small text-muted">Menunggu Modul Gantungan</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted">Status</div><div class="fs-5 fw-bold">{{ strtoupper($closing->status) }}</div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Kas Fisik per Pecahan</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Mata Uang</th><th>Series</th><th>Jenis</th><th class="text-end">Pecahan</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                @forelse($closing->details as $detail)
                    <tr>
                        <td>{{ $detail->currency->code ?? '-' }}</td>
                        <td>{{ $detail->currencyVariant->name ?? '-' }}</td>
                        <td>{{ $detail->currencyDenomination->type_label ?? '-' }}</td>
                        <td class="text-end">{{ $detail->currencyDenomination->display_label ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $detail->physical_quantity, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $detail->physical_amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pecahan yang dihitung.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Rekonsiliasi</h5>
            <div class="row g-3">
                <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Kas</div><strong>Fisik Rp {{ number_format((float) $closing->physical_cash_amount, 0, ',', '.') }}</strong><br><span class="text-muted">Saldo sistem: menunggu engine</span></div></div>
                <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Bank</div><strong>Menunggu rekonsiliasi bank</strong></div></div>
                <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Gantungan</div><strong>Rp {{ number_format((float) $closing->hanging_amount, 0, ',', '.') }}</strong><br><span class="text-muted">Sumber: Modul Gantungan</span></div></div>
            </div>
            @if($closing->notes)
                <hr><div class="small text-muted">Catatan</div><div>{{ $closing->notes }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

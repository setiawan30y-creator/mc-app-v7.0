@extends('layouts.app')

@section('content')
<style>
    .closing-page{padding:18px 20px 28px}
    .closing-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px}
    .closing-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#718078;margin-bottom:3px}
    .closing-title{font-size:20px;font-weight:700;line-height:1.2;margin:0}
    .closing-subtitle{font-size:12px;color:#77827d;margin-top:4px}
    .closing-btn{font-size:12px;font-weight:600;padding:7px 11px;border-radius:7px}
    .closing-paper{background:#fff;border:1px solid #dfe7e2;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.03);overflow:hidden}
    .closing-filter{padding:10px 12px;border-bottom:1px solid #dfe7e2;background:#fafcfb}
    .closing-filter form{display:flex;align-items:end;gap:8px;flex-wrap:nowrap}
    .closing-filter .filter-field{flex:0 0 190px}
    .closing-filter .filter-actions{display:flex;gap:6px;align-items:end;flex:0 0 auto}
    .closing-filter .form-label{font-size:10px;margin-bottom:3px;color:#65716b}
    .closing-filter .form-control,.closing-filter .form-select{font-size:12px;min-height:31px;height:31px;padding:4px 8px}
    .closing-filter .btn{font-size:11px;height:31px;padding:5px 10px}
    .closing-table{font-size:12px;margin:0!important}
    .closing-table th{font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:#68746e;background:#f4f7f5!important;white-space:nowrap;padding:6px 8px!important}
    .closing-table td{padding:5px 8px!important;line-height:1.25;vertical-align:middle}
    .closing-table .code{font-weight:700;white-space:nowrap}
    .closing-table .amount{font-variant-numeric:tabular-nums;white-space:nowrap}
    .closing-status{display:inline-block;padding:2px 6px;border:1px solid #d5dfda;border-radius:4px;font-size:9px;font-weight:700;line-height:1.2;letter-spacing:.04em}
    .closing-detail{font-size:10px;padding:4px 8px;line-height:1.2}
    .closing-empty{padding:24px 10px!important;color:#87928d}
    .closing-footer{padding:8px 12px;background:#fff;border-top:1px solid #dfe7e2}
    @media(max-width:768px){
        .closing-page{padding:12px}
        .closing-head{align-items:flex-start}
        .closing-title{font-size:18px}
        .closing-head .closing-btn{white-space:nowrap}
        .closing-paper{overflow-x:auto}
        .closing-filter{overflow-x:auto}
        .closing-filter form{min-width:430px}
        .closing-filter .filter-field{flex-basis:160px}
        .closing-table{min-width:780px}
    }
</style>

<div class="container-fluid closing-page">
    <div class="closing-head">
        <div>
            <div class="closing-eyebrow">Kas & Operasional</div>
            <h1 class="closing-title">Closing Operasional</h1>
            <div class="closing-subtitle">Rekonsiliasi kas, bank, valuta, dan gantungan per shift.</div>
        </div>
        <a href="{{ route('closing.create') }}" class="btn btn-dark closing-btn">+ Buat Closing</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 px-3 small mb-3">{{ session('success') }}</div>
    @endif

    <div class="closing-paper mb-3">
        <div class="closing-filter">
            <form method="GET" action="{{ route('closing.index') }}">
                <div class="filter-field">
                    <label class="form-label">Tanggal Bisnis</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="filter-field">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-select">
                        <option value="">Semua Shift</option>
                        <option value="morning" @selected(request('shift') === 'morning')>Pagi</option>
                        <option value="afternoon" @selected(request('shift') === 'afternoon')>Sore</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-outline-dark">Filter</button>
                    <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="closing-paper">
        <div class="table-responsive">
            <table class="table table-hover closing-table align-middle">
                <thead>
                    <tr>
                        <th>Closing</th><th>Tanggal</th><th>Shift</th><th>Tipe</th>
                        <th class="text-end">Kas Fisik</th><th class="text-end">Gantungan</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($closings as $closing)
                    <tr>
                        <td class="code">{{ $closing->closing_no }}</td>
                        <td>{{ optional($closing->business_date)->format('d-m-Y') }}</td>
                        <td>{{ $closing->shift === 'morning' ? 'Pagi' : 'Sore' }}</td>
                        <td>{{ $closing->closing_type === 'shift_handover' ? 'Shift / Handover' : 'End of Day' }}</td>
                        <td class="text-end amount">Rp {{ number_format((float) $closing->physical_cash_amount, 0, ',', '.') }}</td>
                        <td class="text-end amount">Rp {{ number_format((float) $closing->hanging_amount, 0, ',', '.') }}</td>
                        <td><span class="closing-status">{{ strtoupper($closing->status) }}</span></td>
                        <td class="text-end"><a href="{{ route('closing.show', $closing) }}" class="btn btn-outline-secondary closing-detail">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center closing-empty">Belum ada closing.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($closings->hasPages())
            <div class="closing-footer">{{ $closings->links() }}</div>
        @endif
    </div>
</div>
@endsection

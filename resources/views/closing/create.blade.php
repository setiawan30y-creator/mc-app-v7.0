@extends('layouts.app')

@section('content')
<style>
    .closing-form-page{padding:18px 20px 28px}
    .closing-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px}
    .closing-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#718078;margin-bottom:3px}
    .closing-title{font-size:20px;font-weight:700;line-height:1.2;margin:0}
    .closing-subtitle{font-size:12px;color:#77827d;margin-top:4px}
    .closing-btn{font-size:12px;font-weight:600;padding:7px 11px;border-radius:7px}
    .closing-section{background:#fff;border:1px solid #dfe7e2;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.03);overflow:hidden;margin-bottom:12px}
    .closing-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:9px 12px;border-bottom:1px solid #dfe7e2;background:#fafcfb}
    .closing-section-title{font-size:12px;font-weight:700;margin:0}
    .closing-section-note{font-size:10px;color:#7b8681;margin-top:2px}
    .closing-section-body{padding:12px}
    .closing-form-page .form-label{font-size:10px;font-weight:600;color:#66726c;margin-bottom:3px}
    .closing-form-page .form-control,.closing-form-page .form-select{font-size:12px;min-height:31px;padding:4px 8px}
    .closing-form-page textarea.form-control{min-height:auto}
    .closing-table{font-size:11px;margin:0!important}
    .closing-table th{font-size:9px;text-transform:uppercase;letter-spacing:.04em;color:#68746e;background:#f4f7f5!important;white-space:nowrap;padding:5px 7px!important}
    .closing-table td{padding:4px 7px!important;line-height:1.2;vertical-align:middle}
    .closing-table .qty{width:120px}
    .closing-table .qty input{text-align:right}
    .closing-table .money{font-variant-numeric:tabular-nums;white-space:nowrap}
    .closing-total{font-size:12px;font-weight:700;white-space:nowrap}
    .closing-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
    .closing-summary-item{border:1px solid #dfe7e2;border-radius:6px;padding:9px 10px;background:#fff;min-height:64px}
    .closing-summary-label{font-size:9px;text-transform:uppercase;letter-spacing:.04em;color:#78837e}
    .closing-summary-value{font-size:15px;font-weight:700;line-height:1.2;margin-top:4px}
    .closing-actions{display:flex;justify-content:flex-end;gap:7px;margin-top:4px}
    .closing-actions .btn{font-size:11px;padding:6px 10px}
    @media(max-width:900px){.closing-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:768px){
        .closing-form-page{padding:12px}
        .closing-head{align-items:flex-start}
        .closing-title{font-size:18px}
        .closing-head .closing-btn{white-space:nowrap}
        .closing-summary{grid-template-columns:1fr 1fr}
        .closing-section-body{padding:9px}
        .closing-table{min-width:700px}
        .closing-table-wrap{overflow-x:auto}
    }
    @media(max-width:520px){.closing-summary{grid-template-columns:1fr}.closing-head{flex-direction:column}.closing-head .closing-btn{width:100%}.closing-actions{flex-direction:column}.closing-actions .btn{width:100%}}
</style>

<div class="container-fluid closing-form-page">
    <div class="closing-head">
        <div>
            <div class="closing-eyebrow">Kas & Operasional</div>
            <h1 class="closing-title">Buat Closing Operasional</h1>
            <div class="closing-subtitle">Hitung kas fisik berdasarkan pecahan dan siapkan rekonsiliasi akhir shift.</div>
        </div>
        <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary closing-btn">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small mb-3"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if($existing)
        <div class="alert alert-warning py-2 px-3 small mb-3">Closing {{ $existing->closing_no }} untuk tanggal dan shift ini sudah ada dengan status <strong>{{ strtoupper($existing->status) }}</strong>. <a href="{{ route('closing.show', $existing) }}" class="alert-link">Buka detail</a>.</div>
    @endif

    <form method="POST" action="{{ route('closing.store') }}">
        @csrf

        <section class="closing-section">
            <div class="closing-section-head"><div><h2 class="closing-section-title">01 · Informasi Closing</h2><div class="closing-section-note">Tentukan tanggal bisnis, shift, dan jenis closing.</div></div></div>
            <div class="closing-section-body">
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label">Tanggal Bisnis</label><input type="date" name="business_date" value="{{ old('business_date', $businessDate) }}" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Shift</label><select name="shift" class="form-select" required><option value="morning" @selected(old('shift', $shift) === 'morning')>Pagi</option><option value="afternoon" @selected(old('shift', $shift) === 'afternoon')>Sore</option></select></div>
                    <div class="col-md-3"><label class="form-label">Jenis Closing</label><select name="closing_type" class="form-select" required><option value="shift_handover" @selected(old('closing_type') === 'shift_handover')>Shift / Handover</option><option value="end_of_day" @selected(old('closing_type') === 'end_of_day')>End of Day / Tutup Toko</option></select></div>
                    <div class="col-md-3"><label class="form-label">Status</label><input type="text" value="DRAFT" class="form-control" readonly></div>
                </div>
            </div>
        </section>

        <section class="closing-section">
            <div class="closing-section-head">
                <div><h2 class="closing-section-title">02 · Kas Fisik & Pecahan</h2><div class="closing-section-note">Masukkan jumlah lembar/keping yang benar-benar ada di kas.</div></div>
                <div class="closing-total">Total Fisik: <span id="physical-total">Rp 0</span></div>
            </div>
            <div class="closing-table-wrap">
                <table class="table closing-table align-middle">
                    <thead><tr><th>Mata Uang</th><th>Series</th><th>Jenis</th><th class="text-end">Pecahan</th><th class="text-end qty">Qty Fisik</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    @forelse($denominations as $denomination)
                        <tr>
                            <td>{{ $denomination->variant->currency->code ?? '-' }}</td>
                            <td>{{ $denomination->variant->name ?? '-' }}</td>
                            <td>{{ $denomination->type_label }}</td>
                            <td class="text-end">{{ $denomination->display_label }}</td>
                            <td class="qty"><input type="number" min="0" step="1" name="physical_quantity[{{ $denomination->id }}]" value="{{ old('physical_quantity.' . $denomination->id, 0) }}" class="form-control form-control-sm denomination-qty" data-value="{{ $denomination->value }}"></td>
                            <td class="text-end money denomination-total">Rp 0</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada master pecahan aktif.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="closing-section">
            <div class="closing-section-head"><div><h2 class="closing-section-title">03 · Rekonsiliasi</h2><div class="closing-section-note">Ringkasan nilai yang akan digunakan oleh engine closing.</div></div></div>
            <div class="closing-section-body">
                <div class="closing-summary">
                    <div class="closing-summary-item"><div class="closing-summary-label">Saldo Kas Sistem</div><div class="closing-summary-value">Rp {{ number_format((float) ($preview['expected_cash_amount'] ?? 0), 0, ',', '.') }}</div></div>
                    <div class="closing-summary-item"><div class="closing-summary-label">Kas Fisik</div><div class="closing-summary-value" id="summary-physical">Rp 0</div></div>
                    <div class="closing-summary-item"><div class="closing-summary-label">Gantungan</div><div class="closing-summary-value">Rp {{ number_format((float) ($preview['hanging_amount'] ?? 0), 0, ',', '.') }}</div></div>
                    <div class="closing-summary-item"><div class="closing-summary-label">Bank Sistem</div><div class="closing-summary-value">Rp {{ number_format((float) ($preview['bank_system_amount'] ?? 0), 0, ',', '.') }}</div></div>
                </div>
            </div>
        </section>

        <section class="closing-section">
            <div class="closing-section-head"><div><h2 class="closing-section-title">04 · Catatan</h2><div class="closing-section-note">Catatan selisih, kondisi kas, handover, atau informasi penting lainnya.</div></div></div>
            <div class="closing-section-body"><textarea name="notes" rows="3" class="form-control" placeholder="Tulis catatan di sini...">{{ old('notes') }}</textarea></div>
        </section>

        <div class="closing-actions"><a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Batal</a><button type="submit" class="btn btn-dark" @disabled($existing)>Simpan Draft Closing</button></div>
    </form>
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.denomination-qty');
    const totalEl = document.getElementById('physical-total');
    const summaryEl = document.getElementById('summary-physical');
    function money(value) { return 'Rp ' + Math.round(value).toLocaleString('id-ID'); }
    function recalc() {
        let grand = 0;
        inputs.forEach((input) => {
            const qty = Number(input.value || 0);
            const value = Number(input.dataset.value || 0);
            const total = qty * value;
            grand += total;
            const cell = input.closest('tr').querySelector('.denomination-total');
            if (cell) cell.textContent = money(total);
        });
        totalEl.textContent = money(grand);
        summaryEl.textContent = money(grand);
    }
    inputs.forEach((input) => input.addEventListener('input', recalc));
    recalc();
})();
</script>
@endsection
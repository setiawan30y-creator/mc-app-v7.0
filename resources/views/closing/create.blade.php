@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Buat Closing Operasional</h1>
            <div class="text-muted small">Hitung fisik per pecahan. Saldo sistem, bank, dan gantungan akan direkonsiliasi oleh engine masing-masing.</div>
        </div>
        <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if($existing)
        <div class="alert alert-warning">
            Closing {{ $existing->closing_no }} untuk tanggal dan shift ini sudah ada dengan status <strong>{{ strtoupper($existing->status) }}</strong>.
            <a href="{{ route('closing.show', $existing) }}" class="alert-link">Buka detail</a>.
        </div>
    @endif

    <form method="POST" action="{{ route('closing.store') }}">
        @csrf

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">1. Informasi Closing</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Bisnis</label>
                        <input type="date" name="business_date" value="{{ old('business_date', $businessDate) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-select" required>
                            <option value="morning" @selected(old('shift', $shift) === 'morning')>Pagi</option>
                            <option value="afternoon" @selected(old('shift', $shift) === 'afternoon')>Sore</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Closing</label>
                        <select name="closing_type" class="form-select" required>
                            <option value="shift_handover" @selected(old('closing_type') === 'shift_handover')>Shift / Handover</option>
                            <option value="end_of_day" @selected(old('closing_type') === 'end_of_day')>End of Day / Tutup Toko</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Sistem</label>
                        <input type="text" value="DRAFT" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">2. Kas Fisik & Pecahan</h5>
                        <div class="text-muted small">Masukkan jumlah lembar/keping yang benar-benar ada di kas.</div>
                    </div>
                    <div class="fw-bold">Total Fisik: <span id="physical-total">Rp 0</span></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr><th>Mata Uang</th><th>Series</th><th>Jenis</th><th class="text-end">Pecahan</th><th style="width:150px">Qty Fisik</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                        @forelse($denominations as $denomination)
                            <tr>
                                <td>{{ $denomination->variant->currency->code ?? '-' }}</td>
                                <td>{{ $denomination->variant->name ?? '-' }}</td>
                                <td>{{ $denomination->type_label }}</td>
                                <td class="text-end">{{ $denomination->display_label }}</td>
                                <td>
                                    <input type="number" min="0" step="1" name="physical_quantity[{{ $denomination->id }}]" value="{{ old('physical_quantity.' . $denomination->id, 0) }}" class="form-control form-control-sm denomination-qty" data-value="{{ $denomination->value }}">
                                </td>
                                <td class="text-end denomination-total">Rp 0</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada master pecahan aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">3. Rekonsiliasi</h5>
                <div class="row g-3">
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-muted">Saldo Kas Sistem</div><div class="fs-5 fw-bold">Akan dihitung</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-muted">Kas Fisik</div><div class="fs-5 fw-bold" id="summary-physical">Rp 0</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-muted">Gantungan</div><div class="fs-5 fw-bold">Akan diambil dari Modul Gantungan</div></div></div>
                    <div class="col-md-3"><div class="border rounded p-3"><div class="small text-muted">Status Rekonsiliasi</div><div class="fs-5 fw-bold">Menunggu Engine</div></div></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">4. Catatan</h5>
                <textarea name="notes" rows="3" class="form-control" placeholder="Catatan selisih, kondisi kas, handover, atau informasi penting lainnya...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('closing.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-dark" @disabled($existing)>Simpan Draft Closing</button>
        </div>
    </form>
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.denomination-qty');
    const totalEl = document.getElementById('physical-total');
    const summaryEl = document.getElementById('summary-physical');

    function money(value) {
        return 'Rp ' + Math.round(value).toLocaleString('id-ID');
    }

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

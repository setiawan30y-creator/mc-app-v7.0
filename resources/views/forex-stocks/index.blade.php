@extends('layouts.app')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <div class="eyebrow">TREASURY</div>
            <h1>Stok Valas Hari Ini</h1>
            <p>Posisi stok fisik valas per pecahan untuk cabang aktif.</p>
        </div>
        <form method="GET" class="date-filter">
            <label for="date">Tanggal</label>
            <input id="date" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
        </form>
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <div class="summary-grid">
        <div class="summary-card"><span>Mata Uang</span><strong>{{ $summary['currencies'] }}</strong></div>
        <div class="summary-card"><span>Pecahan</span><strong>{{ $summary['denominations'] }}</strong></div>
        <div class="summary-card"><span>Total Unit</span><strong>{{ number_format($summary['units'], 0, ',', '.') }}</strong></div>
        <div class="summary-card"><span>Nilai Nominal</span><strong>{{ number_format($summary['value'], 2, ',', '.') }}</strong></div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div><h2>Posisi Stok</h2><span>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Currency</th><th>Variant</th><th>Jenis</th><th>Pecahan</th><th class="num">Qty</th><th class="num">Nilai Nominal</th></tr></thead>
                <tbody>
                @forelse($stocks as $stock)
                    <tr>
                        <td><strong>{{ $stock->variant->currency->code }}</strong><br><small>{{ $stock->variant->currency->name }}</small></td>
                        <td>{{ $stock->variant->name }}</td>
                        <td>{{ $stock->denomination->type_label }}</td>
                        <td>{{ $stock->denomination->display_label }}</td>
                        <td class="num">{{ number_format((float)$stock->quantity, 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((float)$stock->quantity * (float)$stock->denomination->value, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada stok untuk tanggal ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel entry-panel">
        <div class="panel-head"><div><h2>Input / Koreksi Stok</h2><span>Disimpan per tanggal, cabang, dan pecahan.</span></div></div>
        <form method="POST" action="{{ route('forex-stocks.upsert') }}" class="stock-form">
            @csrf
            <input type="hidden" name="stock_date" value="{{ $date }}">
            <label>Currency & Variant
                <select name="currency_variant_id" id="currency_variant_id" required>
                    <option value="">Pilih variant</option>
                    @foreach($currencies as $currency)
                        <optgroup label="{{ $currency->code }} — {{ $currency->name }}">
                            @foreach($currency->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->name }}{{ $variant->code ? ' ('.$variant->code.')' : '' }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </label>
            <label>Pecahan
                <select name="currency_denomination_id" id="currency_denomination_id" required disabled>
                    <option value="">Pilih variant terlebih dahulu</option>
                </select>
            </label>
            <label>Qty
                <input type="number" name="quantity" min="0" step="1" value="0" required>
            </label>
            <button type="submit">Simpan Stok</button>
        </form>
    </div>
</div>

<style>
.page-shell{padding:28px 32px 48px}.page-header{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}.eyebrow{font-size:10px;letter-spacing:1.2px;font-weight:800;color:#7b8b84}.page-header h1{margin:5px 0 4px;font-size:25px;color:#174d3a}.page-header p{margin:0;color:#718078;font-size:12px}.date-filter{display:flex;align-items:center;gap:9px;font-size:11px;font-weight:700;color:#53645d}.date-filter input,.stock-form select,.stock-form input{height:38px;border:1px solid #dce5df;border-radius:8px;padding:0 11px;background:#fff;color:#203b31}.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}.summary-card{background:#fff;border:1px solid #e4ebe7;border-radius:12px;padding:17px 18px;box-shadow:0 3px 12px rgba(20,60,45,.04)}.summary-card span{display:block;color:#75847d;font-size:10px;font-weight:700}.summary-card strong{display:block;margin-top:7px;color:#174d3a;font-size:22px}.panel{background:#fff;border:1px solid #e4ebe7;border-radius:12px;overflow:hidden;box-shadow:0 3px 12px rgba(20,60,45,.04);margin-bottom:18px}.panel-head{padding:16px 18px;border-bottom:1px solid #e9efeb}.panel-head h2{margin:0;color:#174d3a;font-size:14px}.panel-head span{display:block;margin-top:4px;color:#8a9791;font-size:10px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;font-size:11px}th{padding:11px 14px;text-align:left;background:#f6f9f7;color:#687970;font-size:9px;text-transform:uppercase;letter-spacing:.4px}td{padding:13px 14px;border-top:1px solid #edf1ef;color:#34463e}td small{color:#89958f}.num{text-align:right}.empty{text-align:center;color:#89958f;padding:28px}.stock-form{display:grid;grid-template-columns:1.2fr 1.2fr .6fr auto;gap:12px;padding:18px;align-items:end}.stock-form label{display:flex;flex-direction:column;gap:6px;font-size:10px;font-weight:750;color:#617169}.stock-form button{height:38px;border:0;border-radius:8px;padding:0 17px;background:#176b50;color:#fff;font-weight:750;cursor:pointer}.stock-form button:hover{background:#125b44}.alert{padding:12px 15px;border-radius:8px;margin-bottom:16px;font-size:11px}.alert.success{background:#edf8f2;color:#176b50}.alert.error{background:#fff1f0;color:#a23a32}@media(max-width:900px){.summary-grid{grid-template-columns:repeat(2,1fr)}.stock-form{grid-template-columns:1fr 1fr}.page-header{align-items:flex-start;flex-direction:column}}@media(max-width:560px){.summary-grid{grid-template-columns:1fr}.stock-form{grid-template-columns:1fr}}
</style>

<script>
const currencyVariants = @json($currencies->flatMap(fn($currency) => $currency->variants->map(fn($variant) => [
    'id' => $variant->id,
    'denominations' => $variant->denominations->map(fn($d) => ['id'=>$d->id,'label'=>$d->display_label,'type'=>$d->type_label])->values()
]))->values());
const variantSelect = document.getElementById('currency_variant_id');
const denominationSelect = document.getElementById('currency_denomination_id');
variantSelect.addEventListener('change', () => {
    const variant = currencyVariants.find(v => String(v.id) === String(variantSelect.value));
    denominationSelect.innerHTML = '<option value="">Pilih pecahan</option>';
    denominationSelect.disabled = !variant;
    if (variant) variant.denominations.forEach(d => {
        const option = document.createElement('option');
        option.value = d.id;
        option.textContent = `${d.label} — ${d.type}`;
        denominationSelect.appendChild(option);
    });
});
</script>
@endsection

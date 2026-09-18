@extends('layouts.app')

@section('title', 'Stok Valas Hari Ini · MC Almara')

@section('content')
<div class="fx-stock-page">
    <header class="fx-header">
        <div>
            <div class="fx-eyebrow">TREASURY / INVENTORY</div>
            <div class="fx-heading-row">
                <div>
                    <h1>Stok Valas Hari Ini</h1>
                    <p>Posisi stok fisik berdasarkan mata uang, variant, dan pecahan.</p>
                </div>
                <span class="fx-status"><i></i> Operasional</span>
            </div>
        </div>
        <form method="GET" class="fx-date-form">
            <label for="stock-date">Tanggal</label>
            <input id="stock-date" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
        </form>
    </header>

    @if(session('success'))
        <div class="fx-alert success">✓ <span>{{ session('success') }}</span></div>
    @endif
    @if($errors->any())
        <div class="fx-alert error">! <span>{{ $errors->first() }}</span></div>
    @endif

    <div class="fx-summary">
        <div class="fx-card"><div class="fx-icon">◎</div><div><small>MATA UANG</small><strong>{{ $summary['currencies'] }}</strong><em>currency dalam stok</em></div></div>
        <div class="fx-card"><div class="fx-icon">▤</div><div><small>PECahan</small><strong>{{ $summary['denominations'] }}</strong><em>posisi pecahan</em></div></div>
        <div class="fx-card"><div class="fx-icon">#</div><div><small>TOTAL UNIT</small><strong>{{ number_format($summary['units'], 0, ',', '.') }}</strong><em>lembar / keping</em></div></div>
        <div class="fx-card value"><div class="fx-icon">¤</div><div><small>NILAI NOMINAL</small><strong>{{ number_format($summary['value'], 2, ',', '.') }}</strong><em>total nominal fisik</em></div></div>
    </div>

    <section class="fx-panel">
        <div class="fx-panel-head">
            <div><h2>Posisi Stok</h2><p>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p></div>
            <span class="fx-count">{{ $stocks->count() }} posisi</span>
        </div>
        <div class="fx-table-wrap">
            <table class="fx-table">
                <thead><tr><th>Currency</th><th>Variant</th><th>Jenis</th><th>Pecahan</th><th class="num">Qty</th><th class="num">Nilai Nominal</th></tr></thead>
                <tbody>
                @forelse($stocks as $stock)
                    <tr>
                        <td><b class="code">{{ $stock->variant->currency->code }}</b><span class="muted">{{ $stock->variant->currency->name }}</span></td>
                        <td>{{ $stock->variant->name }}</td>
                        <td><span class="badge">{{ $stock->denomination->type_label }}</span></td>
                        <td><strong>{{ $stock->denomination->display_label }}</strong></td>
                        <td class="num"><strong>{{ number_format((float) $stock->quantity, 0, ',', '.') }}</strong></td>
                        <td class="num"><strong>{{ number_format((float) $stock->quantity * (float) $stock->denomination->value, 2, ',', '.') }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="fx-empty"><div>◎</div><strong>Belum ada stok</strong><span>Belum ada posisi stok untuk tanggal ini. Masukkan stok awal melalui form di bawah.</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="fx-panel entry">
        <div class="fx-panel-head">
            <div><h2>Input / Koreksi Stok</h2><p>Masukkan jumlah fisik per pecahan untuk tanggal dan cabang aktif.</p></div>
            <span class="fx-manual">MANUAL ENTRY</span>
        </div>
        <form method="POST" action="{{ route('forex-stocks.upsert') }}" class="fx-form">
            @csrf
            <input type="hidden" name="stock_date" value="{{ $date }}">
            <div class="fx-field">
                <label for="currency_variant_id">Currency & Variant</label>
                <select name="currency_variant_id" id="currency_variant_id" required>
                    <option value="">Pilih currency / variant</option>
                    @foreach($currencies as $currency)
                        <optgroup label="{{ $currency->code }} — {{ $currency->name }}">
                            @foreach($currency->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->name }}{{ $variant->code ? ' ('.$variant->code.')' : '' }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="fx-field">
                <label for="currency_denomination_id">Pecahan</label>
                <select name="currency_denomination_id" id="currency_denomination_id" required disabled>
                    <option value="">Pilih variant terlebih dahulu</option>
                </select>
            </div>
            <div class="fx-field qty"><label for="stock-quantity">Qty</label><input id="stock-quantity" type="number" name="quantity" min="0" step="1" value="0" required></div>
            <button type="submit" class="fx-save">＋ Simpan Stok</button>
        </form>
    </section>
</div>

<style>
.fx-stock-page{max-width:1440px;margin:0 auto;padding:24px 28px 42px;color:#243a31}.fx-header{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:20px}.fx-eyebrow{font-size:9px;font-weight:800;letter-spacing:1.3px;color:#8a9791;margin-bottom:7px}.fx-heading-row{display:flex;align-items:center;gap:14px}.fx-heading-row h1{margin:0;color:#174d3a;font-size:25px;line-height:1.15;letter-spacing:-.3px}.fx-heading-row p{margin:6px 0 0;color:#74827c;font-size:11px}.fx-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #dcece3;border-radius:999px;background:#f5fbf8;color:#287153;font-size:9px;font-weight:750;white-space:nowrap}.fx-status i{width:6px;height:6px;border-radius:50%;background:#37a36f}.fx-date-form{display:flex;align-items:center;gap:9px}.fx-date-form label{font-size:10px;font-weight:750;color:#65756e}.fx-date-form input{height:38px;padding:0 10px;border:1px solid #dce5df;border-radius:9px;background:#fff;color:#30473d;font-size:11px;outline:none}.fx-alert{display:flex;align-items:center;gap:9px;padding:10px 13px;border-radius:9px;margin-bottom:15px;font-size:11px}.fx-alert.success{background:#edf8f2;border:1px solid #d5eddf;color:#176b50}.fx-alert.error{background:#fff2f1;border:1px solid #f1d9d6;color:#a23a32}.fx-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:17px}.fx-card{min-height:96px;display:flex;align-items:center;gap:13px;padding:16px 17px;border:1px solid #e3ebe7;border-radius:11px;background:#fff;box-shadow:0 3px 13px rgba(20,60,45,.035)}.fx-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;flex:0 0 36px;border-radius:9px;background:#eef6f2;color:#176b50;font-size:17px;font-weight:700}.fx-card small{display:block;font-size:8px;letter-spacing:.7px;font-weight:800;color:#819089}.fx-card strong{display:block;margin-top:4px;font-size:21px;line-height:1.1;color:#174d3a}.fx-card em{display:block;margin-top:4px;color:#9aa59f;font-size:9px;font-style:normal}.fx-card.value .fx-icon{background:#f8f2e5;color:#9a7733}.fx-panel{background:#fff;border:1px solid #e3ebe7;border-radius:11px;overflow:hidden;box-shadow:0 3px 13px rgba(20,60,45,.035);margin-bottom:16px}.fx-panel-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px 17px;border-bottom:1px solid #e9efeb}.fx-panel-head h2{margin:0;font-size:13px;font-weight:800;color:#214d3d}.fx-panel-head p{margin:3px 0 0;color:#8a9791;font-size:9px}.fx-count,.fx-manual{padding:5px 8px;border-radius:6px;background:#f4f7f5;color:#718078;font-size:8px;font-weight:800;letter-spacing:.4px}.fx-manual{background:#f8f2e5;color:#967536}.fx-table-wrap{overflow-x:auto}.fx-table{width:100%;border-collapse:collapse;min-width:760px}.fx-table th{padding:10px 15px;background:#f7f9f8;color:#7a8982;border-bottom:1px solid #e6ece9;text-align:left;font-size:8px;font-weight:800;letter-spacing:.65px;text-transform:uppercase}.fx-table td{padding:12px 15px;border-bottom:1px solid #edf1ef;color:#41524a;font-size:10px;vertical-align:middle}.fx-table tbody tr:last-child td{border-bottom:0}.fx-table tbody tr:hover{background:#fbfdfc}.fx-table .num{text-align:right}.fx-table td .code,.fx-table td .muted{display:block}.fx-table .code{font-size:11px;color:#174d3a}.fx-table .muted{margin-top:2px;color:#8a9791;font-size:9px;font-weight:400}.badge{display:inline-flex;padding:4px 7px;border-radius:5px;background:#f4f7f5;color:#65756e;font-size:8px;font-weight:700}.fx-empty{min-height:190px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:28px 20px;color:#87948e}.fx-empty div{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:50%;margin-bottom:9px;background:#f0f6f3;color:#6c9181;font-size:20px}.fx-empty strong{font-size:12px;color:#53645c}.fx-empty span{max-width:420px;margin-top:5px;font-size:9px;line-height:1.5}.fx-form{display:grid;grid-template-columns:1.35fr 1.35fr .55fr auto;gap:12px;padding:17px;align-items:end}.fx-field{display:flex;flex-direction:column;gap:6px}.fx-field label{font-size:9px;font-weight:800;color:#65756e}.fx-field select,.fx-field input{width:100%;height:38px;padding:0 10px;border:1px solid #dce5df;border-radius:8px;outline:0;background:#fff;color:#30473d;font-size:10px}.fx-field select:focus,.fx-field input:focus{border-color:#77a994;box-shadow:0 0 0 3px rgba(23,107,80,.08)}.fx-field select:disabled{background:#f5f7f6;color:#9ba6a1}.fx-save{height:38px;border:0;border-radius:8px;padding:0 15px;background:#176b50;color:#fff;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap}.fx-save:hover{background:#125b44}@media(max-width:1050px){.fx-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.fx-form{grid-template-columns:1fr 1fr}.fx-save{width:100%}}@media(max-width:700px){.fx-stock-page{padding:18px 14px 30px}.fx-header{align-items:stretch;flex-direction:column;gap:14px}.fx-heading-row{align-items:flex-start;flex-direction:column;gap:9px}.fx-date-form{justify-content:space-between}.fx-date-form input{flex:1}.fx-summary{grid-template-columns:1fr}.fx-form{grid-template-columns:1fr}.fx-panel-head{align-items:flex-start}.fx-manual{display:none}}
</style>

<script>
const currencyVariants = @json($currencyVariants);
const variantSelect = document.getElementById('currency_variant_id');
const denominationSelect = document.getElementById('currency_denomination_id');

variantSelect?.addEventListener('change', () => {
    const variant = currencyVariants.find((item) => String(item.id) === String(variantSelect.value));
    denominationSelect.innerHTML = '<option value="">Pilih pecahan</option>';
    denominationSelect.disabled = !variant;

    if (!variant) return;

    variant.denominations.forEach((denomination) => {
        const option = document.createElement('option');
        option.value = denomination.id;
        option.textContent = `${denomination.label} — ${denomination.type}`;
        denominationSelect.appendChild(option);
    });
});
</script>
@endsection

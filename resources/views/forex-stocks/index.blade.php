@extends('layouts.app')

@section('title', 'Stok Valas Hari Ini · MC Almara')

@section('content')
<div class="fx-stock-page">
    <div class="fx-stock-head">
        <div class="fx-stock-title">
            <div class="fx-stock-eyebrow">TREASURY / INVENTORY</div>
            <div class="fx-stock-title-row">
                <div>
                    <h1>Stok Valas Hari Ini</h1>
                    <p>Posisi stok fisik valas berdasarkan mata uang, variant, dan pecahan.</p>
                </div>
                <span class="fx-stock-status"><i></i> Operasional</span>
            </div>
        </div>
        <form method="GET" class="fx-stock-date">
            <label for="stock-date">Tanggal</label>
            <div class="fx-stock-date-input">
                <span>▣</span>
                <input id="stock-date" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="fx-alert fx-alert-success"><span>✓</span>{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="fx-alert fx-alert-error"><span>!</span>{{ $errors->first() }}</div>
    @endif

    <div class="fx-summary-grid">
        <div class="fx-summary-card">
            <div class="fx-summary-icon">◎</div>
            <div><span>MATA UANG</span><strong>{{ $summary['currencies'] }}</strong><small>currency aktif di stok</small></div>
        </div>
        <div class="fx-summary-card">
            <div class="fx-summary-icon">▤</div>
            <div><span>PECahan</span><strong>{{ $summary['denominations'] }}</strong><small>posisi pecahan tersimpan</small></div>
        </div>
        <div class="fx-summary-card">
            <div class="fx-summary-icon">#</div>
            <div><span>TOTAL UNIT</span><strong>{{ number_format($summary['units'], 0, ',', '.') }}</strong><small>lembar / keping</small></div>
        </div>
        <div class="fx-summary-card fx-summary-value">
            <div class="fx-summary-icon">¤</div>
            <div><span>NILAI NOMINAL</span><strong>{{ number_format($summary['value'], 2, ',', '.') }}</strong><small>akumulasi nominal fisik</small></div>
        </div>
    </div>

    <section class="fx-panel">
        <div class="fx-panel-head">
            <div>
                <div class="fx-panel-title">Posisi Stok</div>
                <div class="fx-panel-subtitle">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</div>
            </div>
            <div class="fx-record-count">{{ $stocks->count() }} posisi</div>
        </div>

        <div class="fx-table-wrap">
            <table class="fx-stock-table">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Variant</th>
                        <th>Jenis</th>
                        <th>Pecahan</th>
                        <th class="fx-num">Qty</th>
                        <th class="fx-num">Nilai Nominal</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($stocks as $stock)
                    <tr>
                        <td>
                            <div class="fx-currency-cell">
                                <span class="fx-currency-code">{{ $stock->variant->currency->code }}</span>
                                <span>{{ $stock->variant->currency->name }}</span>
                            </div>
                        </td>
                        <td>{{ $stock->variant->name }}</td>
                        <td><span class="fx-type-badge">{{ $stock->denomination->type_label }}</span></td>
                        <td><strong>{{ $stock->denomination->display_label }}</strong></td>
                        <td class="fx-num"><strong>{{ number_format((float) $stock->quantity, 0, ',', '.') }}</strong></td>
                        <td class="fx-num"><strong>{{ number_format((float) $stock->quantity * (float) $stock->denomination->value, 2, ',', '.') }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="fx-empty">
                                <div class="fx-empty-icon">◎</div>
                                <strong>Belum ada stok</strong>
                                <span>Belum ada posisi stok valas untuk tanggal ini. Gunakan form di bawah untuk memasukkan stok awal.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="fx-panel fx-entry-panel">
        <div class="fx-panel-head">
            <div>
                <div class="fx-panel-title">Input / Koreksi Stok</div>
                <div class="fx-panel-subtitle">Masukkan jumlah fisik per pecahan. Data tersimpan untuk tanggal dan cabang aktif.</div>
            </div>
            <span class="fx-manual-badge">MANUAL ENTRY</span>
        </div>

        <form method="POST" action="{{ route('forex-stocks.upsert') }}" class="fx-stock-form">
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

            <div class="fx-field fx-qty-field">
                <label for="stock-quantity">Qty</label>
                <input id="stock-quantity" type="number" name="quantity" min="0" step="1" value="0" required>
            </div>

            <button type="submit" class="fx-save-button"><span>＋</span> Simpan Stok</button>
        </form>
    </section>
</div>

<style>
.fx-stock-page{max-width:1440px;margin:0 auto;padding:24px 28px 42px;color:#243a31}.fx-stock-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:20px}.fx-stock-eyebrow{font-size:9px;font-weight:800;letter-spacing:1.3px;color:#8a9791;margin-bottom:7px}.fx-stock-title-row{display:flex;align-items:center;gap:15px}.fx-stock-title h1{margin:0;font-size:25px;line-height:1.15;letter-spacing:-.3px;color:#174d3a}.fx-stock-title p{margin:6px 0 0;color:#74827c;font-size:11px}.fx-stock-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #dcece3;border-radius:999px;background:#f5fbf8;color:#287153;font-size:9px;font-weight:750;white-space:nowrap}.fx-stock-status i{width:6px;height:6px;border-radius:50%;background:#37a36f}.fx-stock-date{display:flex;align-items:center;gap:9px}.fx-stock-date label{font-size:10px;font-weight:750;color:#65756e}.fx-stock-date-input{height:38px;display:flex;align-items:center;gap:8px;padding:0 10px;border:1px solid #dce5df;border-radius:9px;background:#fff;box-shadow:0 2px 8px rgba(20,60,45,.03)}.fx-stock-date-input span{font-size:12px;color:#789087}.fx-stock-date input{border:0;outline:0;background:transparent;color:#30473d;font-size:11px}.fx-alert{display:flex;align-items:center;gap:9px;padding:10px 13px;border-radius:9px;margin-bottom:15px;font-size:11px}.fx-alert span{width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-weight:800}.fx-alert-success{background:#edf8f2;border:1px solid #d5eddf;color:#176b50}.fx-alert-success span{background:#d6efdf}.fx-alert-error{background:#fff2f1;border:1px solid #f1d9d6;color:#a23a32}.fx-alert-error span{background:#f8dcd8}.fx-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:17px}.fx-summary-card{min-height:96px;display:flex;align-items:center;gap:13px;padding:16px 17px;border:1px solid #e3ebe7;border-radius:11px;background:#fff;box-shadow:0 3px 13px rgba(20,60,45,.035)}.fx-summary-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;flex:0 0 36px;border-radius:9px;background:#eef6f2;color:#176b50;font-size:17px;font-weight:700}.fx-summary-card span{display:block;font-size:8px;letter-spacing:.7px;font-weight:800;color:#819089}.fx-summary-card strong{display:block;margin-top:4px;font-size:21px;line-height:1.1;color:#174d3a;letter-spacing:-.2px}.fx-summary-card small{display:block;margin-top:4px;color:#9aa59f;font-size:9px}.fx-summary-value .fx-summary-icon{background:#f8f2e5;color:#9a7733}.fx-panel{background:#fff;border:1px solid #e3ebe7;border-radius:11px;overflow:hidden;box-shadow:0 3px 13px rgba(20,60,45,.035);margin-bottom:16px}.fx-panel-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:15px 17px;border-bottom:1px solid #e9efeb}.fx-panel-title{font-size:13px;font-weight:800;color:#214d3d}.fx-panel-subtitle{margin-top:3px;color:#8a9791;font-size:9px}.fx-record-count,.fx-manual-badge{padding:5px 8px;border-radius:6px;background:#f4f7f5;color:#718078;font-size:8px;font-weight:800;letter-spacing:.4px}.fx-manual-badge{background:#f8f2e5;color:#967536}.fx-table-wrap{overflow-x:auto}.fx-stock-table{width:100%;border-collapse:collapse;min-width:760px}.fx-stock-table th{padding:10px 15px;background:#f7f9f8;color:#7a8982;border-bottom:1px solid #e6ece9;text-align:left;font-size:8px;font-weight:800;letter-spacing:.65px;text-transform:uppercase}.fx-stock-table td{padding:12px 15px;border-bottom:1px solid #edf1ef;color:#41524a;font-size:10px;vertical-align:middle}.fx-stock-table tbody tr:last-child td{border-bottom:0}.fx-stock-table tbody tr:hover{background:#fbfdfc}.fx-num{text-align:right}.fx-currency-cell{display:flex;flex-direction:column;gap:2px}.fx-currency-code{font-size:11px;font-weight:850;color:#174d3a}.fx-currency-cell span:last-child{font-size:9px;color:#8a9791}.fx-type-badge{display:inline-flex;padding:4px 7px;border-radius:5px;background:#f4f7f5;color:#65756e;font-size:8px;font-weight:700}.fx-empty{min-height:190px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:28px 20px;color:#87948e}.fx-empty-icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:50%;margin-bottom:9px;background:#f0f6f3;color:#6c9181;font-size:20px}.fx-empty strong{font-size:12px;color:#53645c}.fx-empty span{max-width:420px;margin-top:5px;font-size:9px;line-height:1.5}.fx-entry-panel{margin-bottom:0}.fx-stock-form{display:grid;grid-template-columns:1.35fr 1.35fr .55fr auto;gap:12px;padding:17px;align-items:end}.fx-field{display:flex;flex-direction:column;gap:6px}.fx-field label{font-size:9px;font-weight:800;color:#65756e}.fx-field select,.fx-field input{width:100%;height:38px;padding:0 10px;border:1px solid #dce5df;border-radius:8px;outline:0;background:#fff;color:#30473d;font-size:10px;transition:border-color .15s,box-shadow .15s}.fx-field select:focus,.fx-field input:focus{border-color:#77a994;box-shadow:0 0 0 3px rgba(23,107,80,.08)}.fx-field select:disabled{background:#f5f7f6;color:#9ba6a1}.fx-save-button{height:38px;border:0;border-radius:8px;padding:0 15px;background:#176b50;color:#fff;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap;box-shadow:0 3px 9px rgba(23,107,80,.16);transition:background .15s,transform .15s}.fx-save-button:hover{background:#125b44;transform:translateY(-1px)}.fx-save-button span{font-size:13px;vertical-align:-1px;margin-right:3px}@media(max-width:1050px){.fx-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.fx-stock-form{grid-template-columns:1fr 1fr}.fx-save-button{width:100%}}@media(max-width:700px){.fx-stock-page{padding:18px 14px 30px}.fx-stock-head{align-items:stretch;flex-direction:column;gap:14px}.fx-stock-title-row{align-items:flex-start;flex-direction:column;gap:9px}.fx-stock-date{justify-content:space-between}.fx-stock-date-input{flex:1}.fx-summary-grid{grid-template-columns:1fr}.fx-stock-form{grid-template-columns:1fr}.fx-panel-head{align-items:flex-start}.fx-manual-badge{display:none}}
</style>

<script>
const currencyVariants = @json($currencies->flatMap(fn($currency) => $currency->variants->map(fn($variant) => [
    'id' => $variant->id,
    'denominations' => $variant->denominations->map(fn($d) => [
        'id' => $d->id,
        'label' => $d->display_label,
        'type' => $d->type_label,
    ])->values(),
]))->values());

const variantSelect = document.getElementById('currency_variant_id');
const denominationSelect = document.getElementById('currency_denomination_id');

variantSelect?.addEventListener('change', () => {
    const variant = currencyVariants.find(v => String(v.id) === String(variantSelect.value));
    denominationSelect.innerHTML = '<option value="">Pilih pecahan</option>';
    denominationSelect.disabled = !variant;

    if (variant) {
        variant.denominations.forEach(d => {
            const option = document.createElement('option');
            option.value = d.id;
            option.textContent = `${d.label} — ${d.type}`;
            denominationSelect.appendChild(option);
        });
    }
});
</script>
@endsection

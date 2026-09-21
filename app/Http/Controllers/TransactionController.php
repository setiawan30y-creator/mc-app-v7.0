<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\McTransaction;
use App\Models\McTransactionPayment;
use App\Models\RateSnapshot;
use App\Services\LedgerPostingService;
use App\Services\McTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransactionController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        $customers = Customer::query()->where('tenant_id', $user->tenant_id)->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'active'))->orderBy('full_name')->get(['id', 'customer_number', 'full_name', 'phone', 'kyc_status', 'jenis_id', 'no_ktp']);
        $currencies = Currency::query()->active()->ordered()->with(['variants' => function ($query) { $query->active()->ordered()->with(['denominations' => function ($denominationQuery) { $denominationQuery->where('is_active', true)->orderByDesc('value'); }]); }])->get();
        $rates = RateSnapshot::query()->where('tenant_id', $user->tenant_id)->active()->with(['currency:id,code,name', 'variant:id,currency_id,name,code', 'denomination:id,currency_variant_id,value,type'])->orderByDesc('effective_at')->get(['id', 'tenant_id', 'currency_id', 'currency_variant_id', 'currency_denomination_id', 'effective_at', 'buy_rate', 'sell_rate', 'is_active']);
        $bankAccounts = BankAccount::query()->where('tenant_id', $user->tenant_id)->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))->active()->with('currency:id,code,name')->orderBy('bank_name')->orderBy('account_number')->get();
        return view('transactions.create', compact('customers', 'currencies', 'rates', 'bankAccounts'));
    }

    public function customerHistory(Request $request, string $customer): JsonResponse
    {
        $user = auth()->user();
        $customerModel = Customer::query()->where('id', $customer)->where('tenant_id', $user->tenant_id)->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))->firstOrFail();
        $transactions = McTransaction::query()->where('tenant_id', $user->tenant_id)->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))->where('customer_id', $customerModel->id)->with(['items.currency:id,code,name'])->latest('transaction_date')->latest('created_at')->limit(8)->get();
        $history = $transactions->map(function (McTransaction $transaction) {
            $directions = $transaction->items->pluck('direction')->filter()->unique()->values();
            $currencies = $transaction->items->pluck('currency.code')->filter()->unique()->values();
            $total = $transaction->items->sum(fn ($item) => (float) $item->quantity * (float) $item->rate);
            return ['transaction_no' => $transaction->transaction_no, 'date' => optional($transaction->transaction_date)->format('d/m/Y H:i'), 'direction' => $directions->map(fn ($value) => strtoupper($value))->implode(' / '), 'currency' => $currencies->implode(', '), 'total' => round($total, 2), 'status' => $transaction->status];
        });
        return response()->json(['customer' => ['id' => $customerModel->id, 'name' => $customerModel->full_name, 'number' => $customerModel->customer_number, 'phone' => $customerModel->phone], 'history' => $history]);
    }

    public function store(Request $request, McTransactionService $transactionService, LedgerPostingService $ledgerPostingService)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'customer_id' => ['required', 'string'], 'transaction_date' => ['required', 'date'],
            'fund_source_type' => ['nullable', 'string', 'max:100'], 'fund_source_detail' => ['nullable', 'string', 'max:500'],
            'transaction_purpose_type' => ['nullable', 'string', 'max:100'], 'transaction_purpose_detail' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'],
            'items.*.currency_id' => ['required', 'integer', 'min:1'], 'items.*.currency_variant_id' => ['required', 'integer', 'min:1'],
            'items.*.currency_denomination_id' => ['nullable', 'integer', 'min:1'], 'items.*.direction' => ['required', 'in:buy,sell'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.rate' => ['required', 'numeric', 'gt:0'],
            'items.*.rate_snapshot_id' => ['required', 'string'], 'items.*.notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,transfer,split'], 'cash_amount' => ['nullable', 'numeric', 'min:0'], 'transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_id' => ['nullable', 'string'], 'transfer_reference' => ['nullable', 'string', 'max:150'], 'transfer_external_id' => ['nullable', 'string', 'max:150'],
            'payer_name' => ['nullable', 'string', 'max:150'], 'payment_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $transaction = DB::transaction(function () use ($validated, $transactionService, $ledgerPostingService, $user) {
                $transaction = $transactionService->create([
                    'tenant_id' => $user->tenant_id, 'branch_id' => $user->branch_id, 'customer_id' => $validated['customer_id'],
                    'transaction_date' => Carbon::parse($validated['transaction_date']), 'status' => 'pending_payment',
                    'fund_source_type' => $validated['fund_source_type'] ?? null, 'fund_source_detail' => $validated['fund_source_detail'] ?? null,
                    'transaction_purpose_type' => $validated['transaction_purpose_type'] ?? null, 'transaction_purpose_detail' => $validated['transaction_purpose_detail'] ?? null,
                    'notes' => $validated['notes'] ?? null, 'created_by' => $user->id, 'updated_by' => $user->id, 'items' => $validated['items'],
                ]);

                $transaction->load(['settlements', 'payments']);
                $idr = Currency::query()->where('code', 'IDR')->firstOrFail();
                $settlement = $transaction->settlements->first(fn ($s) => (int) $s->currency_id === (int) $idr->id);
                $required = $settlement ? (float) $settlement->amount : 0;

                if ($required > 0) {
                    $method = $validated['payment_method'] ?? null;
                    if (!$method) throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran wajib dipilih.']);
                    $cash = round((float) ($validated['cash_amount'] ?? 0), 2);
                    $transfer = round((float) ($validated['transfer_amount'] ?? 0), 2);
                    if ($method === 'cash') { $cash = $required; $transfer = 0; }
                    elseif ($method === 'transfer') { $cash = 0; $transfer = $required; }
                    if (round($cash + $transfer, 2) !== round($required, 2)) throw ValidationException::withMessages(['payment_method' => 'Total Cash + Transfer harus tepat sebesar Rp ' . number_format($required, 2, ',', '.') . '.']);
                    if ($transfer > 0) {
                        if (empty($validated['bank_account_id'])) throw ValidationException::withMessages(['bank_account_id' => 'Rekening tujuan wajib dipilih untuk transfer.']);
                        $bank = BankAccount::query()->whereKey($validated['bank_account_id'])->where('tenant_id', $user->tenant_id)->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))->active()->first();
                        if (!$bank) throw ValidationException::withMessages(['bank_account_id' => 'Rekening bank tidak ditemukan atau tidak aktif.']);
                    }
                    if ($cash > 0) {
                        $payment = $this->createPayment($transaction, $settlement, $idr, 'cash', $cash, $validated, null, $user->id);
                        $ledgerPostingService->postPayment($payment);
                    }
                    if ($transfer > 0) {
                        $payment = $this->createPayment($transaction, $settlement, $idr, 'transfer', $transfer, $validated, $validated['bank_account_id'], $user->id);
                        $ledgerPostingService->postPayment($payment);
                    }
                    $transaction->forceFill(['status' => 'paid', 'settlement_status' => 'paid', 'updated_by' => $user->id])->save();
                }
                return $transaction;
            });
            return redirect()->route('teller.index')->with('success', 'Transaksi ' . $transaction->transaction_no . ' berhasil disimpan.');
        } catch (ValidationException $e) { throw $e; }
        catch (Throwable $e) { report($e); return back()->withInput()->withErrors(['transaction' => $e->getMessage()]); }
    }

    protected function createPayment(McTransaction $trx, $settlement, Currency $idr, string $method, float $amount, array $validated, ?string $bankAccountId, int $userId): McTransactionPayment
    {
        return McTransactionPayment::query()->create([
            'id' => (string) Str::ulid(), 'transaction_id' => $trx->id, 'settlement_id' => $settlement->id, 'payment_method' => $method,
            'amount' => number_format($amount, 2, '.', ''), 'currency_id' => $idr->id, 'bank_account_id' => $bankAccountId, 'bank_mutation_id' => null,
            'transfer_reference' => $validated['transfer_reference'] ?? null, 'transfer_external_id' => $validated['transfer_external_id'] ?? null,
            'payer_name' => $validated['payer_name'] ?? null, 'payment_status' => 'confirmed', 'paid_at' => Carbon::now(), 'confirmed_at' => Carbon::now(),
            'confirmed_by' => $userId, 'notes' => $validated['payment_notes'] ?? null,
        ]);
    }
}

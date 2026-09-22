<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashInventory;
use App\Models\CashInventoryMovement;
use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\OpeningBalance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class OpeningBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $date = $request->date('date')?->toDateString() ?? Carbon::now($timezone)->toDateString();

        $startOfDay = Carbon::parse($date, $timezone)->startOfDay()->utc();
        $endOfDay = Carbon::parse($date, $timezone)->endOfDay()->utc();

        $balances = OpeningBalance::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereBetween('balance_date', [$startOfDay, $endOfDay])
            ->where('status', 'finalized')
            ->with(['currency', 'bankAccount', 'variant.currency', 'denomination'])
            ->orderBy('balance_type')
            ->orderBy('created_at')
            ->get();

        $savedSummary = [
            'cash' => (float) $balances->where('balance_type', 'cash')->sum('amount_rp'),
            'bank' => (float) $balances->where('balance_type', 'bank')->sum('amount_rp'),
            'forex' => (float) $balances->where('balance_type', 'forex')->sum('amount_rp'),
        ];
        $savedSummary['gross'] = $savedSummary['cash'] + $savedSummary['bank'] + $savedSummary['forex'];

        $currencies = Currency::query()->active()->ordered()->get();
        $banks = BankAccount::query()->active()->forTenant($user->tenant_id)->forBranch($user->branch_id)->orderBy('bank_name')->get();
        $denominations = CurrencyDenomination::query()->active()->with('variant.currency')->ordered()->get();

        return view('opening-balances.index', compact('date', 'balances', 'savedSummary', 'currencies', 'banks', 'denominations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'balance_date' => ['required', 'date'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_notes' => ['nullable', 'string', 'max:1000'],
            'banks' => ['nullable', 'array'],
            'banks.*.id' => ['nullable', 'string', 'exists:bank_accounts,id'],
            'banks.*.amount' => ['nullable', 'numeric', 'min:0'],
            'banks.*.notes' => ['nullable', 'string', 'max:500'],
            'forex' => ['nullable', 'array'],
            'forex.*.denomination_id' => ['required_with:forex.*.quantity', 'nullable', 'integer', 'exists:currency_denominations,id'],
            'forex.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'forex.*.rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $timezone = config('app.timezone', 'Asia/Jakarta');
            $localDate = Carbon::parse($validated['balance_date'], $timezone);
            $date = $localDate->copy()->startOfDay();
            $startOfDay = $date->copy()->startOfDay()->utc();
            $endOfDay = $date->copy()->endOfDay()->utc();

            $existingForexDate = OpeningBalance::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id)
                ->where('balance_type', 'forex')
                ->where('status', 'finalized')
                ->whereBetween('balance_date', [$startOfDay, $endOfDay])
                ->value('balance_date');

            if ($existingForexDate) {
                $hasPostedStock = CashInventoryMovement::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $user->branch_id)
                    ->where('movement_type', 'transaction')
                    ->whereDate('created_at', '>=', $startOfDay)
                    ->exists();

                if ($hasPostedStock) {
                    throw new RuntimeException('Saldo awal valas tidak boleh diubah setelah transaksi stok diposting. Gunakan adjustment/stock opname.');
                }
            }

            OpeningBalance::where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id)
                ->whereBetween('balance_date', [$startOfDay, $endOfDay])
                ->delete();

            $cash = (float) ($validated['cash_amount'] ?? 0);
            if ($cash > 0) {
                $idr = Currency::query()->where(function ($q) {
                    $q->whereRaw('LOWER(code) = ?', ['idr'])
                        ->orWhereRaw('LOWER(code) = ?', ['rupiah']);
                })->first();

                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'balance_date' => $date,
                    'balance_type' => 'cash',
                    'currency_id' => $idr?->id,
                    'amount_rp' => $cash,
                    'notes' => $validated['cash_notes'] ?? null,
                    'status' => 'finalized',
                    'created_by' => $user->id,
                ]);
            }

            foreach ($validated['banks'] ?? [] as $bank) {
                $amount = (float) ($bank['amount'] ?? 0);
                if ($amount <= 0 || empty($bank['id'])) {
                    continue;
                }

                $account = BankAccount::query()
                    ->where('id', $bank['id'])
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $user->branch_id)
                    ->first();

                if (!$account) {
                    continue;
                }

                $account->update(['opening_balance' => $amount]);

                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'balance_date' => $date,
                    'balance_type' => 'bank',
                    'currency_id' => $account->currency_id,
                    'bank_account_id' => $account->id,
                    'amount_rp' => $amount,
                    'notes' => $bank['notes'] ?? null,
                    'status' => 'finalized',
                    'created_by' => $user->id,
                ]);
            }

            foreach ($validated['forex'] ?? [] as $line) {
                $qty = (float) ($line['quantity'] ?? 0);
                $rate = (float) ($line['rate'] ?? 0);
                if ($qty <= 0 || empty($line['denomination_id'])) {
                    continue;
                }

                $denom = CurrencyDenomination::with('variant.currency')->find($line['denomination_id']);
                if (!$denom) {
                    continue;
                }

                $currencyId = $denom->variant?->currency_id;
                $variantId = $denom->currency_variant_id;
                $amountRp = $qty * $rate;

                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'balance_date' => $date,
                    'balance_type' => 'forex',
                    'currency_id' => $currencyId,
                    'currency_variant_id' => $variantId,
                    'currency_denomination_id' => $denom->id,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'amount_rp' => $amountRp,
                    'status' => 'finalized',
                    'created_by' => $user->id,
                ]);

                $inventory = CashInventory::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $user->branch_id)
                    ->where('currency_id', $currencyId)
                    ->where('currency_variant_id', $variantId)
                    ->where('currency_denomination_id', $denom->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    $inventory = CashInventory::query()->create([
                        'id' => (string) Str::ulid(),
                        'tenant_id' => $user->tenant_id,
                        'branch_id' => $user->branch_id,
                        'currency_id' => $currencyId,
                        'currency_variant_id' => $variantId,
                        'currency_denomination_id' => $denom->id,
                        'quantity' => 0,
                        'total_amount' => 0,
                        'status' => 'active',
                    ]);
                }

                $inventory->update([
                    'quantity' => $qty,
                    'total_amount' => $qty * (float) $denom->value,
                    'status' => 'active',
                ]);

                CashInventoryMovement::query()->create([
                    'id' => (string) Str::ulid(),
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'inventory_id' => $inventory->id,
                    'transaction_id' => null,
                    'cash_movement_id' => null,
                    'direction' => 'in',
                    'quantity' => $qty,
                    'amount' => $qty * (float) $denom->value,
                    'balance_quantity' => $qty,
                    'balance_amount' => $qty * (float) $denom->value,
                    'movement_type' => 'opening',
                    'reference' => 'OPENING-' . $date->format('Ymd'),
                    'notes' => 'Saldo awal stok valas per denominasi.',
                    'created_by' => $user->id,
                ]);
            }
        });

        return redirect()
            ->route('opening-balances.index', ['date' => $validated['balance_date']])
            ->with('success', 'Saldo awal berhasil disimpan dan stok valas siap digunakan sebagai baseline ERP.');
    }
}

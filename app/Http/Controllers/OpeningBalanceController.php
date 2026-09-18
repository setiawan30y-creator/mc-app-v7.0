<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\OpeningBalance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpeningBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->date('date')?->toDateString() ?? Carbon::today()->toDateString();

        $balances = OpeningBalance::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('balance_date', $date)
            ->with(['currency', 'bankAccount', 'variant.currency', 'denomination'])
            ->get();

        $currencies = Currency::query()->active()->ordered()->get();
        $banks = BankAccount::query()->active()->forTenant($user->tenant_id)->forBranch($user->branch_id)->orderBy('bank_name')->get();
        $denominations = CurrencyDenomination::query()->active()->with('variant.currency')->ordered()->get();

        return view('opening-balances.index', compact('date', 'balances', 'currencies', 'banks', 'denominations'));
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
            $date = $validated['balance_date'];
            OpeningBalance::where('tenant_id', $user->tenant_id)->where('branch_id', $user->branch_id)->whereDate('balance_date', $date)->delete();

            $cash = (float) ($validated['cash_amount'] ?? 0);
            if ($cash > 0) {
                $idr = Currency::query()->where(function ($q) { $q->whereRaw('LOWER(code) = ?', ['idr'])->orWhereRaw('LOWER(code) = ?', ['rupiah']); })->first();
                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id, 'branch_id' => $user->branch_id, 'balance_date' => $date,
                    'balance_type' => 'cash', 'currency_id' => $idr?->id, 'amount_rp' => $cash,
                    'notes' => $validated['cash_notes'] ?? null, 'status' => 'finalized', 'created_by' => $user->id,
                ]);
            }

            foreach ($validated['banks'] ?? [] as $bank) {
                $amount = (float) ($bank['amount'] ?? 0);
                if ($amount <= 0 || empty($bank['id'])) continue;
                $account = BankAccount::query()->where('id', $bank['id'])->where('tenant_id', $user->tenant_id)->where('branch_id', $user->branch_id)->first();
                if (!$account) continue;
                $account->update(['opening_balance' => $amount]);
                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id, 'branch_id' => $user->branch_id, 'balance_date' => $date,
                    'balance_type' => 'bank', 'currency_id' => $account->currency_id, 'bank_account_id' => $account->id,
                    'amount_rp' => $amount, 'notes' => $bank['notes'] ?? null, 'status' => 'finalized', 'created_by' => $user->id,
                ]);
            }

            foreach ($validated['forex'] ?? [] as $line) {
                $qty = (float) ($line['quantity'] ?? 0);
                $rate = (float) ($line['rate'] ?? 0);
                if ($qty <= 0 || empty($line['denomination_id'])) continue;
                $denom = CurrencyDenomination::with('variant.currency')->find($line['denomination_id']);
                if (!$denom) continue;
                OpeningBalance::create([
                    'tenant_id' => $user->tenant_id, 'branch_id' => $user->branch_id, 'balance_date' => $date,
                    'balance_type' => 'forex', 'currency_id' => $denom->variant?->currency_id,
                    'currency_variant_id' => $denom->currency_variant_id, 'currency_denomination_id' => $denom->id,
                    'quantity' => $qty, 'rate' => $rate, 'amount_rp' => $qty * $rate,
                    'status' => 'finalized', 'created_by' => $user->id,
                ]);
            }
        });

        return redirect()->route('opening-balances.index', ['date' => $validated['balance_date']])->with('success', 'Saldo awal berhasil disimpan.');
    }
}

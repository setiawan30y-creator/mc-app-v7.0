<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashMovement;
use App\Models\Currency;
use App\Models\McTransaction;
use App\Models\McTransactionPayment;
use App\Services\LedgerPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TransactionPaymentController extends Controller
{
    public function __construct(
        protected LedgerPostingService $ledgerPostingService,
    ) {
    }

    public function create(string $transaction)
    {
        $trx = $this->transaction($transaction);
        $trx->load(['customer', 'items.currency', 'settlements.currency', 'payments']);

        $idr = Currency::query()->where('code', 'IDR')->first();
        $idrSettlement = $trx->settlements->first(function ($settlement) use ($idr) {
            return $idr && (int) $settlement->currency_id === (int) $idr->id;
        });

        $paymentDirection = $idrSettlement?->direction;
        $requiredAmount = $idrSettlement ? (float) $idrSettlement->amount : 0;
        $paidAmount = (float) $trx->payments->where('payment_status', '!=', 'failed')->sum('amount');
        $remainingAmount = max(0, round($requiredAmount - $paidAmount, 2));

        $bankAccounts = BankAccount::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when(auth()->user()->branch_id, fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->active()
            ->with('currency:id,code,name')
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        return view('transactions.payment', compact(
            'trx', 'idr', 'idrSettlement', 'paymentDirection',
            'requiredAmount', 'paidAmount', 'remainingAmount', 'bankAccounts'
        ));
    }

    public function store(Request $request, string $transaction)
    {
        $trx = $this->transaction($transaction);
        $trx->load(['settlements', 'payments']);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,transfer,split'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'transfer_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_id' => ['nullable', 'string'],
            'transfer_reference' => ['nullable', 'string', 'max:150'],
            'transfer_external_id' => ['nullable', 'string', 'max:150'],
            'payer_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $idr = Currency::query()->where('code', 'IDR')->firstOrFail();
        $settlement = $trx->settlements->first(fn ($s) => (int) $s->currency_id === (int) $idr->id);
        $required = $settlement ? (float) $settlement->amount : 0;
        $alreadyPaid = (float) $trx->payments->where('payment_status', '!=', 'failed')->sum('amount');
        $remaining = max(0, round($required - $alreadyPaid, 2));

        $cash = round((float) ($validated['cash_amount'] ?? 0), 2);
        $transfer = round((float) ($validated['transfer_amount'] ?? 0), 2);

        if ($validated['payment_method'] === 'cash') {
            $cash = $remaining;
            $transfer = 0;
        } elseif ($validated['payment_method'] === 'transfer') {
            $transfer = $remaining;
            $cash = 0;
        }

        if ($cash + $transfer <= 0 && $remaining > 0) {
            throw ValidationException::withMessages(['payment' => 'Nominal pembayaran wajib diisi.']);
        }

        if (round($cash + $transfer, 2) !== $remaining) {
            throw ValidationException::withMessages([
                'payment' => 'Total Cash + Transfer harus tepat sebesar sisa pembayaran ' . number_format($remaining, 2, ',', '.') . '.',
            ]);
        }

        if ($transfer > 0) {
            if (empty($validated['bank_account_id'])) {
                throw ValidationException::withMessages(['bank_account_id' => 'Rekening tujuan wajib dipilih untuk transfer.']);
            }

            $bank = BankAccount::query()
                ->whereKey($validated['bank_account_id'])
                ->where('tenant_id', auth()->user()->tenant_id)
                ->when(auth()->user()->branch_id, fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->active()
                ->first();

            if (!$bank) {
                throw ValidationException::withMessages(['bank_account_id' => 'Rekening bank tidak ditemukan atau tidak aktif.']);
            }
        }

        if (!$settlement && $remaining > 0) {
            throw ValidationException::withMessages(['payment' => 'Settlement IDR tidak tersedia untuk transaksi ini.']);
        }

        DB::transaction(function () use ($trx, $settlement, $idr, $cash, $transfer, $validated) {
            if ($cash > 0) {
                $payment = $this->createPayment(
                    $trx,
                    $settlement,
                    $idr,
                    'cash',
                    $cash,
                    $validated,
                    null
                );

                $this->ledgerPostingService->postPayment($payment);
                $this->assertCashLedgerPosted($payment->id);
            }

            if ($transfer > 0) {
                $payment = $this->createPayment(
                    $trx,
                    $settlement,
                    $idr,
                    'transfer',
                    $transfer,
                    $validated,
                    $validated['bank_account_id']
                );

                $this->ledgerPostingService->postPayment($payment);
                $this->assertBankLedgerPosted($payment->id);
            }

            $trx->forceFill([
                'status' => 'paid',
                'settlement_status' => 'paid',
                'updated_by' => auth()->id(),
            ])->save();
        });

        return redirect()->route('teller.index')->with('success', 'Pembayaran transaksi ' . $trx->transaction_no . ' berhasil disimpan.');
    }

    protected function createPayment(
        McTransaction $trx,
        $settlement,
        Currency $idr,
        string $method,
        float $amount,
        array $validated,
        ?string $bankAccountId
    ): McTransactionPayment {
        return McTransactionPayment::query()->create([
            'id' => (string) Str::ulid(),
            'transaction_id' => $trx->id,
            'settlement_id' => $settlement->id,
            'payment_method' => $method,
            'amount' => number_format($amount, 2, '.', ''),
            'currency_id' => $idr->id,
            'bank_account_id' => $bankAccountId,
            'bank_mutation_id' => null,
            'transfer_reference' => $validated['transfer_reference'] ?? null,
            'transfer_external_id' => $validated['transfer_external_id'] ?? null,
            'payer_name' => $validated['payer_name'] ?? null,
            'payment_status' => 'confirmed',
            'paid_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
            'confirmed_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    protected function assertCashLedgerPosted(string $paymentId): void
    {
        if (!CashMovement::query()->where('payment_id', $paymentId)->exists()) {
            throw new RuntimeException('Payment cash berhasil dibuat tetapi mutasi kas tidak terbentuk. Transaksi dibatalkan untuk mencegah saldo tidak sinkron.');
        }
    }

    protected function assertBankLedgerPosted(string $paymentId): void
    {
        $payment = McTransactionPayment::query()->find($paymentId);

        if (!$payment || !$payment->bank_mutation_id) {
            throw new RuntimeException('Payment transfer berhasil dibuat tetapi mutasi bank tidak terbentuk. Transaksi dibatalkan untuk mencegah saldo tidak sinkron.');
        }
    }

    protected function transaction(string $id): McTransaction
    {
        $user = auth()->user();

        return McTransaction::query()
            ->whereKey($id)
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->firstOrFail();
    }
}

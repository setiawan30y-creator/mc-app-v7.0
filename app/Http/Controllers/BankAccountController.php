<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\BankAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $bankAccountService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $accounts = $this->bankAccountService->list(
            $user->tenant_id,
            $user->branch_id,
            false
        );

        return view('settings.bank-accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(Request $request): View
    {
        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        return view('settings.bank-accounts.create', [
            'currencies' => $currencies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:100',
            ],
            'bank_code' => [
                'nullable',
                'string',
                'max:30',
            ],
            'account_name' => [
                'required',
                'string',
                'max:150',
            ],
            'account_number' => [
                'required',
                'string',
                'max:100',
            ],
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],
            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $user = $request->user();

        $this->bankAccountService->create(
            $user->tenant_id,
            $user->branch_id,
            $validated
        );

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function show(
        Request $request,
        string $bankAccount
    ): View {
        $user = $request->user();

        $account = $this->bankAccountService->find(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        $calculatedBalance = $this->bankAccountService->calculateBalance(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        return view('settings.bank-accounts.show', [
            'account' => $account,
            'calculatedBalance' => $calculatedBalance,
        ]);
    }

    public function edit(
        Request $request,
        string $bankAccount
    ): View {
        $user = $request->user();

        $account = $this->bankAccountService->find(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        return view('settings.bank-accounts.edit', [
            'account' => $account,
            'currencies' => $currencies,
        ]);
    }

    public function update(
        Request $request,
        string $bankAccount
    ): RedirectResponse {
        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:100',
            ],
            'bank_code' => [
                'nullable',
                'string',
                'max:30',
            ],
            'account_name' => [
                'required',
                'string',
                'max:150',
            ],
            'account_number' => [
                'required',
                'string',
                'max:100',
            ],
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],
            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $user = $request->user();

        $this->bankAccountService->update(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount,
            $validated
        );

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function deactivate(
        Request $request,
        string $bankAccount
    ): RedirectResponse {
        $user = $request->user();

        $this->bankAccountService->deactivate(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil dinonaktifkan.');
    }

    public function activate(
        Request $request,
        string $bankAccount
    ): RedirectResponse {
        $user = $request->user();

        $this->bankAccountService->activate(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        return redirect()
            ->route('settings.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diaktifkan.');
    }
}
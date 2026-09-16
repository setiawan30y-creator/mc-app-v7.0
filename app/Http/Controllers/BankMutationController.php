<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Services\BankMutationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankMutationController extends Controller
{
    public function __construct(
        private readonly BankMutationService $bankMutationService
    ) {
    }

    /**
     * GLOBAL MUTASI BANK
     *
     * Menampilkan seluruh mutasi rekening
     * pada tenant + branch user yang sedang aktif.
     */
    public function globalIndex(Request $request): View
    {
        $user = $request->user();

        /*
         * Filter dari URL.
         */
        $filters = [
            'bank_account_id' => $request->input('bank_account_id'),
            'reconciliation_status'
                => $request->input('reconciliation_status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'search' => $request->input('search'),
        ];

        /*
         * Daftar rekening untuk dropdown filter.
         */
        $accounts = $this->bankMutationService->globalAccounts(
            $user->tenant_id,
            $user->branch_id
        );

        /*
         * Mutasi global.
         */
        $mutations = $this->bankMutationService->globalList(
            $user->tenant_id,
            $user->branch_id,
            $filters,
            50
        );

        /*
         * Summary per mata uang.
         *
         * Tidak mencampur IDR + USD + EUR.
         */
        $summary = $this->bankMutationService->globalSummary(
            $user->tenant_id,
            $user->branch_id,
            $filters
        );

        return view(
            'settings.bank-mutations.index',
            [
                'mutations' => $mutations,
                'accounts' => $accounts,
                'summary' => $summary,
                'filters' => $filters,
            ]
        );
    }

    /**
     * MUTASI PER REKENING
     */
    public function index(
        Request $request,
        string $bankAccount
    ): View {
        $user = $request->user();

        $account = BankAccount::query()
            ->where('id', $bankAccount)
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->with('currency')
            ->firstOrFail();

        $mutations = $this->bankMutationService->list(
            $user->tenant_id,
            $user->branch_id,
            $bankAccount
        );

        return view(
            'settings.bank-accounts.mutations.index',
            [
                'account' => $account,
                'mutations' => $mutations,
            ]
        );
    }

    /**
     * FORM TAMBAH MUTASI PER REKENING
     */
    public function create(
        Request $request,
        string $bankAccount
    ): View {
        $user = $request->user();

        $account = BankAccount::query()
            ->where('id', $bankAccount)
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->with('currency')
            ->firstOrFail();

        return view(
            'settings.bank-accounts.mutations.create',
            [
                'account' => $account,
            ]
        );
    }

    /**
     * SIMPAN MUTASI.
     */
    public function store(
        Request $request,
        string $bankAccount
    ): RedirectResponse {
        $validated = $request->validate([
            'transaction_date' => [
                'required',
                'date',
            ],

            'value_date' => [
                'nullable',
                'date',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'debit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'credit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'balance' => [
                'nullable',
                'numeric',
            ],

            'external_id' => [
                'nullable',
                'string',
                'max:150',
            ],

            'source' => [
                'nullable',
                'in:manual,import,api,bank_statement',
            ],

            'reconciliation_status' => [
                'nullable',
                'in:unmatched,matched,manual,ignored',
            ],

            'matched_transaction_id' => [
                'nullable',
                'string',
                'max:26',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $user = $request->user();

        $validated['bank_account_id'] = $bankAccount;

        $this->bankMutationService->create(
            $user->tenant_id,
            $user->branch_id,
            $validated
        );

        return redirect()
            ->route(
                'settings.bank-accounts.mutations.index',
                $bankAccount
            )
            ->with(
                'success',
                'Mutasi bank berhasil ditambahkan.'
            );
    }

    /**
     * DETAIL MUTASI.
     */
    public function show(
        Request $request,
        string $bankAccount,
        string $mutation
    ): View {
        $user = $request->user();

        $account = BankAccount::query()
            ->where('id', $bankAccount)
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->with('currency')
            ->firstOrFail();

        $mutationData = $this->bankMutationService->find(
            $user->tenant_id,
            $user->branch_id,
            $mutation
        );

        abort_unless(
            $mutationData->bank_account_id === $account->id,
            404
        );

        $calculatedBalance =
            $this->bankMutationService
                ->calculateBalanceAfterMutation(
                    $user->tenant_id,
                    $user->branch_id,
                    $mutation
                );

        return view(
            'settings.bank-accounts.mutations.show',
            [
                'account' => $account,
                'mutation' => $mutationData,
                'calculatedBalance' => $calculatedBalance,
            ]
        );
    }

    /**
     * REKONSILIASI MUTASI.
     */
    public function reconcile(
        Request $request,
        string $bankAccount,
        string $mutation
    ): RedirectResponse {
        $validated = $request->validate([
            'reconciliation_status' => [
                'required',
                'in:unmatched,matched,manual,ignored',
            ],

            'matched_transaction_id' => [
                'nullable',
                'string',
                'max:26',
            ],
        ]);

        $user = $request->user();

        $mutationData = $this->bankMutationService->find(
            $user->tenant_id,
            $user->branch_id,
            $mutation
        );

        abort_unless(
            $mutationData->bank_account_id === $bankAccount,
            404
        );

        $this->bankMutationService->reconcile(
            $user->tenant_id,
            $user->branch_id,
            $mutation,
            $validated['reconciliation_status'],
            $validated['matched_transaction_id'] ?? null
        );

        return redirect()
            ->route(
                'settings.bank-accounts.mutations.show',
                [
                    'bankAccount' => $bankAccount,
                    'mutation' => $mutation,
                ]
            )
            ->with(
                'success',
                'Status rekonsiliasi berhasil diperbarui.'
            );
    }

    /**
     * IGNORE MUTASI.
     */
    public function ignore(
        Request $request,
        string $bankAccount,
        string $mutation
    ): RedirectResponse {
        $user = $request->user();

        $mutationData = $this->bankMutationService->find(
            $user->tenant_id,
            $user->branch_id,
            $mutation
        );

        abort_unless(
            $mutationData->bank_account_id === $bankAccount,
            404
        );

        $this->bankMutationService->ignore(
            $user->tenant_id,
            $user->branch_id,
            $mutation
        );

        return redirect()
            ->route(
                'settings.bank-accounts.mutations.index',
                $bankAccount
            )
            ->with(
                'success',
                'Mutasi berhasil ditandai sebagai ignored.'
            );
    }
}
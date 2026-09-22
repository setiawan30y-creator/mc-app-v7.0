<?php

namespace App\Http\Controllers;

use App\Models\BankMutation;
use App\Models\Currency;
use App\Services\BankAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(private readonly BankAccountService $bankAccountService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $accounts = $this->bankAccountService->list($user->tenant_id, $user->branch_id, false);
        $today = now()->startOfDay();
        $accounts->load([
            'currency',
            'mutations' => fn ($query) => $query->where('transaction_date', '>=', $today)->where('transaction_date', '<', $today->copy()->addDay())->orderByDesc('transaction_date'),
        ]);

        $selectedBankAccount = $request->filled('bank_account_id')
            ? $accounts->firstWhere('id', (int) $request->integer('bank_account_id'))
            : null;

        // Total saldo hanya dijumlahkan di dalam mata uang yang sama.
        // Ini mencegah USD, EUR, dan IDR dijumlahkan menjadi angka yang tidak bermakna.
        $bankTotalsByCurrency = $accounts->where('is_active', true)
            ->groupBy(fn ($account) => $account->currency?->code ?? 'N/A')
            ->map(fn ($currencyAccounts) => [
                'code' => $currencyAccounts->first()->currency?->code ?? 'N/A',
                'name' => $currencyAccounts->first()->currency?->name ?? 'Mata Uang',
                'total' => $currencyAccounts->sum(fn ($account) => (float) $account->calculated_balance),
                'accounts' => $currencyAccounts->count(),
            ])->values();

        $mutationsQuery = BankMutation::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->with('bankAccount');

        if ($selectedBankAccount) $mutationsQuery->where('bank_account_id', $selectedBankAccount->id);
        if ($request->filled('date_from')) $mutationsQuery->whereDate('transaction_date', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $mutationsQuery->whereDate('transaction_date', '<=', $request->input('date_to'));

        $mutations = $mutationsQuery->orderByDesc('transaction_date')->orderByDesc('created_at')->paginate(50, ['*'], 'mutations_page')->withQueryString();

        return view('settings.bank-accounts.index', [
            'accounts' => $accounts,
            'bankTotalsByCurrency' => $bankTotalsByCurrency,
            'mutations' => $mutations,
            'selectedBankAccount' => $selectedBankAccount,
            'dateFrom' => $request->input('date_from'),
            'dateTo' => $request->input('date_to'),
        ]);
    }

    public function create(Request $request): View
    {
        $currencies = Currency::query()->active()->ordered()->get();
        return view('settings.bank-accounts.create', ['currencies' => $currencies]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['bank_name'=>['required','string','max:100'],'bank_code'=>['nullable','string','max:30'],'account_name'=>['required','string','max:150'],'account_number'=>['required','string','max:100'],'currency_id'=>['required','integer','exists:currencies,id'],'opening_balance'=>['nullable','numeric','min:0'],'is_active'=>['nullable','boolean'],'notes'=>['nullable','string']]);
        $user = $request->user();
        $this->bankAccountService->create($user->tenant_id, $user->branch_id, $validated);
        return redirect()->route('settings.bank-accounts.index')->with('success','Rekening bank berhasil ditambahkan.');
    }

    public function show(Request $request, string $bankAccount): View
    {
        $user = $request->user();
        $account = $this->bankAccountService->find($user->tenant_id, $user->branch_id, $bankAccount);
        $calculatedBalance = $this->bankAccountService->calculateBalance($user->tenant_id, $user->branch_id, $bankAccount);
        return view('settings.bank-accounts.show', ['account'=>$account,'calculatedBalance'=>$calculatedBalance]);
    }

    public function edit(Request $request, string $bankAccount): View
    {
        $user = $request->user();
        $account = $this->bankAccountService->find($user->tenant_id, $user->branch_id, $bankAccount);
        $currencies = Currency::query()->active()->ordered()->get();
        return view('settings.bank-accounts.edit', ['account'=>$account,'currencies'=>$currencies]);
    }

    public function update(Request $request, string $bankAccount): RedirectResponse
    {
        $validated = $request->validate(['bank_name'=>['required','string','max:100'],'bank_code'=>['nullable','string','max:30'],'account_name'=>['required','string','max:150'],'account_number'=>['required','string','max:100'],'currency_id'=>['required','integer','exists:currencies,id'],'opening_balance'=>['nullable','numeric','min:0'],'is_active'=>['nullable','boolean'],'notes'=>['nullable','string']]);
        $user = $request->user();
        $this->bankAccountService->update($user->tenant_id, $user->branch_id, $bankAccount, $validated);
        return redirect()->route('settings.bank-accounts.index')->with('success','Rekening bank berhasil diperbarui.');
    }

    public function deactivate(Request $request, string $bankAccount): RedirectResponse
    {
        $user=$request->user(); $this->bankAccountService->deactivate($user->tenant_id,$user->branch_id,$bankAccount);
        return redirect()->route('settings.bank-accounts.index')->with('success','Rekening bank berhasil dinonaktifkan.');
    }

    public function activate(Request $request, string $bankAccount): RedirectResponse
    {
        $user=$request->user(); $this->bankAccountService->activate($user->tenant_id,$user->branch_id,$bankAccount);
        return redirect()->route('settings.bank-accounts.index')->with('success','Rekening bank berhasil diaktifkan.');
    }
}

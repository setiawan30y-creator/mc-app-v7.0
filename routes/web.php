<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankMutationController;
use App\Http\Controllers\CashClosingController;
use App\Http\Controllers\CurrencyDenominationController;
use App\Http\Controllers\CurrencyVariantController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerImportController;
use App\Http\Controllers\CustomerRiskMasterController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\TenantSettingController;
use App\Http\Controllers\ComplianceThresholdRuleController;
use App\Http\Controllers\Settings\IsoCurrencyController;
use App\Http\Controllers\TellerController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth','tenant.context','branch.context'])->group(function () {
    Route::get('/', fn () => view('dashboard'))
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/teller', [TellerController::class, 'index'])->name('teller.index');

    /* Closing Operasional */
    Route::get('/closing', [CashClosingController::class, 'index'])->name('closing.index');
    Route::get('/closing/create', [CashClosingController::class, 'create'])->name('closing.create');
    Route::post('/closing', [CashClosingController::class, 'store'])->name('closing.store');
    Route::get('/closing/{closing}', [CashClosingController::class, 'show'])->name('closing.show');

    /* Customer Master */
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customer.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customer.create')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customer.create')->name('customers.store');
    Route::get('/customers/export/excel', [CustomerController::class, 'exportExcel'])->middleware('permission:customer.view')->name('customers.export.excel');
    Route::get('/customers/export/pdf', [CustomerController::class, 'exportPdf'])->middleware('permission:customer.view')->name('customers.export.pdf');
    Route::post('/customers/import/excel', [CustomerController::class, 'importExcel'])->middleware('permission:customer.create')->name('customers.import.excel');
    Route::get('/customers/whatsapp/all', [CustomerController::class, 'whatsappAll'])->middleware('permission:customer.view')->name('customers.whatsapp.all');
    Route::get('/customers/{customer}/preview', [CustomerController::class, 'preview'])->middleware('permission:customer.view')->name('customers.preview');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customer.update')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customer.update')->name('customers.update');

    /* Pengaturan Tenant */
    Route::get('/pengaturan/perusahaan', [TenantSettingController::class, 'edit'])->middleware('permission:settings.manage')->name('settings.company.edit');
    Route::put('/pengaturan/perusahaan', [TenantSettingController::class, 'update'])->middleware('permission:settings.manage')->name('settings.company.update');

    /* Master Customer - Risk Reference */
    Route::get('/pengaturan/master-customer', [CustomerRiskMasterController::class, 'index'])->middleware('permission:settings.manage')->name('settings.customer-risk.index');
    Route::post('/pengaturan/master-customer', [CustomerRiskMasterController::class, 'store'])->middleware('permission:settings.manage')->name('settings.customer-risk.store');
    Route::put('/pengaturan/master-customer/{customerRiskMaster}', [CustomerRiskMasterController::class, 'update'])->middleware('permission:settings.manage')->name('settings.customer-risk.update');
    Route::delete('/pengaturan/master-customer/{customerRiskMaster}', [CustomerRiskMasterController::class, 'destroy'])->middleware('permission:settings.manage')->name('settings.customer-risk.destroy');

    /* Master Currency Variant / Series */
    Route::get('/pengaturan/master-currency-variant', [CurrencyVariantController::class, 'index'])->middleware('permission:settings.manage')->name('settings.currency-variants.index');
    Route::post('/pengaturan/master-currency-variant', [CurrencyVariantController::class, 'store'])->middleware('permission:settings.manage')->name('settings.currency-variants.store');
    Route::put('/pengaturan/master-currency-variant/{currencyVariant}', [CurrencyVariantController::class, 'update'])->middleware('permission:settings.manage')->name('settings.currency-variants.update');
    Route::patch('/pengaturan/master-currency-variant/{currencyVariant}/toggle', [CurrencyVariantController::class, 'toggle'])->middleware('permission:settings.manage')->name('settings.currency-variants.toggle');
    Route::patch('/pengaturan/master-currency-variant/{currencyVariant}/set-default', [CurrencyVariantController::class, 'setDefault'])->middleware('permission:settings.manage')->name('settings.currency-variants.set-default');

    /* Manajemen Kurs */
    Route::get('/pengaturan/manajemen-kurs', [RateController::class, 'index'])->middleware('permission:settings.manage')->name('settings.rates.index');
    Route::post('/pengaturan/manajemen-kurs', [RateController::class, 'store'])->middleware('permission:settings.manage')->name('settings.rates.store');
    Route::put('/pengaturan/manajemen-kurs/{rateSnapshot}', [RateController::class, 'update'])->middleware('permission:settings.manage')->name('settings.rates.update');
    Route::post('/pengaturan/manajemen-kurs/sumber', [RateController::class, 'storeSource'])->middleware('permission:settings.manage')->name('settings.rates.sources.store');
    Route::put('/pengaturan/manajemen-kurs/sumber/{rateSource}', [RateController::class, 'updateSource'])->middleware('permission:settings.manage')->name('settings.rates.sources.update');
    Route::patch('/pengaturan/manajemen-kurs/sumber/{rateSource}/toggle', [RateController::class, 'toggleSource'])->middleware('permission:settings.manage')->name('settings.rates.sources.toggle');

    /* ISO 4217 Currency Selector */
    Route::get('/pengaturan/manajemen-kurs/iso-currencies', [IsoCurrencyController::class, 'index'])->middleware('permission:settings.manage')->name('settings.iso-currencies.index');
    Route::get('/pengaturan/manajemen-kurs/iso-currencies/{isoCurrency}', [IsoCurrencyController::class, 'show'])->middleware('permission:settings.manage')->name('settings.iso-currencies.show');
    Route::post('/pengaturan/manajemen-kurs/iso-currencies/{isoCurrency}/add-to-master', [IsoCurrencyController::class, 'addToMaster'])->middleware('permission:settings.manage')->name('settings.iso-currencies.add-to-master');

    /* Bank Accounts */
    Route::get('/pengaturan/bank-accounts', [BankAccountController::class, 'index'])->middleware('permission:settings.manage')->name('settings.bank-accounts.index');
    Route::get('/pengaturan/bank-accounts/create', [BankAccountController::class, 'create'])->middleware('permission:settings.manage')->name('settings.bank-accounts.create');
    Route::post('/pengaturan/bank-accounts', [BankAccountController::class, 'store'])->middleware('permission:settings.manage')->name('settings.bank-accounts.store');
    Route::get('/pengaturan/bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->middleware('permission:settings.manage')->name('settings.bank-accounts.show');
    Route::get('/pengaturan/bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])->middleware('permission:settings.manage')->name('settings.bank-accounts.edit');
    Route::put('/pengaturan/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->middleware('permission:settings.manage')->name('settings.bank-accounts.update');
    Route::patch('/pengaturan/bank-accounts/{bankAccount}/deactivate', [BankAccountController::class, 'deactivate'])->middleware('permission:settings.manage')->name('settings.bank-accounts.deactivate');
    Route::patch('/pengaturan/bank-accounts/{bankAccount}/activate', [BankAccountController::class, 'activate'])->middleware('permission:settings.manage')->name('settings.bank-accounts.activate');
    Route::get('/pengaturan/mutasi-bank', [BankMutationController::class, 'globalIndex'])->middleware('permission:settings.manage')->name('settings.bank-mutations.index');
    Route::get('/pengaturan/bank-accounts/{bankAccount}/mutations', [BankMutationController::class, 'index'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.index');
    Route::get('/pengaturan/bank-accounts/{bankAccount}/mutations/create', [BankMutationController::class, 'create'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.create');
    Route::post('/pengaturan/bank-accounts/{bankAccount}/mutations', [BankMutationController::class, 'store'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.store');
    Route::get('/pengaturan/bank-accounts/{bankAccount}/mutations/{mutation}', [BankMutationController::class, 'show'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.show');
    Route::patch('/pengaturan/bank-accounts/{bankAccount}/mutations/{mutation}/reconcile', [BankMutationController::class, 'reconcile'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.reconcile');
    Route::patch('/pengaturan/bank-accounts/{bankAccount}/mutations/{mutation}/ignore', [BankMutationController::class, 'ignore'])->middleware('permission:settings.manage')->name('settings.bank-accounts.mutations.ignore');

    /* Master Denomination */
    Route::get('/pengaturan/master-denomination', [CurrencyDenominationController::class, 'index'])->middleware('permission:settings.manage')->name('settings.denominations.index');
    Route::post('/pengaturan/master-denomination', [CurrencyDenominationController::class, 'store'])->middleware('permission:settings.manage')->name('settings.denominations.store');
    Route::put('/pengaturan/master-denomination/{currencyDenomination}', [CurrencyDenominationController::class, 'update'])->middleware('permission:settings.manage')->name('settings.denominations.update');
    Route::patch('/pengaturan/master-denomination/{currencyDenomination}/toggle', [CurrencyDenominationController::class, 'toggle'])->middleware('permission:settings.manage')->name('settings.denominations.toggle');

    /* Compliance - Transaction Threshold */
    Route::get('/pengaturan/compliance-threshold', [ComplianceThresholdRuleController::class, 'index'])->middleware('permission:settings.manage')->name('settings.compliance-threshold.index');
    Route::post('/pengaturan/compliance-threshold', [ComplianceThresholdRuleController::class, 'store'])->middleware('permission:settings.manage')->name('settings.compliance-threshold.store');
    Route::put('/pengaturan/compliance-threshold/{complianceThresholdRule}', [ComplianceThresholdRuleController::class, 'update'])->middleware('permission:settings.manage')->name('settings.compliance-threshold.update');
    Route::patch('/pengaturan/compliance-threshold/{complianceThresholdRule}/toggle', [ComplianceThresholdRuleController::class, 'toggle'])->middleware('permission:settings.manage')->name('settings.compliance-threshold.toggle');

    /* Customer Import Wizard */
    Route::get('/customers/import', [CustomerImportController::class, 'index'])->name('customers.import');
    Route::post('/customers/import/preview', [CustomerImportController::class, 'preview'])->name('customers.import.preview');
});

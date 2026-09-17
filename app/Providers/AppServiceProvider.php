<?php

namespace App\Providers;

use App\Http\Controllers\TransactionPaymentController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::guessPolicyNamesUsing(
            function (string $modelClass): string {
                return str_replace('\\Models\\', '\\Policies\\', $modelClass) . 'Policy';
            }
        );

        Route::middleware(['auth', 'tenant.context', 'branch.context'])->group(function () {
            Route::get('/teller/transaction/{transaction}/payment', [TransactionPaymentController::class, 'create'])->name('transactions.payments.create');
            Route::post('/teller/transaction/{transaction}/payment', [TransactionPaymentController::class, 'store'])->name('transactions.payments.store');
        });
    }
}

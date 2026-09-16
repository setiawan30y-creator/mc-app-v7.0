<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware([
    'auth',
    'tenant.context',
    'branch.context',
])->group(function () {

    Route::get('/', function () {
        return view('dashboard');
    })
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Customer Master
    |--------------------------------------------------------------------------
    */

Route::get('/customers', [CustomerController::class, 'index'])
    ->middleware('permission:customer.view')
    ->name('customers.index');

Route::get('/customers/create', [CustomerController::class, 'create'])
    ->middleware('permission:customer.create')
    ->name('customers.create');

Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware('permission:customer.create')
    ->name('customers.store');

Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
    ->middleware('permission:customer.update')
    ->name('customers.edit');

Route::put('/customers/{customer}', [CustomerController::class, 'update'])
    ->middleware('permission:customer.update')
    ->name('customers.update');
});
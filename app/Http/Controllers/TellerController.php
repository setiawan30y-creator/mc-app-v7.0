<?php

namespace App\Http\Controllers;

use App\Models\McTransaction;
use Illuminate\Http\Request;

class TellerController extends Controller
{
    /**
     * Teller transaction dashboard.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = auth()->user()->branch_id;

        $query = McTransaction::query()
            ->with([
                'customer',
                'items.currency',
                'items.currencyVariant',
                'settlements',
            ])
            ->where('tenant_id', $tenantId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        /*
         * Search:
         * transaction number / customer.
         */
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where(
                    'transaction_no',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery
                        ->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere(
                            'customer_number',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            '%' . $search . '%'
                        );
                });
            });
        }

        /*
         * Status filter.
         */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        /*
         * Date filter.
         */
        if ($request->filled('date')) {
            $query->whereDate(
                'transaction_date',
                $request->input('date')
            );
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('teller.index', [
            'transactions' => $transactions,
        ]);
    }
}
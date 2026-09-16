<?php

namespace App\Http\Controllers;

use App\Models\Gantungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GantunganController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Gantungan::query()->with('createdBy')
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id));

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('date')) $query->whereDate('business_date', $request->date);
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(fn ($x) => $x->where('gantungan_no', 'like', "%{$q}%")
                ->orWhere('title', 'like', "%{$q}%")
                ->orWhere('counterparty_name', 'like', "%{$q}%"));
        }

        $summaryQuery = (clone $query);
        $summary = [
            'open' => (clone $summaryQuery)->whereIn('status', ['open', 'partial'])->sum('outstanding_amount'),
            'count' => (clone $summaryQuery)->whereIn('status', ['open', 'partial'])->count(),
            'settled' => (clone $summaryQuery)->where('status', 'settled')->sum('settled_amount'),
        ];

        $gantungans = $query->latest('business_date')->latest()->paginate(20)->withQueryString();
        return view('gantungan.index', compact('gantungans', 'summary'));
    }

    public function create()
    {
        return view('gantungan.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'business_date' => ['required', 'date'],
            'category' => ['required', 'in:employee,branch,supplier,operational,other'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'counterparty_name' => ['nullable', 'string', 'max:150'],
            'reference' => ['nullable', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
        ]);

        $gantungan = DB::transaction(function () use ($data, $user) {
            $prefix = 'GNT-' . str_replace('-', '', $data['business_date']);
            $sequence = Gantungan::where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id)
                ->whereDate('business_date', $data['business_date'])->count() + 1;
            $amount = number_format((float) $data['amount'], 2, '.', '');
            return Gantungan::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'gantungan_no' => $prefix . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'business_date' => $data['business_date'],
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $amount,
                'settled_amount' => '0.00',
                'outstanding_amount' => $amount,
                'counterparty_name' => $data['counterparty_name'] ?? null,
                'reference' => $data['reference'] ?? null,
                'status' => 'open',
                'due_date' => $data['due_date'] ?? null,
                'created_by' => $user->id,
            ]);
        });

        return redirect()->route('gantungan.show', $gantungan)->with('success', 'Gantungan berhasil dibuat.');
    }

    public function show(Gantungan $gantungan)
    {
        $this->authorizeScope($gantungan);
        $gantungan->load(['settlements.createdBy', 'createdBy', 'settledBy']);
        return view('gantungan.show', compact('gantungan'));
    }

    public function settle(Request $request, Gantungan $gantungan)
    {
        $this->authorizeScope($gantungan);
        abort_unless($gantungan->isOpen(), 422, 'Gantungan sudah lunas.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,transfer,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $outstanding = (float) $gantungan->outstanding_amount;
        $settlementAmount = (float) $data['amount'];
        if ($settlementAmount > $outstanding + 0.000001) {
            return back()->withInput()->withErrors(['amount' => 'Nominal pelunasan melebihi saldo outstanding.']);
        }

        DB::transaction(function () use ($data, $gantungan, $user = null) {
            $currentUser = auth()->user();
            $amount = (float) $data['amount'];
            $newSettled = round((float) $gantungan->settled_amount + $amount, 2);
            $newOutstanding = round((float) $gantungan->amount - $newSettled, 2);

            $gantungan->settlements()->create([
                'id' => (string) Str::ulid(),
                'amount' => number_format($amount, 2, '.', ''),
                'settled_at' => now(),
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $currentUser->id,
            ]);

            $gantungan->update([
                'settled_amount' => number_format($newSettled, 2, '.', ''),
                'outstanding_amount' => number_format($newOutstanding, 2, '.', ''),
                'status' => $newOutstanding <= 0 ? 'settled' : 'partial',
                'settled_by' => $newOutstanding <= 0 ? $currentUser->id : null,
                'settled_at' => $newOutstanding <= 0 ? now() : null,
                'updated_by' => $currentUser->id,
            ]);
        });

        return back()->with('success', 'Pelunasan Gantungan berhasil dicatat.');
    }

    private function authorizeScope(Gantungan $gantungan): void
    {
        $user = auth()->user();
        abort_unless($gantungan->tenant_id === $user->tenant_id && (! $user->branch_id || $gantungan->branch_id === $user->branch_id), 403);
    }
}

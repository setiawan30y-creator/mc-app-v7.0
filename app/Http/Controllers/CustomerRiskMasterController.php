<?php

namespace App\Http\Controllers;

use App\Models\CustomerRiskMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerRiskMasterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $type = $request->input('type', 'occupation');

        if (! in_array($type, ['occupation', 'nationality'], true)) {
            $type = 'occupation';
        }

        $items = CustomerRiskMaster::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('type', $type)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('settings.customer-risk.index', [
            'items' => $items,
            'type' => $type,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(['occupation', 'nationality']),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'code' => [
                'nullable',
                'string',
                'max:30',
            ],
            'risk_level' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            'risk_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $exists = CustomerRiskMaster::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('type', $validated['type'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Data dengan nama tersebut sudah tersedia.',
                ]);
        }

        CustomerRiskMaster::create([
            'tenant_id' => $user->tenant_id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'risk_level' => $validated['risk_level'],
            'risk_score' => $validated['risk_score'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('settings.customer-risk.index', [
                'type' => $validated['type'],
            ])
            ->with('success', 'Master berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        CustomerRiskMaster $customerRiskMaster
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        abort_unless(
            $customerRiskMaster->tenant_id === $user->tenant_id,
            404
        );

        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(['occupation', 'nationality']),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'code' => [
                'nullable',
                'string',
                'max:30',
            ],
            'risk_level' => [
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
            'risk_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $exists = CustomerRiskMaster::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('type', $validated['type'])
            ->where('name', $validated['name'])
            ->whereKeyNot($customerRiskMaster->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Data dengan nama tersebut sudah tersedia.',
                ]);
        }

        $customerRiskMaster->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'risk_level' => $validated['risk_level'],
            'risk_score' => $validated['risk_score'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('settings.customer-risk.index', [
                'type' => $validated['type'],
            ])
            ->with('success', 'Master berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        CustomerRiskMaster $customerRiskMaster
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        abort_unless(
            $customerRiskMaster->tenant_id === $user->tenant_id,
            404
        );

        $customerRiskMaster->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('settings.customer-risk.index', [
                'type' => $customerRiskMaster->type,
            ])
            ->with('success', 'Master dinonaktifkan.');
    }
}

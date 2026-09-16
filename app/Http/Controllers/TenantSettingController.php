<?php

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantSettingController extends Controller
{
    /**
     * Menampilkan pengaturan profil perusahaan
     * untuk tenant yang sedang login.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $setting = TenantSetting::firstOrCreate(
            [
                'tenant_id' => $user->tenant_id,
            ],
            [
                'company_name' => $user->tenant?->name,
                'company_short_name' => $user->tenant?->code,
                'country' => 'Indonesia',
                'timezone' => 'Asia/Jakarta',
                'locale' => 'id',
                'default_currency' => 'IDR',
            ]
        );

        return view('settings.company.edit', compact('setting'));
    }

    /**
     * Menyimpan perubahan profil perusahaan.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_short_name' => ['nullable', 'string', 'max:100'],

            'idpjk' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'license_number' => ['nullable', 'string', 'max:100'],

            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],

            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:255'],

            'timezone' => ['required', 'string', 'max:100'],
            'locale' => ['required', 'string', 'max:10'],
            'default_currency' => ['required', 'string', 'max:10'],

            'receipt_header' => ['nullable', 'string'],
            'receipt_footer' => ['nullable', 'string'],
        ]);

        TenantSetting::updateOrCreate(
            [
                'tenant_id' => $user->tenant_id,
            ],
            $validated
        );

        return redirect()
            ->route('settings.company.edit')
            ->with('success', 'Profil perusahaan berhasil disimpan.');
    }
}
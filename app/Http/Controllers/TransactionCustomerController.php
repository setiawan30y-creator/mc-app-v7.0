<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionCustomerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'jenis_id' => ['nullable', 'string', 'max:30'],
            'no_ktp' => ['nullable', 'string', 'max:100'],
        ]);

        $phone = trim((string) ($validated['phone'] ?? ''));
        $noKtp = trim((string) ($validated['no_ktp'] ?? ''));

        if ($phone !== '' && Customer::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('phone', $phone)
            ->exists()) {
            return response()->json(['message' => 'Nomor HP sudah terdaftar sebagai customer.'], 422);
        }

        if ($noKtp !== '' && Customer::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('no_ktp', $noKtp)
            ->exists()) {
            return response()->json(['message' => 'Nomor identitas sudah terdaftar sebagai customer.'], 422);
        }

        do {
            $customerNumber = 'C' . now()->format('ymdHis') . strtoupper(Str::random(3));
        } while (Customer::query()->where('tenant_id', $user->tenant_id)->where('customer_number', $customerNumber)->exists());

        $customer = Customer::query()->create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
            'customer_number' => $customerNumber,
            'id_nasabah' => $customerNumber,
            'tipe' => 1,
            'full_name' => trim($validated['full_name']),
            'display_name' => trim($validated['full_name']),
            'customer_type' => 'individual',
            'phone' => $phone !== '' ? $phone : null,
            'jenis_id' => $validated['jenis_id'] ?? null,
            'no_ktp' => $noKtp !== '' ? $noKtp : null,
            'tgl_daftar' => now()->toDateString(),
            'status' => 'active',
            'kyc_status' => 'pending',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Customer berhasil ditambahkan.',
            'customer' => [
                'id' => $customer->id,
                'customer_number' => $customer->customer_number,
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
                'kyc_status' => $customer->kyc_status,
                'jenis_id' => $customer->jenis_id,
                'no_ktp' => $customer->no_ktp,
            ],
        ], 201);
    }
}

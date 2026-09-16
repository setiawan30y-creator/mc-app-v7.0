<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $customers = Customer::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($q) use ($search) {
                    $q->where('id_nasabah', 'like', "%{$search}%")
                        ->orWhere('no_cif', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('no_ktp', 'like', "%{$search}%")
                        ->orWhere('selain_ktp', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where(
                    'status',
                    $request->string('status')->toString()
                );
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $branches = Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('customers.create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'customer_type' => [
                'required',
                Rule::in(['individual', 'company']),
            ],

            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'tempat_lahir' => [
                'nullable',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'address' => [
                'required',
                'string',
            ],

            'warga_negara' => [
                'required',
                'string',
                'size:3',
            ],

            'jenis_kelamin' => [
                'nullable',
                'string',
                'max:20',
            ],

            'pekerjaan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'no_rekening' => [
                'nullable',
                'string',
                'max:100',
            ],

            'jenis_id' => [
                'required',
                'string',
                Rule::in([
                    'KTP',
                    'SIM',
                    'PASSPORT',
                    'SERTIFIKAT',
                ]),
            ],

            'no_ktp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'selain_ktp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'npwp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'local_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'document' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $tipe = $validated['customer_type'] === 'individual'
            ? 1
            : 2;

        $identityError = $this->validateIdentity(
            $tipe,
            $validated['jenis_id'],
            $validated['no_ktp'] ?? null,
            $validated['selain_ktp'] ?? null
        );

        if ($identityError !== null) {
            return back()
                ->withErrors($identityError)
                ->withInput();
        }

        if ($validated['jenis_id'] === 'KTP') {
            $validated['selain_ktp'] = null;
        } else {
            $validated['no_ktp'] = null;
        }

        $customer = DB::transaction(function () use (
            $validated,
            $user,
            $tipe,
            $request
        ) {
            $nextNumber = $this->nextCustomerNumber($user->tenant_id);

            $nextCif = $this->nextCifNumber($user->tenant_id);

            $idNasabah = 'NSB-' .
                str_pad(
                    (string) $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $customerNumber = 'CUS-' .
                str_pad(
                    (string) $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $noCif = 'AMR-' .
                str_pad(
                    (string) $nextCif,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

            $documentPath = null;

            if ($request->hasFile('document')) {
                $documentPath = $request
                    ->file('document')
                    ->store(
                        'customers/documents/' . $user->tenant_id,
                        'local'
                    );
            }

            return Customer::create([
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,

                'id_nasabah' => $idNasabah,
                'idpjk' => null,
                'tipe' => $tipe,

                'full_name' => $validated['full_name'],
                'display_name' => $validated['full_name'],

                'customer_type' => $validated['customer_type'],

                'tempat_lahir' =>
                    $validated['tempat_lahir'] ?? null,

                'birth_date' =>
                    $validated['birth_date'] ?? null,

                'address' =>
                    $validated['address'],

                'warga_negara' =>
                    strtoupper($validated['warga_negara']),

                'jenis_kelamin' =>
                    $validated['jenis_kelamin'] ?? null,

                'pekerjaan' =>
                    $validated['pekerjaan'] ?? null,

                'phone' =>
                    $validated['phone'],

                'no_rekening' =>
                    $validated['no_rekening'] ?? null,

                'jenis_id' =>
                    $validated['jenis_id'],

                'no_ktp' =>
                    $validated['no_ktp'] ?? null,

                'selain_ktp' =>
                    $validated['selain_ktp'] ?? null,

                'no_cif' =>
                    $noCif,

                'npwp' =>
                    $validated['npwp'] ?? null,

                'local_id' =>
                    $validated['local_id'] ?? null,

                'tgl_daftar' =>
                    now()->toDateString(),

                'document_path' =>
                    $documentPath,

                'customer_number' =>
                    $customerNumber,

                'status' =>
                    'active',

                'kyc_status' =>
                    'pending',

                'created_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,
            ]);
        });

        app(AuditLogService::class)->record(
            user: $user,
            action: 'CREATE',
            module: 'Customer',
            referenceType: Customer::class,
            referenceId: $customer->id,
            beforeData: null,
            afterData: $this->auditData($customer),
            reason: 'Pembuatan nasabah baru',
            request: $request,
        );

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                "Nasabah {$customer->no_cif} berhasil ditambahkan."
            );
    }

    public function edit(
        Request $request,
        Customer $customer
    ): View {
        $this->ensureCustomerScope(
            $request,
            $customer
        );

        $user = $request->user();

        $branches = Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('customers.edit', [
            'customer' => $customer,
            'branches' => $branches,
        ]);
    }

    public function update(
        Request $request,
        Customer $customer
    ): RedirectResponse {
        $this->ensureCustomerScope(
            $request,
            $customer
        );

        $user = $request->user();

        $validated = $request->validate([
            'tipe' => [
                'required',
                'integer',
                Rule::in([1, 2]),
            ],

            'idpjk' => [
                'nullable',
                'string',
                'max:100',
            ],

            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'display_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tempat_lahir' => [
                'nullable',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'address' => [
                'required',
                'string',
                'max:500',
            ],

            'warga_negara' => [
                'required',
                'string',
                'max:100',
            ],

            'jenis_kelamin' => [
                'nullable',
                'string',
                'max:20',
            ],

            'pekerjaan' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'no_rekening' => [
                'nullable',
                'string',
                'max:100',
            ],

            'jenis_id' => [
                'required',
                'string',
                Rule::in([
                    'KTP',
                    'SIM',
                    'PASSPORT',
                    'SERTIFIKAT',
                ]),
            ],

            'no_ktp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'selain_ktp' => [
                'nullable',
                'string',
                'max:255',
            ],

            'npwp' => [
                'nullable',
                'string',
                'max:100',
            ],

            'local_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tgl_daftar' => [
                'required',
                'date',
            ],

            'document' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],

            'camera_document' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $tipe = (int) $validated['tipe'];

        $identityError = $this->validateIdentity(
            $tipe,
            $validated['jenis_id'],
            $validated['no_ktp'] ?? null,
            $validated['selain_ktp'] ?? null
        );

        if ($identityError !== null) {
            return back()
                ->withErrors($identityError)
                ->withInput();
        }

        if ($validated['jenis_id'] === 'KTP') {
            $validated['selain_ktp'] = null;
        } else {
            $validated['no_ktp'] = null;
        }

        $beforeData = $this->auditData($customer);

        $oldDocumentPath = $customer->document_path;
        $newDocumentPath = null;

        DB::transaction(function () use (
            $customer,
            $validated,
            $user,
            $tipe,
            $request,
            &$newDocumentPath
        ) {
            $documentFile = $request->file('document')
                ?? $request->file('camera_document');

            if ($documentFile !== null) {
                $newDocumentPath = $documentFile->store(
                    'customers/documents/' . $user->tenant_id,
                    'local'
                );
            }

            $customer->fill([
                /*
                 * ID permanen tidak disentuh.
                 */
                'idpjk' =>
                    $validated['idpjk'] ?? null,

                'tipe' => $tipe,

                'full_name' =>
                    $validated['full_name'],

                'display_name' =>
                    $validated['display_name'] ?? $validated['full_name'],

                'customer_type' =>
                    $tipe === 1 ? 'individual' : 'company',

                'tempat_lahir' =>
                    $validated['tempat_lahir'] ?? null,

                'birth_date' =>
                    $validated['birth_date'] ?? null,

                'address' =>
                    $validated['address'],

                'warga_negara' =>
                    strtoupper($validated['warga_negara']),

                'jenis_kelamin' =>
                    $validated['jenis_kelamin'] ?? null,

                'pekerjaan' =>
                    $validated['pekerjaan'] ?? null,

                'phone' =>
                    $validated['phone'],

                'no_rekening' =>
                    $validated['no_rekening'] ?? null,

                'jenis_id' =>
                    $validated['jenis_id'],

                'no_ktp' =>
                    $validated['no_ktp'] ?? null,

                'selain_ktp' =>
                    $validated['selain_ktp'] ?? null,

                'npwp' =>
                    $validated['npwp'] ?? null,

                'local_id' =>
                    $validated['local_id'] ?? null,

                'tgl_daftar' =>
                    $validated['tgl_daftar'],

                'updated_by' =>
                    $user->id,
            ]);

            if ($newDocumentPath !== null) {
                $customer->document_path =
                    $newDocumentPath;
            }

            $customer->save();
        });

        /*
         * Hapus dokumen lama setelah transaksi berhasil.
         */
        if (
            $newDocumentPath !== null &&
            $oldDocumentPath !== null &&
            $oldDocumentPath !== $newDocumentPath &&
            Storage::disk('local')->exists($oldDocumentPath)
        ) {
            Storage::disk('local')->delete(
                $oldDocumentPath
            );
        }

        $customer->refresh();

        $afterData = $this->auditData($customer);

        app(AuditLogService::class)->record(
            user: $user,
            action: 'UPDATE',
            module: 'Customer',
            referenceType: Customer::class,
            referenceId: $customer->id,
            beforeData: $beforeData,
            afterData: $afterData,
            reason:
                $validated['reason']
                ?? 'Perubahan data nasabah',
            request: $request,
        );

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                "Nasabah {$customer->no_cif} berhasil diperbarui."
            );
    }

    /*
     * ============================================================
     * HELPER
     * ============================================================
     */

    private function ensureCustomerScope(
        Request $request,
        Customer $customer
    ): void {
        $user = $request->user();

        abort_unless(
            $customer->tenant_id === $user->tenant_id &&
            $customer->branch_id === $user->branch_id,
            404
        );
    }

    private function validateIdentity(
        int $tipe,
        string $jenisId,
        ?string $noKtp,
        ?string $selainKtp
    ): ?array {
        if (
            $tipe === 1 &&
            ! in_array(
                $jenisId,
                [
                    'KTP',
                    'SIM',
                    'PASSPORT',
                ],
                true
            )
        ) {
            return [
                'jenis_id' =>
                    'Jenis ID tidak sesuai untuk nasabah perorangan.',
            ];
        }

        if (
            $tipe === 2 &&
            $jenisId !== 'SERTIFIKAT'
        ) {
            return [
                'jenis_id' =>
                    'Nasabah perusahaan wajib menggunakan SERTIFIKAT.',
            ];
        }

        if ($jenisId === 'KTP' && empty($noKtp)) {
            return [
                'no_ktp' =>
                    'Nomor KTP wajib diisi.',
            ];
        }

        if ($jenisId !== 'KTP' && empty($selainKtp)) {
            return [
                'selain_ktp' =>
                    'Nomor identitas wajib diisi.',
            ];
        }

        return null;
    }

    private function nextCustomerNumber(
        string $tenantId
    ): int {
        $lastCustomer = Customer::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('id_nasabah')
            ->orderByDesc('id_nasabah')
            ->lockForUpdate()
            ->first();

        if (
            $lastCustomer?->id_nasabah &&
            preg_match(
                '/(\d+)$/',
                $lastCustomer->id_nasabah,
                $matches
            )
        ) {
            return ((int) $matches[1]) + 1;
        }

        return 1;
    }

    private function nextCifNumber(
        string $tenantId
    ): int {
        $lastCif = Customer::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('no_cif')
            ->orderByDesc('no_cif')
            ->lockForUpdate()
            ->first();

        if (
            $lastCif?->no_cif &&
            preg_match(
                '/(\d+)$/',
                $lastCif->no_cif,
                $matches
            )
        ) {
            return ((int) $matches[1]) + 1;
        }

        return 1;
    }

    private function auditData(
        Customer $customer
    ): array {
        return [
            'id_nasabah' =>
                $customer->id_nasabah,

            'idpjk' =>
                $customer->idpjk,

            'customer_number' =>
                $customer->customer_number,

            'full_name' =>
                $customer->full_name,

            'tempat_lahir' =>
                $customer->tempat_lahir,

            'birth_date' =>
                $customer->birth_date?->format('Y-m-d'),

            'address' =>
                $customer->address,

            'warga_negara' =>
                $customer->warga_negara,

            'jenis_kelamin' =>
                $customer->jenis_kelamin,

            'pekerjaan' =>
                $customer->pekerjaan,

            'phone' =>
                $customer->phone,

            'no_rekening' =>
                $customer->no_rekening,

            'jenis_id' =>
                $customer->jenis_id,

            'no_ktp' =>
                $customer->no_ktp,

            'selain_ktp' =>
                $customer->selain_ktp,

            'no_cif' =>
                $customer->no_cif,

            'npwp' =>
                $customer->npwp,

            'local_id' =>
                $customer->local_id,

            'tgl_daftar' =>
                $customer->tgl_daftar?->format('Y-m-d'),

            'document_path' =>
                $customer->document_path,

            'tipe' =>
                $customer->tipe,

            'customer_type' =>
                $customer->customer_type,

            'status' =>
                $customer->status,

            'kyc_status' =>
                $customer->kyc_status,
        ];
    }
}
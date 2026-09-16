<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerRiskMaster;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filterColumns = [
            'id_nasabah', 'idpjk', 'customer_number', 'full_name',
            'tempat_lahir', 'birth_date', 'address', 'warga_negara',
            'jenis_kelamin', 'pekerjaan', 'phone', 'no_rekening',
            'jenis_id', 'no_ktp', 'selain_ktp', 'no_cif', 'npwp',
            'local_id', 'tgl_daftar',
        ];

        $operators = ['contains', 'equals', 'starts_with', 'ends_with'];

        $customersQuery = Customer::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id);

        $filters = $request->input('filters', []);

        if (is_array($filters)) {
            foreach ($filters as $filter) {
                if (!is_array($filter)) {
                    continue;
                }

                $column = $filter['column'] ?? null;
                $operator = $filter['operator'] ?? 'contains';
                $value = trim((string) ($filter['value'] ?? ''));

                if (!in_array($column, $filterColumns, true) || !in_array($operator, $operators, true) || $value === '') {
                    continue;
                }

                $customersQuery->where(function ($query) use ($column, $operator, $value) {
                    $value = $column === 'birth_date' || $column === 'tgl_daftar'
                        ? $value
                        : $value;

                    match ($operator) {
                        'equals' => $query->where($column, $value),
                        'starts_with' => $query->where($column, 'like', $value . '%'),
                        'ends_with' => $query->where($column, 'like', '%' . $value),
                        default => $query->where($column, 'like', '%' . $value . '%'),
                    };
                });
            }
        }

        $customers = $customersQuery
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $riskMasters = CustomerRiskMaster::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->whereIn('type', ['occupation', 'nationality'])
            ->get()
            ->groupBy('type');

        $occupationRisks = $riskMasters
            ->get('occupation', collect())
            ->keyBy(fn ($item) => mb_strtolower(trim($item->name)));

        $nationalityRisks = $riskMasters
            ->get('nationality', collect())
            ->keyBy(fn ($item) => mb_strtolower(trim($item->name)));

        foreach ($customers as $customer) {
            $occupationKey = mb_strtolower(trim((string) $customer->pekerjaan));
            $nationalityKey = mb_strtolower(trim((string) $customer->warga_negara));

            $customer->occupation_risk = $occupationRisks->get($occupationKey);
            $customer->nationality_risk = $nationalityRisks->get($nationalityKey);

            $risks = collect([
                $customer->occupation_risk,
                $customer->nationality_risk,
            ])->filter();

            $customer->overall_risk = $risks
                ->sortByDesc('risk_score')
                ->first();
        }

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }

    public function preview(Request $request, Customer $customer): View
    {
        $this->ensureCustomerScope($request, $customer);

        return view('customers.preview', [
            'customer' => $customer,
        ]);
    }

    public function whatsappAll(Request $request): View
    {
        $customers = $this->customerExportQuery($request)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        return view('customers.whatsapp-all', [
            'customers' => $customers,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $customers = $this->customerExportQuery($request)->get();

        $headers = $this->customerHeaders();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Nasabah');

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1) . "1", $header);
        }

        $row = 2;
        foreach ($customers as $customer) {
            foreach ($this->customerExportRow($customer) as $index => $value) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1) . $row, $value);
            }
            $row++;
        }

        foreach (range(1, count($headers)) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $filename = 'master-nasabah-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            static function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportPdf(Request $request)
    {
        $customers = $this->customerExportQuery($request)->get();

        $pdf = Pdf::loadView('customers.export-pdf', [
            'customers' => $customers,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('master-nasabah-' . now()->format('Ymd-His') . '.pdf');
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480',
            ],
        ]);

        $user = $request->user();
        $path = $request->file('excel_file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'File Excel tidak memiliki data.');
        }

        $headers = $this->customerHeaders();
        $headerMap = [];
        foreach ($rows[1] as $column => $header) {
            $normalized = strtoupper(trim((string) $header));
            if ($normalized !== '') {
                $headerMap[$normalized] = $column;
            }
        }

        $requiredHeaders = ['NAMA'];
        foreach ($requiredHeaders as $required) {
            if (!isset($headerMap[$required])) {
                return back()->with('error', "Kolom {$required} wajib ada pada file Excel.");
            }
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $rows,
            $headerMap,
            $user,
            &$created,
            &$skipped,
            $request
        ) {
            foreach (array_slice($rows, 1) as $row) {
                $fullName = trim((string) ($row[$headerMap['NAMA']] ?? ''));

                if ($fullName === '') {
                    $skipped++;
                    continue;
                }

                $phone = $this->importValue($row, $headerMap, 'NO_HP');
                $noKtp = $this->importValue($row, $headerMap, 'NO_KTP');
                $noCif = $this->importValue($row, $headerMap, 'NO_CIF');

                $duplicate = Customer::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $user->branch_id)
                    ->where(function ($query) use ($phone, $noKtp, $noCif) {
                        if ($phone) {
                            $query->orWhere('phone', $phone);
                        }
                        if ($noKtp) {
                            $query->orWhere('no_ktp', $noKtp);
                        }
                        if ($noCif) {
                            $query->orWhere('no_cif', $noCif);
                        }
                    })
                    ->exists();

                if ($duplicate) {
                    $skipped++;
                    continue;
                }

                $nextNumber = $this->nextCustomerNumber($user->tenant_id);
                $nextCif = $this->nextCifNumber($user->tenant_id);

                $customer = Customer::create([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'id_nasabah' => 'NSB-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT),
                    'idpjk' => $this->importValue($row, $headerMap, 'IDPJK'),
                    'tipe' => 1,
                    'full_name' => $fullName,
                    'display_name' => $fullName,
                    'customer_type' => 'individual',
                    'tempat_lahir' => $this->importValue($row, $headerMap, 'TEMPAT_LAHIR'),
                    'birth_date' => $this->importDate($row, $headerMap, 'TANGGAL_LAHIR'),
                    'address' => $this->importValue($row, $headerMap, 'ALAMAT') ?? '-',
                    'warga_negara' => strtoupper($this->importValue($row, $headerMap, 'WARGA_NEGARA') ?? 'IDN'),
                    'jenis_kelamin' => $this->importValue($row, $headerMap, 'JENIS_KELAMIN'),
                    'pekerjaan' => $this->importValue($row, $headerMap, 'PEKERJAAN'),
                    'phone' => $phone ?? '-',
                    'no_rekening' => $this->importValue($row, $headerMap, 'NO_REKENING'),
                    'jenis_id' => $this->importValue($row, $headerMap, 'JENIS_ID') ?: 'KTP',
                    'no_ktp' => $noKtp,
                    'selain_ktp' => $this->importValue($row, $headerMap, 'SELAIN_KTP'),
                    'no_cif' => $noCif ?: 'AMR-' . str_pad((string) $nextCif, 5, '0', STR_PAD_LEFT),
                    'npwp' => $this->importValue($row, $headerMap, 'NPWP'),
                    'local_id' => $this->importValue($row, $headerMap, 'LOCAL_ID'),
                    'tgl_daftar' => $this->importDate($row, $headerMap, 'TGL_DAFTAR') ?: now()->toDateString(),
                    'status' => 'active',
                    'kyc_status' => 'pending',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'customer_number' => 'CUS-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT),
                ]);

                app(AuditLogService::class)->record(
                    user: $user,
                    action: 'IMPORT',
                    module: 'Customer',
                    referenceType: Customer::class,
                    referenceId: $customer->id,
                    beforeData: null,
                    afterData: $this->auditData($customer),
                    reason: 'Import master nasabah dari Excel',
                    request: $request
                );

                $created++;
            }
        });

        return redirect()
            ->route('customers.index')
            ->with('success', "Import selesai. {$created} nasabah ditambahkan, {$skipped} baris dilewati.");
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $branches = Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $riskMasters = CustomerRiskMaster::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->whereIn('type', ['occupation', 'nationality'])
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        $occupations = $riskMasters->get('occupation', collect());
        $nationalities = $riskMasters->get('nationality', collect());

        return view('customers.create', [
            'branches' => $branches,
            'occupations' => $occupations,
            'nationalities' => $nationalities,
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
                $documentFile = $request->file('document');

                if (!$documentFile->isValid()) {
                    throw new \RuntimeException(
                        'File dokumen identitas tidak valid: ' . $documentFile->getErrorMessage()
                    );
                }

                $documentPath = $documentFile->store(
                    'customers/documents/' . $user->tenant_id,
                    'local'
                );

                if (!$documentPath) {
                    throw new \RuntimeException(
                        'Dokumen identitas gagal disimpan ke storage.'
                    );
                }
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

    private function customerExportQuery(Request $request)
    {
        $user = $request->user();

        $query = Customer::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id);

        $filters = $request->input('filters', []);
        $columns = [
            'id_nasabah', 'idpjk', 'customer_number', 'full_name',
            'tempat_lahir', 'birth_date', 'address', 'warga_negara',
            'jenis_kelamin', 'pekerjaan', 'phone', 'no_rekening',
            'jenis_id', 'no_ktp', 'selain_ktp', 'no_cif', 'npwp',
            'local_id', 'tgl_daftar',
        ];

        $operators = ['contains', 'equals', 'starts_with', 'ends_with'];

        if (is_array($filters)) {
            foreach ($filters as $filter) {
                if (!is_array($filter)) {
                    continue;
                }

                $column = $filter['column'] ?? null;
                $operator = $filter['operator'] ?? 'contains';
                $value = trim((string) ($filter['value'] ?? ''));

                if ($value === '' || !in_array($column, $columns, true) || !in_array($operator, $operators, true)) {
                    continue;
                }

                match ($operator) {
                    'equals' => $query->where($column, $value),
                    'starts_with' => $query->where($column, 'like', $value . '%'),
                    'ends_with' => $query->where($column, 'like', '%' . $value),
                    default => $query->where($column, 'like', '%' . $value . '%'),
                };
            }
        }

        return $query->latest();
    }

    private function customerHeaders(): array
    {
        return [
            'ID_Nasabah', 'IDPJK', 'Kode Nasabah', 'Nama', 'Tempat_Lahir',
            'Tanggal_Lahir', 'Alamat', 'Warga_Negara', 'Jenis_Kelamin',
            'Pekerjaan', 'No_HP', 'No_Rekening', 'Jenis ID', 'No_KTP',
            'Selain_KTP', 'No_CIF', 'NPWP', 'Local_ID', 'Tgl_Daftar',
        ];
    }

    private function customerExportRow(Customer $customer): array
{
    return [
        $customer->id_nasabah,
        $customer->idpjk,
        $customer->customer_number,
        $customer->full_name,
        $customer->tempat_lahir,
        $customer->birth_date?->format('d M Y'),
        $customer->address,
        $customer->warga_negara,
        match ($customer->jenis_kelamin) {
            'L' => 'Laki-Laki',
            'P' => 'Perempuan',
            default => $customer->jenis_kelamin ?: '-',
        },
        $customer->pekerjaan,
        $customer->phone,
        $customer->no_rekening,
        $customer->jenis_id_label,
        $customer->no_ktp,
        $customer->selain_ktp,
        $customer->no_cif,
        $customer->npwp,
        $customer->local_id,
        $customer->tgl_daftar?->format('d/m/Y'),
    ];
}

    private function importValue(array $row, array $headerMap, string $header): ?string
    {
        $column = $headerMap[$header] ?? null;

        if ($column === null) {
            return null;
        }

        $value = trim((string) ($row[$column] ?? ''));

        return $value === '' ? null : $value;
    }

    private function importDate(array $row, array $headerMap, string $header): ?string
    {
        $value = $this->importValue($row, $headerMap, $header);

        if (!$value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

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
            ->whereNotNull('customer_number')
            ->orderByDesc('customer_number')
            ->lockForUpdate()
            ->first();

        if (
            $lastCustomer?->customer_number &&
            preg_match(
                '/(\d+)$/',
                $lastCustomer->customer_number,
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
                match ($customer->jenis_kelamin) { 'L' => 'Laki-Laki', 'P' => 'Perempuan', default => $customer->jenis_kelamin ?: '-' },

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










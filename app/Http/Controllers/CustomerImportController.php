<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustomerImportController extends Controller
{
    /**
     * Halaman upload import customer.
     */
    public function index()
    {
        return view('customers.import');
    }

    /**
     * Membaca Excel dan menampilkan preview.
     *
     * PENTING:
     * Method ini TIDAK menyimpan data ke database.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480',
            ],
        ]);

        $file = $request->file('excel_file');

        $spreadsheet = IOFactory::load($file->getRealPath());

        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return back()->withErrors([
                'excel_file' => 'File Excel kosong.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $headerRow = array_shift($rows);

        $headers = [];

        foreach ($headerRow as $column => $header) {
            $headers[$column] = $this->normalizeHeader($header);
        }

        /*
        |--------------------------------------------------------------------------
        | Required Header
        |--------------------------------------------------------------------------
        */

        if (!in_array('NAMA', $headers, true)) {
            return back()->withErrors([
                'excel_file' => 'Kolom NAMA wajib tersedia di file Excel.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Mapping
        |--------------------------------------------------------------------------
        */

        $previewRows = [];

        $validCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {

            $excelRow = $index + 2;

            $data = [];

            foreach ($headers as $column => $header) {
                if ($header === '') {
                    continue;
                }

                $data[$header] = isset($row[$column])
                    ? trim((string) $row[$column])
                    : null;
            }

            /*
            |--------------------------------------------------------------------------
            | Skip baris benar-benar kosong
            |--------------------------------------------------------------------------
            */

            $hasValue = false;

            foreach ($data as $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $hasValue = true;
                    break;
                }
            }

            if (!$hasValue) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Basic validation
            |--------------------------------------------------------------------------
            */

            $errors = [];

            $name = trim((string) ($data['NAMA'] ?? ''));

            if ($name === '') {
                $errors[] = 'Nama wajib diisi.';
            }

            $phone = $this->nullableValue($data['NO_HP'] ?? null);
            $ktp = $this->nullableValue($data['NO_KTP'] ?? null);
            $cif = $this->nullableValue($data['NO_CIF'] ?? null);

            /*
            |--------------------------------------------------------------------------
            | Duplicate terhadap database
            |--------------------------------------------------------------------------
            */

            $duplicate = null;

            $query = Customer::query()
                ->where('tenant_id', Auth::user()->tenant_id)
                ->where('branch_id', Auth::user()->branch_id)
                ->where(function ($q) use ($phone, $ktp, $cif) {

                    $hasCondition = false;

                    if ($phone) {
                        $q->where('phone', $phone);
                        $hasCondition = true;
                    }

                    if ($ktp) {
                        if ($hasCondition) {
                            $q->orWhere('no_ktp', $ktp);
                        } else {
                            $q->where('no_ktp', $ktp);
                            $hasCondition = true;
                        }
                    }

                    if ($cif) {
                        if ($hasCondition) {
                            $q->orWhere('no_cif', $cif);
                        } else {
                            $q->where('no_cif', $cif);
                        }
                    }
                });

            if (($phone || $ktp || $cif)) {
                $duplicate = $query->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            if (!empty($errors)) {
                $status = 'error';
                $errorCount++;
            } elseif ($duplicate) {
                $status = 'duplicate';
                $duplicateCount++;
            } else {
                $status = 'valid';
                $validCount++;
            }

            $previewRows[] = [
                'row' => $excelRow,
                'name' => $name,
                'phone' => $phone,
                'ktp' => $ktp,
                'cif' => $cif,
                'status' => $status,
                'errors' => $errors,
                'duplicate' => $duplicate ? [
                    'id' => $duplicate->id,
                    'customer_number' => $duplicate->customer_number,
                    'name' => $duplicate->full_name,
                ] : null,
                'raw' => $data,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan data sementara di session
        |--------------------------------------------------------------------------
        */

        session([
            'customer_import_preview' => [
                'headers' => array_values(array_unique($headers)),
                'rows' => $previewRows,
                'total' => count($previewRows),
                'valid' => $validCount,
                'duplicate' => $duplicateCount,
                'error' => $errorCount,
            ],
        ]);

        return view('customers.import-preview', [
            'headers' => array_values(array_unique($headers)),
            'rows' => $previewRows,
            'total' => count($previewRows),
            'validCount' => $validCount,
            'duplicateCount' => $duplicateCount,
            'errorCount' => $errorCount,
        ]);
    }

    /**
     * Normalisasi header Excel.
     */
    private function normalizeHeader($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = Str::upper($value);

        $value = str_replace(
            [
                ' ',
                '-',
                '.',
                '/',
            ],
            '_',
            $value
        );

        $value = preg_replace('/_+/', '_', $value);

        return trim($value, '_');
    }

    /**
     * Nilai kosong menjadi null.
     */
    private function nullableValue($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
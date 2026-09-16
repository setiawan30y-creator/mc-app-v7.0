<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Master Nasabah</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; }
        h2 { font-size: 13px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e8f1eb; font-weight: bold; }
        th, td { border: 0.5px solid #999; padding: 3px; vertical-align: top; }
    </style>
</head>
<body>
    <h2>Master Data Nasabah</h2>
    <table>
        <thead>
            <tr>
                @foreach([
                    'ID_Nasabah','IDPJK','Kode Nasabah','Nama','Tempat_Lahir',
                    'Tanggal_Lahir','Alamat','Warga_Negara','Jenis_Kelamin',
                    'Pekerjaan','No_HP','No_Rekening','Jenis ID','No_KTP',
                    'Selain_KTP','No_CIF','NPWP','Local_ID','Tgl_Daftar'
                ] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    @foreach([
                        $customer->id_nasabah,
                        $customer->idpjk,
                        $customer->customer_number,
                        $customer->full_name,
                        $customer->tempat_lahir,
                        $customer\->birth_date?->format('d M Y'),
                        $customer->address,
                        $customer->warga_negara,
                        $customermatch(\->jenis_kelamin) { 'L' => 'Laki-Laki', 'P' => 'Perempuan', default => \->jenis_kelamin ?: '-' },
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
                    ] as $value)
                        <td>{{ $value ?: '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

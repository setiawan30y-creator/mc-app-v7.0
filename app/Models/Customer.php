<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasUlids;

    protected $fillable = [
        'tenant_id',
        'branch_id',

        // Identitas internal
        'customer_number',
        'id_nasabah',
        'idpjk',
        'tipe',

        // Data nasabah
        'full_name',
        'display_name',
        'customer_type',
        'tempat_lahir',
        'birth_date',
        'address',
        'warga_negara',
        'jenis_kelamin',
        'pekerjaan',
        'phone',

        // Data rekening & identitas
        'no_rekening',
        'jenis_id',
        'no_ktp',
        'selain_ktp',
        'no_cif',
        'npwp',
        'local_id',

        // Pendaftaran
        'tgl_daftar',

        // Status
        'status',
        'kyc_status',

        // Dokumen
        'document_path',

        // Audit
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tipe' => 'integer',
        'birth_date' => 'date',
        'tgl_daftar' => 'date',
    ];

    /**
     * Tenant pemilik nasabah.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Cabang tempat nasabah terdaftar.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * User yang membuat data nasabah.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User yang terakhir mengubah data nasabah.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Apakah nasabah adalah perorangan?
     */
    public function isIndividual(): bool
    {
        return $this->tipe === 1;
    }

    /**
     * Apakah nasabah adalah perusahaan?
     */
    public function isCompany(): bool
    {
        return $this->tipe === 2;
    }

    /**
     * Nama tipe nasabah untuk tampilan.
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            1 => 'Perorangan',
            2 => 'Perusahaan',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Nama jenis ID untuk tampilan.
     */
    public function getJenisIdLabelAttribute(): string
    {
        return match ($this->jenis_id) {
            'KTP' => 'KTP',
            'SIM' => 'SIM',
            'PASSPORT' => 'Passport',
            'SERTIFIKAT' => 'Sertifikat',
            default => '-',
        };
    }
}
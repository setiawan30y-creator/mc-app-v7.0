<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    protected $fillable = [
        'tenant_id',

        // Profil perusahaan
        'company_name',
        'company_short_name',

        // Legalitas
        'idpjk',
        'npwp',
        'license_number',

        // Alamat
        'address',
        'city',
        'province',
        'postal_code',
        'country',

        // Kontak
        'phone',
        'email',
        'website',

        // Logo
        'logo_path',

        // Sistem
        'timezone',
        'locale',
        'default_currency',

        // Struk
        'receipt_header',
        'receipt_footer',
    ];

    /**
     * Tenant pemilik setting ini.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
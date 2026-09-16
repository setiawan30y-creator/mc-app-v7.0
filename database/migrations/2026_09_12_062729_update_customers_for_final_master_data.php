<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            /*
             * ID Nasabah
             * Nomor internal unik dalam tenant.
             */
            $table->string('id_nasabah', 50)
                ->nullable()
                ->after('id');

            /*
             * IDPJK
             * Untuk sementara nullable karena sumbernya nanti
             * akan berasal dari profil perusahaan/tenant.
             */
            $table->string('idpjk', 100)
                ->nullable()
                ->after('tenant_id');

            /*
             * Tipe Nasabah
             *
             * 1 = Perorangan
             * 2 = Perusahaan
             */
            $table->unsignedTinyInteger('tipe')
                ->default(1)
                ->after('idpjk');

            /*
             * Tempat lahir.
             */
            $table->string('tempat_lahir', 100)
                ->nullable()
                ->after('full_name');

            /*
             * Warga Negara.
             */
            $table->string('warga_negara', 3)
                ->nullable()
                ->after('birth_date');

            /*
             * Jenis kelamin.
             */
            $table->string('jenis_kelamin', 20)
                ->nullable()
                ->after('warga_negara');

            /*
             * Pekerjaan.
             */
            $table->string('pekerjaan', 100)
                ->nullable()
                ->after('jenis_kelamin');

            /*
             * Nomor rekening.
             */
            $table->string('no_rekening', 100)
                ->nullable()
                ->after('phone');

            /*
             * Jenis ID:
             *
             * KTP
             * SIM
             * PASSPORT
             * SERTIFIKAT
             */
            $table->string('jenis_id', 30)
                ->nullable()
                ->after('no_rekening');

            /*
             * Nomor KTP.
             *
             * Hanya digunakan jika jenis_id = KTP.
             */
            $table->string('no_ktp', 100)
                ->nullable()
                ->after('jenis_id');

            /*
             * Selain KTP.
             *
             * Digunakan untuk:
             * SIM
             * PASSPORT
             * SERTIFIKAT
             */
            $table->string('selain_ktp', 100)
                ->nullable()
                ->after('no_ktp');

            /*
             * Nomor CIF.
             *
             * Format:
             * AMR-00001
             * AMR-00002
             * dst.
             */
            $table->string('no_cif', 50)
                ->nullable()
                ->after('selain_ktp');

            /*
             * NPWP.
             */
            $table->string('npwp', 100)
                ->nullable()
                ->after('no_cif');

            /*
             * Local ID.
             */
            $table->string('local_id', 100)
                ->nullable()
                ->after('npwp');

            /*
             * Tanggal daftar.
             */
            $table->date('tgl_daftar')
                ->nullable()
                ->after('local_id');

            /*
             * Path dokumen identitas.
             *
             * Untuk sementara satu dokumen utama.
             * Nanti bisa dikembangkan menjadi customer_documents
             * untuk banyak dokumen.
             */
            $table->string('document_path')
                ->nullable()
                ->after('tgl_daftar');

            /*
             * Index pencarian.
             */
            $table->index(
                ['tenant_id', 'id_nasabah'],
                'customers_tenant_id_nasabah_index'
            );

            $table->index(
                ['tenant_id', 'no_cif'],
                'customers_tenant_no_cif_index'
            );

            $table->index(
                ['tenant_id', 'tipe'],
                'customers_tenant_tipe_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_tenant_id_nasabah_index');
            $table->dropIndex('customers_tenant_no_cif_index');
            $table->dropIndex('customers_tenant_tipe_index');

            $table->dropColumn([
                'id_nasabah',
                'idpjk',
                'tipe',
                'tempat_lahir',
                'warga_negara',
                'jenis_kelamin',
                'pekerjaan',
                'no_rekening',
                'jenis_id',
                'no_ktp',
                'selain_ktp',
                'no_cif',
                'npwp',
                'local_id',
                'tgl_daftar',
                'document_path',
            ]);
        });
    }
};
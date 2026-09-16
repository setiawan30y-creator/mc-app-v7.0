<?php

namespace App\Console\Commands;

use App\Models\IsoCurrency;
use App\Models\IsoCurrencyEntity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Throwable;

class SyncIsoCurrencies extends Command
{
    protected $signature = 'iso:sync
                            {--file= : Path XML SIX List One, default storage/app/list-one.xml}
                            {--dry-run : Parse and report without writing to database}';

    protected $description = 'Sync current ISO 4217 currencies and entities from the official SIX List One XML';

    /**
     * Codes from SIX List One that are not operational currencies
     * for the MC App currency selector.
     */
    private const EXCLUDED_CODES = [
        'BOV',
        'CHE',
        'CHW',
        'CLF',
        'COU',
        'MXV',
        'USN',
        'UYI',
        'UYW',

        'XAD',
        'XAG',
        'XAU',
        'XBA',
        'XBB',
        'XBC',
        'XBD',
        'XDR',
        'XPD',
        'XPT',
        'XSU',
        'XTS',
        'XUA',
        'XXX',
    ];

    /**
     * Some SIX List One entity names do not exactly match
     * the English region names returned by PHP Intl.
     *
     * These are explicit aliases only.
     *
     * Key   = normalized SIX entity name
     * Value = ISO / region code
     */
private const ENTITY_ALIASES = [
    'ANTIGUA AND BARBUDA' => 'AG',
    'BONAIRE, SINT EUSTATIUS AND SABA' => 'BQ',
    'BOSNIA AND HERZEGOVINA' => 'BA',
    'BRUNEI DARUSSALAM' => 'BN',
    'CABO VERDE' => 'CV',

    'CONGO (THE DEMOCRATIC REPUBLIC OF THE)' => 'CD',
    'CONGO (THE)' => 'CG',

    'FALKLAND ISLANDS (THE) [MALVINAS]' => 'FK',
    'HEARD ISLAND AND MCDONALD ISLANDS' => 'HM',

    'HOLY SEE (THE)' => 'VA',

    'HONG KONG' => 'HK',
    'MACAO' => 'MO',
    'MYANMAR' => 'MM',
    'PITCAIRN' => 'PN',

    'SAINT BARTHÉLEMY' => 'BL',
    'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA' => 'SH',
    'SAINT KITTS AND NEVIS' => 'KN',
    'SAINT LUCIA' => 'LC',
    'SAINT MARTIN (FRENCH PART)' => 'MF',
    'SAINT PIERRE AND MIQUELON' => 'PM',
    'SAINT VINCENT AND THE GRENADINES' => 'VC',

    'SAO TOME AND PRINCIPE' => 'ST',
    'SINT MAARTEN (DUTCH PART)' => 'SX',

    'SVALBARD AND JAN MAYEN' => 'SJ',

    'TAIWAN (PROVINCE OF CHINA)' => 'TW',
    'TRINIDAD AND TOBAGO' => 'TT',
    'TÜRKİYE' => 'TR',

    'TURKS AND CAICOS ISLANDS (THE)' => 'TC',

    'UNITED KINGDOM OF GREAT BRITAIN AND NORTHERN IRELAND (THE)' => 'GB',

    'UNITED STATES MINOR OUTLYING ISLANDS (THE)' => 'UM',
    'UNITED STATES OF AMERICA (THE)' => 'US',

    'VIET NAM' => 'VN',

    'VIRGIN ISLANDS (BRITISH)' => 'VG',
    'VIRGIN ISLANDS (U.S.)' => 'VI',

    'WALLIS AND FUTUNA' => 'WF',

    'BOLIVIA (PLURINATIONAL STATE OF)' => 'BO',
    'IRAN (ISLAMIC REPUBLIC OF)' => 'IR',
    'KOREA (THE DEMOCRATIC PEOPLE’S REPUBLIC OF)' => 'KP',
    'KOREA (THE REPUBLIC OF)' => 'KR',
    'LAO PEOPLE’S DEMOCRATIC REPUBLIC (THE)' => 'LA',
    'MICRONESIA (FEDERATED STATES OF)' => 'FM',
    'MOLDOVA (THE REPUBLIC OF)' => 'MD',
    'RUSSIAN FEDERATION (THE)' => 'RU',
    'SYRIAN ARAB REPUBLIC' => 'SY',
    'TANZANIA, UNITED REPUBLIC OF' => 'TZ',
    'VENEZUELA (BOLIVARIAN REPUBLIC OF)' => 'VE',
];

    public function handle(): int
    {
        $file = $this->option('file')
            ?: storage_path('app/list-one.xml');

        if (!is_file($file)) {
            $this->error('File XML tidak ditemukan:');
            $this->line($file);

            return self::FAILURE;
        }

        $this->info('SIX ISO 4217 List One');
        $this->line('File: ' . $file);
        $this->newLine();

        try {
            $xml = simplexml_load_file(
                $file,
                SimpleXMLElement::class,
                LIBXML_NONET | LIBXML_NOBLANKS
            );
        } catch (Throwable $e) {
            $this->error('Gagal membaca XML: ' . $e->getMessage());

            return self::FAILURE;
        }

        if (!$xml instanceof SimpleXMLElement) {
            $this->error('XML tidak dapat diparse.');

            return self::FAILURE;
        }

        $publishedDate = (string) ($xml['Pblshd'] ?? '');

        $entries = $xml->CcyTbl->CcyNtry ?? [];

        $currencies = [];
        $rawEntityCount = 0;
        $skippedNoCurrency = 0;
        $skippedExcluded = 0;

        $matchedEntityCount = 0;
        $unmatchedEntityCount = 0;
        $unmatchedEntities = [];

        foreach ($entries as $entry) {
            $countryName = trim((string) ($entry->CtryNm ?? ''));
            $currencyName = trim((string) ($entry->CcyNm ?? ''));
            $code = strtoupper(trim((string) ($entry->Ccy ?? '')));
            $numericCode = trim((string) ($entry->CcyNbr ?? ''));
            $minorUnitRaw = trim((string) ($entry->CcyMnrUnts ?? ''));

            if ($code === '') {
                $skippedNoCurrency++;
                continue;
            }

            if (in_array($code, self::EXCLUDED_CODES, true)) {
                $skippedExcluded++;
                continue;
            }

            $rawEntityCount++;

            $minorUnit = null;

            if ($minorUnitRaw !== '' && is_numeric($minorUnitRaw)) {
                $minorUnit = (int) $minorUnitRaw;
            }

            if (!isset($currencies[$code])) {
                $currencies[$code] = [
                    'code' => $code,
                    'numeric_code' => $numericCode !== ''
                        ? $numericCode
                        : null,
                    'name' => $currencyName !== ''
                        ? $currencyName
                        : $code,
                    'minor_unit' => $minorUnit,
                    'entities' => [],
                ];
            } else {
                /*
                 * Currency dapat digunakan oleh banyak entity.
                 * Jika informasi currency pada entry berikutnya
                 * lebih lengkap, pertahankan informasi yang ada.
                 */
                if (
                    $currencies[$code]['numeric_code'] === null
                    && $numericCode !== ''
                ) {
                    $currencies[$code]['numeric_code'] = $numericCode;
                }

                if (
                    ($currencies[$code]['name'] === $code)
                    && $currencyName !== ''
                ) {
                    $currencies[$code]['name'] = $currencyName;
                }

                if (
                    $currencies[$code]['minor_unit'] === null
                    && $minorUnit !== null
                ) {
                    $currencies[$code]['minor_unit'] = $minorUnit;
                }
            }

            if ($countryName !== '') {
                $countryCode = $this->resolveCountryCode($countryName);

                if ($countryCode !== null) {
                    $matchedEntityCount++;
                } else {
                    $unmatchedEntityCount++;

                    if (!in_array($countryName, $unmatchedEntities, true)) {
                        $unmatchedEntities[] = $countryName;
                    }
                }

                $currencies[$code]['entities'][$countryName] = [
                    'country_code' => $countryCode,
                    'entity_name' => $countryName,
                ];
            }
        }

        $currencyCount = count($currencies);

        $entityCount = array_sum(
            array_map(
                static fn (array $currency): int => count($currency['entities']),
                $currencies
            )
        );

        $this->table(
            ['Metric', 'Value'],
            [
                ['XML published', $publishedDate ?: '-'],
                ['XML entries', count($entries)],
                ['Currency tanpa Ccy', $skippedNoCurrency],
                ['Excluded special/fund/metal', $skippedExcluded],
                ['Unique operational currencies', $currencyCount],
                ['Unique entities', $entityCount],
                ['Entity matched → country code', $matchedEntityCount],
                ['Entity unmatched', $unmatchedEntityCount],
            ]
        );

        if ($unmatchedEntityCount > 0) {
            $this->newLine();
            $this->warn(
                'Ada entity SIX yang belum dapat dicocokkan dengan ISO/Intl.'
            );

            $this->table(
                ['#', 'SIX Entity'],
                collect($unmatchedEntities)
                    ->values()
                    ->map(
                        static fn (string $name, int $index): array => [
                            $index + 1,
                            $name,
                        ]
                    )
                    ->all()
            );
        }

        $this->newLine();

        if ($currencyCount === 0) {
            $this->error('Tidak ada currency yang dapat di-import.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('DRY RUN: tidak ada perubahan database.');

            return self::SUCCESS;
        }

        $syncedAt = now();

        DB::transaction(function () use (
            $currencies,
            $syncedAt
        ): void {
            /*
             * List One adalah daftar CURRENT.
             * Currency yang tidak lagi ada di List One
             * tidak boleh tetap dianggap aktif.
             */
            IsoCurrency::query()->update([
                'is_active' => false,
            ]);

            foreach ($currencies as $currencyData) {
                $entityNames = array_keys($currencyData['entities']);

                $currency = IsoCurrency::query()->updateOrCreate(
                    [
                        'code' => $currencyData['code'],
                    ],
                    [
                        'numeric_code' => $currencyData['numeric_code'],
                        'name' => $currencyData['name'],
                        'minor_unit' => $currencyData['minor_unit'],
                        'is_active' => true,
                        'last_synced_at' => $syncedAt,
                    ]
                );

                foreach ($currencyData['entities'] as $entityData) {
                    IsoCurrencyEntity::query()->updateOrCreate(
                        [
                            'iso_currency_id' => $currency->id,
                            'entity_name' => $entityData['entity_name'],
                        ],
                        [
                            'country_code' => $entityData['country_code'],
                        ]
                    );
                }

                /*
                 * Hapus entity lama yang sudah tidak terdapat
                 * dalam SIX List One terbaru.
                 */
                if ($entityNames === []) {
                    IsoCurrencyEntity::query()
                        ->where('iso_currency_id', $currency->id)
                        ->delete();
                } else {
                    IsoCurrencyEntity::query()
                        ->where('iso_currency_id', $currency->id)
                        ->whereNotIn('entity_name', $entityNames)
                        ->delete();
                }
            }
        });

        $this->newLine();
        $this->info('✓ Sinkronisasi SIX ISO 4217 berhasil.');
        $this->line('Synced at: ' . $syncedAt->toDateTimeString());

        return self::SUCCESS;
    }

    /**
     * Resolve SIX entity name into ISO 3166-1 alpha-2 / Intl region code.
     *
     * PHP Intl provides the authoritative region display names.
     * We reverse-build the code → name list and compare normalized names.
     */
    private function resolveCountryCode(string $entityName): ?string
    {
        $normalizedEntity = $this->normalizeEntityName($entityName);

        if ($normalizedEntity === '') {
            return null;
        }

        /*
         * Explicit aliases for SIX naming differences.
         */
	foreach (self::ENTITY_ALIASES as $aliasName => $countryCode) {
    	if (
        $this->normalizeEntityName($aliasName) === $normalizedEntity
    	) {
        	return $countryCode;
    		}
	}

        /*
         * Build the reverse lookup from all two-letter region codes
         * recognized by PHP Intl.
         *
         * This includes EU because ICU/Intl recognizes it as a region.
         */
        static $regionMap = null;

        if ($regionMap === null) {
            $regionMap = [];

            for ($first = ord('A'); $first <= ord('Z'); $first++) {
                for ($second = ord('A'); $second <= ord('Z'); $second++) {
                    $code = chr($first) . chr($second);

                   $displayName = \Locale::getDisplayRegion(
    		'-' . $code,
    			'en'
			);

                    if (
                        $displayName === ''
                        || $displayName === $code
                    ) {
                        continue;
                    }

                    $normalizedDisplayName = $this->normalizeEntityName(
                        $displayName
                    );

                    if ($normalizedDisplayName === '') {
                        continue;
                    }

                    /*
                     * Keep the first valid mapping.
                     */
                    if (!isset($regionMap[$normalizedDisplayName])) {
                        $regionMap[$normalizedDisplayName] = $code;
                    }
                }
            }
        }

        if (isset($regionMap[$normalizedEntity])) {
            return $regionMap[$normalizedEntity];
        }

        /*
         * A few harmless fallback transformations.
         *
         * SIX frequently appends "(THE)" while Intl does not.
         */
        $withoutThe = preg_replace(
            '/\s*\(THE\)\s*/u',
            ' ',
            $normalizedEntity
        );

        $withoutThe = $this->normalizeEntityName(
            $withoutThe ?? $normalizedEntity
        );

        if (
            $withoutThe !== ''
            && isset($regionMap[$withoutThe])
        ) {
            return $regionMap[$withoutThe];
        }

        return null;
    }

    /**
     * Normalize entity names so SIX and Intl naming differences
     * can be compared safely without changing stored entity_name.
     */
    private function normalizeEntityName(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        /*
         * Normalize common Unicode punctuation variants.
         */
        $value = str_replace(
            [
                '’',
                '‘',
                'ʼ',
                '´',
                '`',
                '‐',
                '-',
                '–',
                '—',
            ],
            [
                "'",
                "'",
                "'",
                "'",
                "'",
                '-',
                '-',
                '-',
                '-',
            ],
            $value
        );

        /*
         * Normalize repeated whitespace.
         */
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        /*
         * Normalize comma spacing.
         */
        $value = preg_replace('/\s*,\s*/u', ', ', $value) ?? $value;

        /*
         * Normalize parentheses spacing.
         */
        $value = preg_replace('/\s*\(\s*/u', ' (', $value) ?? $value;
        $value = preg_replace('/\s*\)\s*/u', ') ', $value) ?? $value;

        $value = trim($value);

        /*
         * Uppercase using multibyte support.
         */
        if (function_exists('mb_strtoupper')) {
            $value = mb_strtoupper($value, 'UTF-8');
        } else {
            $value = strtoupper($value);
        }

        return trim($value);
    }
}
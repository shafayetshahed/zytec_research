<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Issues gap-free, per-year sequential document numbers such as
 * INV-2026-0001. The counter row is locked so concurrent requests never
 * receive the same number.
 */
class DocumentNumberService
{
    public const QUOTATION = 'QUO';

    public const RECEIPT = 'RCP';

    public const INVOICE = 'INV';

    public function next(string $prefix): string
    {
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($prefix, $year) {
            $row = DB::table('document_sequences')->where('prefix', $prefix)->where('year', $year)->lockForUpdate()->first();

            if ($row === null) {
                DB::table('document_sequences')->insertOrIgnore(['prefix' => $prefix, 'year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
                $row = DB::table('document_sequences')->where('prefix', $prefix)->where('year', $year)->lockForUpdate()->first();
            }

            $number = (int) $row->last_number + 1;
            DB::table('document_sequences')->where('id', $row->id)->update(['last_number' => $number, 'updated_at' => now()]);

            return sprintf('%s-%d-%04d', $prefix, $year, $number);
        });
    }
}

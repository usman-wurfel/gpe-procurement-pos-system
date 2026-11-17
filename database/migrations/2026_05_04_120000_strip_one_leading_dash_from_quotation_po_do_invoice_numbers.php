<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time cleanup: stored document numbers had an extra leading dash (e.g. ---- vs --).
 * Removes exactly one leading '-' where the value starts with '-'.
 */
return new class extends Migration
{
    private function stripLeadingDash(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(
                'UPDATE `'.$table.'` SET `'.$column.'` = SUBSTRING(`'.$column.'`, 2) '.
                'WHERE `'.$column.'` IS NOT NULL AND `'.$column.'` LIKE ? AND CHAR_LENGTH(`'.$column.'`) > 1',
                ['-%']
            );

            return;
        }

        // Portable fallback (sqlite / pgsql): row-by-row chunks
        $idCol = Schema::hasColumn($table, 'id') ? 'id' : null;
        if (! $idCol) {
            return;
        }

        DB::table($table)
            ->whereNotNull($column)
            ->where($column, 'like', '-%')
            ->orderBy($idCol)
            ->chunkById(500, function ($rows) use ($table, $column, $idCol) {
                foreach ($rows as $row) {
                    $val = $row->{$column};
                    if ($val === null || $val === '') {
                        continue;
                    }
                    $str = (string) $val;
                    if (str_starts_with($str, '-') && strlen($str) > 1) {
                        DB::table($table)->where($idCol, $row->{$idCol})->update([
                            $column => substr($str, 1),
                        ]);
                    }
                }
            }, $idCol);
    }

    public function up(): void
    {
        $pairs = [
            ['quotations', 'reference_no'],
            ['purchases', 'reference_no'],
            ['delivery_orders', 'delivery_order_no'],
            ['delivery_orders', 'po_number'],
            ['delivery_orders', 'quotation_number'],
            ['invoices', 'invoice_no'],
            ['invoices', 'po_number'],
            ['invoices', 'quotation_number'],
        ];

        foreach ($pairs as [$table, $column]) {
            $this->stripLeadingDash($table, $column);
        }
    }

    public function down(): void
    {
        // Cannot restore stripped characters safely.
    }
};

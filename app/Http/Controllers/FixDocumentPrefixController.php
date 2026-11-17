<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off HTTP fix: same behaviour as migration
 * 2026_05_04_120000_strip_one_leading_dash_from_quotation_po_do_invoice_numbers.
 */
class FixDocumentPrefixController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! Auth::check() || Auth::user()->role_id > 2) {
            abort(403, 'Sirf authorized staff ye fix chala sakta hai.');
        }

        $details = [];
        $total = 0;

        foreach ($this->pairs() as [$table, $column]) {
            $n = $this->stripLeadingDash($table, $column);
            $details[] = ['table' => $table, 'column' => $column, 'rows_updated' => $n];
            $total += $n;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Leading dash cleanup complete.',
            'total_rows_updated' => $total,
            'details' => $details,
        ]);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function pairs(): array
    {
        return [
            ['quotations', 'reference_no'],
            ['purchases', 'reference_no'],
            ['delivery_orders', 'delivery_order_no'],
            ['delivery_orders', 'po_number'],
            ['delivery_orders', 'quotation_number'],
            ['invoices', 'invoice_no'],
            ['invoices', 'po_number'],
            ['invoices', 'quotation_number'],
        ];
    }

    private function stripLeadingDash(string $table, string $column): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return DB::update(
                'UPDATE `'.$table.'` SET `'.$column.'` = SUBSTRING(`'.$column.'`, 2) '.
                'WHERE `'.$column.'` IS NOT NULL AND `'.$column.'` LIKE ? AND CHAR_LENGTH(`'.$column.'`) > 1',
                ['-%']
            );
        }

        $idCol = Schema::hasColumn($table, 'id') ? 'id' : null;
        if (! $idCol) {
            return 0;
        }

        $updated = 0;

        DB::table($table)
            ->whereNotNull($column)
            ->where($column, 'like', '-%')
            ->orderBy($idCol)
            ->chunkById(500, function ($rows) use ($table, $column, $idCol, &$updated) {
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
                        $updated++;
                    }
                }
            }, $idCol);

        return $updated;
    }
}

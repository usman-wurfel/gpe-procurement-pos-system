<?php

namespace App\Support;

/**
 * Product autocomplete uses pipe-delimited payloads; product names may also contain "|".
 * These helpers treat the last N segments as fixed fields and merge the rest as name.
 */
final class LimsProductSearchPayload
{
    /**
     * Quotation / sale-style payload before "?": code | name (may contain pipes) | imei | is_embeded
     *
     * @return list{string, string, string, string}|null
     */
    public static function parseFourFieldPipePayload(string $payload): ?array
    {
        $parts = explode('|', $payload);
        if (count($parts) < 4) {
            return null;
        }

        $is_embeded = (string) array_pop($parts);
        $imei = (string) array_pop($parts);
        $code = (string) array_shift($parts);
        $name = implode('|', $parts);

        return [$code, $name, $imei, $is_embeded];
    }

    /**
     * Transfer list item: code | name | imei | is_embeded | row_qty | (optional trailing empty)
     *
     * @return list{string, string, string, string, string}|null
     */
    public static function parseTransferPipePayload(string $payload): ?array
    {
        $parts = explode('|', $payload);
        while (count($parts) > 0 && $parts[count($parts) - 1] === '') {
            array_pop($parts);
        }
        if (count($parts) < 5) {
            return null;
        }

        $rowQty = (string) array_pop($parts);
        $is_embeded = (string) array_pop($parts);
        $imei = (string) array_pop($parts);
        $code = (string) array_shift($parts);
        $name = implode('|', $parts);

        return [$code, $name, $imei, $is_embeded, $rowQty];
    }

    /**
     * Purchase autocomplete: code | name (name may contain pipes)
     *
     * @return array{0: string, 1: string}
     */
    public static function parseCodeNamePipe(string $payload): array
    {
        $parts = explode('|', $payload);
        $code = (string) array_shift($parts);
        $name = implode('|', $parts);

        return [rtrim($code, ' '), $name];
    }
}

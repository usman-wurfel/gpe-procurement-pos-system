<?php

use Carbon\Carbon;

/**
 * Display document number with runtime prefix from settings.
 * Uses saved value for the number part, prefix from current settings.
 */
if (!function_exists('doc_number_display')) {
    function doc_number_display($saved_value, $prefix)
    {
        if ($saved_value === null || $saved_value === '') {
            return '';
        }
        $original_value = trim((string) $saved_value);
        $saved_value = $original_value;
        $p = trim((string) ($prefix ?? ''));
        $p = rtrim($p, " -_/");

        if ($p === '') {
            return $saved_value;
        }

        // Remove repeated configured prefix if already stored in DB.
        $escaped_prefix = preg_quote($p, '/');
        $saved_value = preg_replace('/^(?:' . $escaped_prefix . '[\s\-_\/]*)+/i', '', $saved_value);

        // Also handle case where value starts with prefix without separator (e.g. DO3401...).
        if (stripos($saved_value, $p) === 0) {
            $saved_value = ltrim(substr($saved_value, strlen($p)), " -_/");
        }

        // Final fallback: remove a single generic leading prefix-with-hyphen only.
        if (preg_match('/^[A-Za-z ]+\-/', $saved_value)) {
            $saved_value = preg_replace('/^[^-]+-/', '', $saved_value);
        }

        $saved_value = trim((string) $saved_value);
        $saved_value = ltrim($saved_value, " -_/");
        if ($saved_value === '') {
            $saved_value = $original_value;
        }

        return $p . '-' . $saved_value;
    }
}

if (!function_exists('normalize_to_sql_datetime')) {
    function normalize_to_sql_datetime($input, $useCurrentTime = false)
    {
        if (empty($input)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        $input = trim($input);

        // Replace multiple possible separators with "-"
        $normalized = preg_replace('/[\/\.\s]+/', '-', $input);

        // Formats to test (you can add more if needed)
        $formats = [
            'd-m-Y',
            'd/m/Y',
            'd.m.Y',
            'm-d-Y',
            'm/d/Y',
            'm.d.Y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
        ];

        foreach ($formats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $normalized);

                if ($date !== false) {
                    if ($useCurrentTime) {
                        // inject current time if only date provided
                        $date->setTimeFrom(Carbon::now());
                    }
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                // just continue to next format
            }
        }

        // fallback: try Carbon::parse (loose parsing)
        try {
            $date = Carbon::parse($input);
            if ($useCurrentTime) {
                $date->setTimeFrom(Carbon::now());
            }
            return $date->format('Y-m-d').' '.date('H:i:s');
        } catch (\Exception $e) {
            // totally failed → return current datetime
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}

/**
 * Permission names for a role (array of strings).
 * Handles legacy cache entries stored as Collection of {name} objects.
 */
if (! function_exists('role_permission_names')) {
    function role_permission_names(int $roleId): array
    {
        $cached = \Illuminate\Support\Facades\Cache::remember(
            'role_permission_names_v3_' . $roleId,
            60 * 60 * 24 * 365,
            function () use ($roleId) {
                return \Illuminate\Support\Facades\DB::table('permissions')
                    ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->where('role_id', $roleId)
                    ->pluck('permissions.name')
                    ->all();
            }
        );

        return collect($cached)->map(function ($item) {
            if (is_string($item)) {
                return $item;
            }
            if (is_array($item) && isset($item['name'])) {
                return $item['name'];
            }
            if (is_object($item) && isset($item->name)) {
                return $item->name;
            }

            return (string) $item;
        })->values()->all();
    }
}

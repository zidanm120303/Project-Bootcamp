<?php

namespace App\Support;

final class Rupiah
{
    public static function value(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (int) round($value);
        }

        $value = trim((string) $value);

        if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $value)) {
            return (int) str_replace('.', '', $value);
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits === '' ? null : (int) $digits;
    }
}

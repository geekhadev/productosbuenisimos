<?php

namespace App\Support\Http;

final class IniSize
{
    public static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }

    public static function human(string $iniValue): string
    {
        $bytes = self::toBytes($iniValue);

        if ($bytes >= 1024 * 1024) {
            $megabytes = $bytes / (1024 * 1024);

            return rtrim(rtrim(number_format($megabytes, 1, '.', ''), '0'), '.').' MB';
        }

        if ($bytes >= 1024) {
            $kilobytes = $bytes / 1024;

            return rtrim(rtrim(number_format($kilobytes, 0, '.', ''), '0'), '.').' KB';
        }

        return $bytes.' B';
    }

    public static function uploadMaxBytes(): int
    {
        return self::toBytes((string) ini_get('upload_max_filesize'));
    }

    public static function uploadMaxHuman(): string
    {
        return self::human((string) ini_get('upload_max_filesize'));
    }
}

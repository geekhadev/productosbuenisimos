<?php

namespace App\Support\Sale;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Extrae certificado y clave privada en PEM desde un PKCS#12 (.pfx / .p12).
 *
 * Primero usa {@see openssl_pkcs12_read}; si falla (p. ej. cifrados antiguos con OpenSSL 3),
 * intenta el binario {@see openssl} con {@see -legacy} y sin él.
 */
final class PemBundleFromPkcs12
{
    /**
     * @return array{cert: string, pkey: string}|null
     */
    public static function tryExtract(string $pkcs12Binary, string $password): ?array
    {
        foreach (self::passwordVariants($password) as $passVariant) {
            $parsed = [];
            if (@openssl_pkcs12_read($pkcs12Binary, $parsed, $passVariant)) {
                if (isset($parsed['cert'], $parsed['pkey']) && is_string($parsed['cert']) && is_string($parsed['pkey'])) {
                    $normalized = self::normalizePemPair($parsed['cert'], $parsed['pkey']);
                    if ($normalized !== null) {
                        return $normalized;
                    }
                }
            }
            while (openssl_error_string() !== false) {
            }
        }

        $openssl = self::findOpensslBinary();
        if ($openssl === null) {
            return null;
        }

        $p12File = tempnam(sys_get_temp_dir(), 'pk12');
        $passFile = tempnam(sys_get_temp_dir(), 'p12p');
        if ($p12File === false || $passFile === false) {
            return null;
        }

        try {
            if (file_put_contents($p12File, $pkcs12Binary) === false) {
                return null;
            }
            @chmod($p12File, 0600);

            foreach (self::passwordVariants($password) as $passVariant) {
                if (file_put_contents($passFile, $passVariant) === false) {
                    return null;
                }
                @chmod($passFile, 0600);

                foreach ([true, false] as $legacy) {
                    $bundle = self::extractViaOpenSslCli($p12File, $passFile, $openssl, $legacy);
                    if ($bundle !== null) {
                        return $bundle;
                    }
                }
            }
        } finally {
            @unlink($p12File);
            @unlink($passFile);
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function passwordVariants(string $password): array
    {
        return array_values(array_unique([
            $password,
            rtrim($password, "\r\n"),
            trim($password),
        ]));
    }

    private static function findOpensslBinary(): ?string
    {
        $finder = new ExecutableFinder;

        return $finder->find('openssl', null, [
            '/opt/homebrew/bin',
            '/usr/local/bin',
            '/usr/bin',
        ]);
    }

    /**
     * @return array{cert: string, pkey: string}|null
     */
    private static function extractViaOpenSslCli(
        string $p12File,
        string $passFile,
        string $openssl,
        bool $legacy,
    ): ?array {
        $certCmd = [$openssl, 'pkcs12', '-in', $p12File, '-passin', 'file:'.$passFile];
        if ($legacy) {
            $certCmd[] = '-legacy';
        }
        $certCmd = array_merge($certCmd, ['-clcerts', '-nokeys']);

        $keyCmd = [$openssl, 'pkcs12', '-in', $p12File, '-passin', 'file:'.$passFile];
        if ($legacy) {
            $keyCmd[] = '-legacy';
        }
        $keyCmd = array_merge($keyCmd, ['-nocerts', '-nodes']);

        try {
            $certProcess = new Process($certCmd);
            $certProcess->setTimeout(30);
            $certProcess->run();
            if (! $certProcess->isSuccessful()) {
                return null;
            }

            $cert = trim($certProcess->getOutput());
            if ($cert === '' || ! str_contains($cert, 'BEGIN CERTIFICATE')) {
                return null;
            }

            $keyProcess = new Process($keyCmd);
            $keyProcess->setTimeout(30);
            $keyProcess->run();
            if (! $keyProcess->isSuccessful()) {
                return null;
            }

            $pkey = trim($keyProcess->getOutput());
            if ($pkey === '' || ! str_contains($pkey, 'PRIVATE KEY')) {
                return null;
            }

            return self::normalizePemPair($cert, $pkey);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{cert: string, pkey: string}|null
     */
    private static function normalizePemPair(string $certRaw, string $pkeyRaw): ?array
    {
        $cert = self::normalizePemCertificate($certRaw);
        $pkey = self::normalizePemPrivateKey($pkeyRaw);
        if ($cert === null || $pkey === null) {
            return null;
        }

        return ['cert' => $cert, 'pkey' => $pkey];
    }

    /**
     * Deja solo el primer certificado X.509 en PEM (sin texto tipo "Bag Attributes" que algunos openssl imprimen).
     */
    private static function normalizePemCertificate(string $raw): ?string
    {
        if (! preg_match(
            '/-----BEGIN CERTIFICATE-----\s*(.+?)\s*-----END CERTIFICATE-----/s',
            $raw,
            $m,
        )) {
            return null;
        }

        $b64 = preg_replace('/\s+/', '', $m[1]) ?? '';

        return $b64 === '' ? null : self::wrapPem('CERTIFICATE', $b64);
    }

    /**
     * Deja solo el primer bloque de clave privada en PEM reconocido por OpenSSL.
     */
    private static function normalizePemPrivateKey(string $raw): ?string
    {
        $pairs = [
            ['RSA PRIVATE KEY', 'RSA PRIVATE KEY'],
            ['PRIVATE KEY', 'PRIVATE KEY'],
            ['EC PRIVATE KEY', 'EC PRIVATE KEY'],
        ];

        foreach ($pairs as [$beginLabel, $endLabel]) {
            $begin = '-----BEGIN '.$beginLabel.'-----';
            $end = '-----END '.$endLabel.'-----';
            if (preg_match('/'.preg_quote($begin, '/').'\s*(.+?)\s*'.preg_quote($end, '/').'/s', $raw, $m)) {
                $b64 = preg_replace('/\s+/', '', $m[1]) ?? '';

                return $b64 === '' ? null : self::wrapPem($beginLabel, $b64);
            }
        }

        return null;
    }

    private static function wrapPem(string $label, string $base64BodyNoWhitespace): string
    {
        $wrapped = rtrim(chunk_split($base64BodyNoWhitespace, 64, "\n"), "\n");

        return '-----BEGIN '.$label."-----\n".$wrapped."\n-----END ".$label."-----\n";
    }
}

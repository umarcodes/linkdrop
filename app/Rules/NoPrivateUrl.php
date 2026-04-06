<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects URLs that resolve to private, loopback, or reserved addresses,
 * preventing SSRF attacks where the server is tricked into fetching
 * internal services (localhost, AWS metadata endpoint, etc.).
 */
class NoPrivateUrl implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $host = parse_url($value, PHP_URL_HOST);

        if (! $host) {
            return; // Standard URL validation will catch malformed URLs.
        }

        $host = strtolower(trim($host, '[]')); // strip IPv6 brackets

        $blockedHosts = [
            'localhost',
            'metadata.google.internal',
            '169.254.169.254', // AWS/Azure/GCP instance metadata
        ];

        if (in_array($host, $blockedHosts, strict: true)) {
            $fail('The :attribute must not point to an internal address.');

            return;
        }

        // Block *.local, *.internal, *.localhost TLDs
        if (preg_match('/\.(local|internal|localhost)$/i', $host)) {
            $fail('The :attribute must not point to an internal address.');

            return;
        }

        // If the host is a raw IP address, reject private/reserved ranges
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $isPublic = filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if (! $isPublic) {
                $fail('The :attribute must not point to a private IP address.');
            }
        }
    }
}

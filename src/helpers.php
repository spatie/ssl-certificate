<?php

namespace Spatie\SslCertificate;

function starts_with($haystack, $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param string $haystack
 * @param string|array $needles
 *
 * @return bool
 */
function ends_with(string $haystack, $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ((string) $needle === substr($haystack, -length($needle))) {
            return true;
        }
    }

    return false;
}

/**
 * Returns the portion of string specified by the start and length parameters.
 *
 * @param string  $string
 * @param int $start
 * @param int|null $length
 *
 * @return string
 */
function substr(string $string, int $start, int $length = null): string
{
    return mb_substr($string, $start, $length, 'UTF-8');
}

/**
 * Return the length of the given string.
 *
 * @param string $value
 *
 * @return int
 */
function length(string $value): int
{
    return mb_strlen($value);
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param string $haystack
 * @param string|array $needles
 *
 * @return bool
 */
function str_contains(string $haystack, $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

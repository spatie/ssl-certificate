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

function ends_with(string $haystack, string | array $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ((string) $needle === substr($haystack, -length($needle))) {
            return true;
        }
    }

    return false;
}

function substr(string $string, int $start, ?int $length = null): string
{
    return mb_substr($string, $start, $length, 'UTF-8');
}

function length(string $value): int
{
    return mb_strlen($value);
}

function str_contains(string $haystack, string | array $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

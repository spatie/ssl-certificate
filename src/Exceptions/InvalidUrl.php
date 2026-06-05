<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;

final class InvalidUrl extends Exception
{
    public static function couldNotValidate(string $url): static
    {
        return new self("String `{$url}` is not a valid url.");
    }

    public static function couldNotDetermineHost(string $url): static
    {
        return new self("Could not determine host from url `{$url}`.");
    }
}

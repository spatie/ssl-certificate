<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;

final class InvalidIpAddress extends Exception
{
    public static function couldNotValidate(string $ipAddress): static
    {
        return new self("String `{$ipAddress}` is not a valid IP address.");
    }
}

<?php

namespace Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

use Exception;

class CouldNotDownloadCertificate extends Exception
{
    public static function hostDoesNotExist(string $hostName): self
    {
        return new HostDoesNotExist($hostName);
    }

    public static function noCertificateInstalled(string $hostName): self
    {
        return new NoCertificateInstalled($hostName);
    }

    public static function unknownError(string $hostName, string $errorMessage): self
    {
        return new UnknownError($hostName, $errorMessage);
    }
}

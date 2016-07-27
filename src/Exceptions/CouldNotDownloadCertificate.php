<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;

class CouldNotDownloadCertificate extends Exception
{
    public static function hostDoesNotExist(string $hostName): CouldNotDownloadCertificate
    {
        return new static("The host named `{$hostName}` does not exist.");
    }

    public static function noCertificateInstalled(string $hostName): CouldNotDownloadCertificate
    {
        return new static("Could not find a certifcate on  host named `{$hostName}`.");
    }

    public static function unknownError(string $hostName, string $errorMessage): CouldNotDownloadCertificate
    {
        return new static("Could not download certificate for host `{$hostName}` because {$errorMessage}");
    }
}

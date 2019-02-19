<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\UnknownError;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\HostDoesNotExist;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\NoCertificateInstalled;

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

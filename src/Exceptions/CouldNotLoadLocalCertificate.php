<?php

namespace Spatie\SslCertificate\Exceptions;

use Exception;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateFilePermissionInvalid;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateFileReadFailed;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\CertificateFileStringEmpty;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\LocalCertificateParseFailed;
use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate\NoCertificateFoundAtPath;

class CouldNotLoadLocalCertificate extends Exception
{
    public static function certificateStringIsEmpty(): self
    {
        return new CertificateFileStringEmpty();
    }

    public static function certificatePathNotFound(string $path): self
    {
        return new NoCertificateFoundAtPath($path);
    }

    public static function localCertificateParseFailed(): self
    {
        return new LocalCertificateParseFailed();
    }

    public static function certificateFilePermissionInvalid(string $path): self
    {
        return new CertificateFilePermissionInvalid($path);
    }

    public static function certificateFileReadFailed(string $path): self
    {
        return new CertificateFileReadFailed($path);
    }
}
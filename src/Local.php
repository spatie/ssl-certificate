<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotLoadLocalCertificate;

class Local
{
    private function parseCertificate(string $certificateString): SslCertificate
    {
        $certificateString = trim($certificateString);

        if (length($certificateString) === 0) {
            throw CouldNotLoadLocalCertificate::certificateStringIsEmpty();
        }

        $certificateFields = openssl_x509_parse($certificateString);
        if (!$certificateFields) {
            throw CouldNotLoadLocalCertificate::localCertificateParseFailed();
        }

        $fingerprint = openssl_x509_fingerprint($certificateString);
        $fingerprintSha256 = openssl_x509_fingerprint($certificateString, 'sha256');

        return new SslCertificate(
            $certificateFields,
            $fingerprint,
            $fingerprintSha256
        );
    }

    public static function certificateAsString(string $certificateString): SslCertificate
    {
        return (new static ())->parseCertificate($certificateString);
    }

    public static function certificateFromLocalPath(string $path): SslCertificate
    {
        if (!file_exists($path)) {
            throw CouldNotLoadLocalCertificate::certificatePathNotFound($path);
        }

        if (!is_readable($path)) {
            throw CouldNotLoadLocalCertificate::certificateFilePermissionInvalid($path);
        }

        $certificateString = file_get_contents($path);
        if (!$certificateString) {
            throw CouldNotLoadLocalCertificate::certificateFileReadFailed($path);
        }

        return (new static())->parseCertificate($certificateString);
    }
}

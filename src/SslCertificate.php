<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;

class SslCertificate
{
    /** @var array */
    protected $rawCertificateFields = [];

    public static function createFromUrl(string $url, int $timeout = 30): SslCertificate
    {
        $rawCertificateFields = Downloader::downloadCertificateFromUrl($url);

        return new static($rawCertificateFields);
    }

    public static function createFromFile(string $path)
    {
    }

    public function __construct(array $rawCertificateFields)
    {
        $this->rawCertificateFields = $rawCertificateFields;
    }

    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    public function getDomain(): string
    {
        return $this->rawCertificateFields['subject']['CN'] ?? '';
    }

    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');

        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    public function getExpirationDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
    }
}

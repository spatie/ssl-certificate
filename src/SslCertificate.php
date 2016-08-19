<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;

class SslCertificate
{
    /** @var array */
    protected $rawCertificateFields = [];

    public static function createForHostName(string $url, int $timeout = 30): SslCertificate
    {
        $rawCertificateFields = Downloader::downloadCertificateFromUrl($url, $timeout);

        return new static($rawCertificateFields);
    }

    public function __construct(array $rawCertificateFields)
    {
        $this->rawCertificateFields = $rawCertificateFields;
    }

    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    public function getIssuer(): string
    {
        return $this->rawCertificateFields['issuer']['CN'];
    }

    public function getDomain(): string
    {
        return $this->rawCertificateFields['subject']['CN'] ?? '';
    }

    public function getSignatureAlgorithm(): string
    {
        return $this->rawCertificateFields['signatureTypeSN'] ?? '';
    }

    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');

        return array_map(function (string $domain) {
            return str_replace('DNS:', '', $domain);
        }, $additionalDomains);
    }

    public function validFromDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validFrom_time_t']);
    }

    public function expirationDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
    }

    public function isExpired(): bool
    {
        return $this->expirationDate()->isPast();
    }

    public function isValid(string $url = null)
    {
        if (!Carbon::now()->between($this->validFromDate(), $this->expirationDate())) {
            return false;
        }

        if (!empty($url)) {
            return $this->appliesToUrl($url ?? $this->getDomain());
        }

        return true;
    }

    public function isValidUntil(Carbon $carbon, string $url = null): bool
    {
        if ($this->expirationDate()->gt($carbon)) {
            return false;
        }

        return $this->isValid($url);
    }

    public function appliesToUrl(string $url): bool
    {
        $host = (new Url($url))->getHostName();

        $certificateHosts = array_merge([$this->getDomain()], $this->getAdditionalDomains());

        foreach ($certificateHosts as $certificateHost) {
            if ($host === $certificateHost) {
                return true;
            }

            if ($this->wildcardHostCoversHost($certificateHost, $host)) {
                return true;
            }
        }

        return false;
    }

    protected function wildcardHostCoversHost(string $wildcardHost, string $host): bool
    {
        if ($host === $wildcardHost) {
            return true;
        }

        if (!starts_with($wildcardHost, '*')) {
            return false;
        }

        $wildcardHostWithoutWildcard = substr($wildcardHost, 2);

        return ends_with($host, $wildcardHostWithoutWildcard);
    }
}

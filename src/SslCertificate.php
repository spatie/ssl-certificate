<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;

class SslCertificate
{
    /** @var array */
    protected $rawCertificateFields = [];

    public static function download(): Downloader
    {
        return new Downloader();
    }

    public static function createForHostName(string $url, int $timeout = 30): SslCertificate
    {
        $sslCertificate = Downloader::downloadCertificateFromUrl($url, $timeout);

        return $sslCertificate;
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
        if (! Carbon::now()->between($this->validFromDate(), $this->expirationDate())) {
            return false;
        }

        if (! empty($url)) {
            return $this->appliesToUrl($url ?? $this->getDomain());
        }

        return true;
    }

    public function isSelfSigned(): bool
    {
        return $this->getIssuer() === $this->getDomain();
    }

    public function isValidUntil(Carbon $carbon, string $url = null): bool
    {
        if ($this->expirationDate()->lte($carbon)) {
            return false;
        }

        return $this->isValid($url);
    }

    public function daysUntilExpirationDate(): int
    {
        $endDate = $this->expirationDate();

        $interval = Carbon::now()->diff($endDate);

        return (int)$interval->format("%r%a");
    }

    public function getDomains(): array
    {
        return array_filter(array_merge([$this->getDomain()], $this->getAdditionalDomains()));
    }

    public function appliesToUrl(string $url): bool
    {
        $host = (new Url($url))->getHostName();

        $certificateHosts = $this->getDomains();

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

        if (! starts_with($wildcardHost, '*')) {
            return false;
        }

        $wildcardHostWithoutWildcard = substr($wildcardHost, 2);

        return substr_count($wildcardHost, '.') >= substr_count($host, '.') && ends_with($host, $wildcardHostWithoutWildcard);
    }

    public function getRawCertificateFieldsJson(): string
    {
        return json_encode($this->getRawCertificateFields());
    }

    public function getHash(): string
    {
        return md5($this->getRawCertificateFieldsJson());
    }

    public function __toString(): string
    {
        return $this->getRawCertificateFieldsJson();
    }
}

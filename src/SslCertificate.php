<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;
use Spatie\Macroable\Macroable;

class SslCertificate
{
    use Macroable;

    /** @var array */
    protected $rawCertificateFields = [];

    /** @var string */
    protected $fingerprint = '';

    /** @var string */
    private $fingerprintSha256 = '';

    /** @var string */
    private $remoteAddress = '';

    public static function download(): Downloader
    {
        return new Downloader();
    }

    public static function createForHostName(string $url, int $timeout = 30): self
    {
        return Downloader::downloadCertificateFromUrl($url, $timeout);
    }

    public function __construct(
        array $rawCertificateFields,
        string $fingerprint = '',
        string $fingerprintSha256 = '',
        string $remoteAddress = ''
    ) {
        $this->rawCertificateFields = $rawCertificateFields;

        $this->fingerprint = $fingerprint;

        $this->fingerprintSha256 = $fingerprintSha256;

        $this->remoteAddress = $remoteAddress;
    }

    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    public function getIssuer(): string
    {
        return $this->rawCertificateFields['issuer']['CN'] ?? '';
    }

    public function getDomain(): string
    {
        if (! array_key_exists('CN', $this->rawCertificateFields['subject'])) {
            return '';
        }

        if (is_string($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'];
        }

        if (is_array($this->rawCertificateFields['subject']['CN'])) {
            return $this->rawCertificateFields['subject']['CN'][0];
        }

        return '';
    }

    public function getSignatureAlgorithm(): string
    {
        return $this->rawCertificateFields['signatureTypeSN'] ?? '';
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @return string
     */
    public function getFingerprintSha256(): string
    {
        return $this->fingerprintSha256;
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

    public function usesSha1Hash(): bool
    {
        $certificateFields = $this->getRawCertificateFields();

        if ($certificateFields['signatureTypeSN'] === 'RSA-SHA1') {
            return true;
        }

        if ($certificateFields['signatureTypeLN'] === 'sha1WithRSAEncryption') {
            return true;
        }

        return false;
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

        return (int) $interval->format('%r%a');
    }

    public function getDomains(): array
    {
        $allDomains = $this->getAdditionalDomains();
        $allDomains[] = $this->getDomain();
        $uniqueDomains = array_unique($allDomains);

        return array_values(array_filter($uniqueDomains));
    }

    public function appliesToUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_IP)) {
            $host = $url;
        } else {
            $host = (new Url($url))->getHostName();
        }

        $certificateHosts = $this->getDomains();

        foreach ($certificateHosts as $certificateHost) {
            $certificateHost = str_replace('ip address:', '', strtolower($certificateHost));
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

        if (substr_count($wildcardHost, '.') < substr_count($host, '.')) {
            return false;
        }

        $wildcardHostWithoutWildcard = substr($wildcardHost, 1);

        $hostWithDottedPrefix = ".{$host}";

        return ends_with($hostWithDottedPrefix, $wildcardHostWithoutWildcard);
    }

    public function getRawCertificateFieldsJson(): string
    {
        return json_encode($this->getRawCertificateFields());
    }

    public function getHash(): string
    {
        return md5($this->getRawCertificateFieldsJson());
    }

    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    public function __toString(): string
    {
        return $this->getRawCertificateFieldsJson();
    }

    public function containsDomain(string $domain): bool
    {
        $certificateHosts = $this->getDomains();

        foreach ($certificateHosts as $certificateHost) {
            if ($certificateHost == $domain) {
                return true;
            }

            if (ends_with($domain, '.'.$certificateHost)) {
                return true;
            }
        }

        return false;
    }

    public function isPreCertificate(): bool
    {
        if (! array_key_exists('extensions', $this->rawCertificateFields)) {
            return false;
        }

        if (! array_key_exists('ct_precert_poison', $this->rawCertificateFields['extensions'])) {
            return false;
        }

        return true;
    }
}

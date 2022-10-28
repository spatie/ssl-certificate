<?php

namespace Spatie\SslCertificate;

use Carbon\Carbon;
use Spatie\Macroable\Macroable;

class SslCertificate
{
    use Macroable;

    public static function download(): Downloader
    {
        return new Downloader();
    }

    public static function createForHostName(string $url, int $timeout = 30, bool $verifyCertificate = true): self
    {
        return Downloader::downloadCertificateFromUrl($url, $timeout, $verifyCertificate);
    }

    public static function createFromFile(string $pathToCertificate): self
    {
        $fileContents = file_get_contents($pathToCertificate);
        if (! str_contains($fileContents, 'BEGIN CERTIFICATE')) {
            $fileContents = self::der2pem($fileContents);
        }

        return self::createFromString($fileContents);
    }

    public static function createFromString(string $certificatePem): self
    {
        $certificateFields = openssl_x509_parse($certificatePem);

        $fingerprint = openssl_x509_fingerprint($certificatePem);
        $fingerprintSha256 = openssl_x509_fingerprint($certificatePem, 'sha256');

        return new self(
            $certificateFields,
            $fingerprint,
            $fingerprintSha256
        );
    }

    public static function createFromArray(array $properties): self
    {
        return new self(
            $properties['rawCertificateFields'],
            $properties['fingerprint'],
            $properties['fingerprintSha256'],
            $properties['remoteAddress'],
        );
    }

    public static function der2pem($der_data): string
    {
        $pem = chunk_split(base64_encode($der_data), 64, "\n");

        return "-----BEGIN CERTIFICATE-----\n".$pem."-----END CERTIFICATE-----\n";
    }

    public function __construct(
        protected array $rawCertificateFields,
        protected string $fingerprint = '',
        private string $fingerprintSha256 = '',
        private string $remoteAddress = '',
    ) {
        //
    }

    public function getRawCertificateFields(): array
    {
        return $this->rawCertificateFields;
    }

    public function getIssuer(): string
    {
        return $this->rawCertificateFields['issuer']['CN'] ?? '';
    }

    public function getSerialNumber(): string
    {
        return $this->rawCertificateFields['serialNumber'] ?? '';
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

    public function getOrganization(): string
    {
        return $this->rawCertificateFields['issuer']['O'] ?? '';
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint ?? '';
    }

    public function getFingerprintSha256(): string
    {
        return $this->fingerprintSha256 ?? '';
    }

    public function getAdditionalDomains(): array
    {
        $additionalDomains = explode(', ', $this->rawCertificateFields['extensions']['subjectAltName'] ?? '');

        return array_map(fn (string $domain) => str_replace('DNS:', '', $domain), $additionalDomains);
    }

    public function validFromDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validFrom_time_t']);
    }

    public function expirationDate(): Carbon
    {
        return Carbon::createFromTimestampUTC($this->rawCertificateFields['validTo_time_t']);
    }

    public function lifespanInDays(): int
    {
        return $this->validFromDate()->diffInDays($this->expirationDate());
    }

    public function isExpired(): bool
    {
        return $this->expirationDate()->isPast();
    }

    public function isValid(string $url = null): bool
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

    public function toArray(): array
    {
        return [
            'rawCertificateFields' => $this->rawCertificateFields,
            'fingerprint' => $this->fingerprint,
            'fingerprintSha256' => $this->fingerprintSha256,
            'remoteAddress' => $this->remoteAddress,
        ];
    }

    public function containsDomain(string $domain): bool
    {
        $certificateHosts = $this->getDomains();

        foreach ($certificateHosts as $certificateHost) {
            if ($certificateHost === $domain) {
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

<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Spatie\SslCertificate\Exceptions\InvalidIpAddress;

class Downloader
{
    /** @var int */
    protected $port = 443;

    /** @var string */
    protected $ipAddress = null;

    /** @var bool */
    protected $usingIpAddress = false;

    /** @var int */
    protected $timeout = 30;

    /** @var bool */
    protected $enableSni = true;

    /** @var bool */
    protected $capturePeerChain = false;

    /** @var bool */
    protected $verifyPeer = true;

    /** @var bool */
    protected $verifyPeerName = true;

    public function usingPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    public function usingSni(bool $sni)
    {
        $this->enableSni = $sni;

        return $this;
    }

    public function withFullChain(bool $fullChain)
    {
        $this->capturePeerChain = $fullChain;

        return $this;
    }

    public function withVerifyPeer(bool $verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    public function withVerifyPeerName(bool $verifyPeerName)
    {
        $this->verifyPeerName = $verifyPeerName;

        return $this;
    }

    public function setTimeout(int $timeOutInSeconds)
    {
        $this->timeout = $timeOutInSeconds;

        return $this;
    }

    public function fromIpAddress(string $ipAddress)
    {
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw InvalidIpAddress::couldNotValidate($ipAddress);
        }

        $this->ipAddress = $ipAddress;
        $this->usingIpAddress = true;

        return $this;
    }

    public function getCertificates(string $hostName): array
    {
        $response = $this->fetchCertificates($hostName);
        $remoteAddress = $response['remoteAddress'];

        $peerCertificate = $response['options']['ssl']['peer_certificate'];

        $peerCertificateChain = $response['options']['ssl']['peer_certificate_chain'] ?? [];

        $fullCertificateChain = array_merge([$peerCertificate], $peerCertificateChain);

        $certificates = array_map(function ($certificate) use ($remoteAddress) {
            $certificateFields = openssl_x509_parse($certificate);

            $fingerprint = openssl_x509_fingerprint($certificate);
            $fingerprintSha256 = openssl_x509_fingerprint($certificate, 'sha256');

            return new SslCertificate(
                $certificateFields,
                $fingerprint,
                $fingerprintSha256,
                $remoteAddress
            );
        }, $fullCertificateChain);

        return array_unique($certificates);
    }

    public function forHost(string $hostName): SslCertificate
    {
        $hostName = (new Url($hostName))->getHostName();

        $certificates = $this->getCertificates($hostName);

        return $certificates[0] ?? false;
    }

    public static function downloadCertificateFromUrl(string $url, int $timeout = 30): SslCertificate
    {
        return (new static())
            ->setTimeout($timeout)
            ->forHost($url);
    }

    protected function fetchCertificates(string $hostName): array
    {
        $hostName = (new Url($hostName))->getHostName();

        $sslOptions = [
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => $this->capturePeerChain,
            'SNI_enabled' => $this->enableSni,
            'peer_name' => $hostName,
            'verify_peer' => $this->verifyPeer,
            'verify_peer_name' => $this->verifyPeerName,
        ];

        $streamContext = stream_context_create([
            'ssl' => $sslOptions,
        ]);

        $connectTo = ($this->usingIpAddress)
            ? $this->ipAddress
            : $hostName;

        $client = @stream_socket_client(
            "ssl://{$connectTo}:{$this->port}",
            $errorNumber,
            $errorDescription,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $streamContext
        );

        if (! empty($errorDescription)) {
            throw $this->buildFailureException($connectTo, $errorDescription);
        }

        if (! $client) {
            $clientErrorMessage = ($this->usingIpAddress)
                ? "Could not connect to `{$connectTo}` or it does not have a certificate matching `${hostName}`."
                : "Could not connect to `{$connectTo}`.";

            throw CouldNotDownloadCertificate::unknownError($hostName, $clientErrorMessage);
        }

        $response = stream_context_get_params($client);

        $response['remoteAddress'] = stream_socket_get_name($client, true);

        fclose($client);

        return $response;
    }

    protected function buildFailureException(string $hostName, string $errorDescription)
    {
        if (str_contains($errorDescription, 'getaddrinfo failed')) {
            return CouldNotDownloadCertificate::hostDoesNotExist($hostName);
        }

        if (str_contains($errorDescription, 'error:14090086')) {
            return CouldNotDownloadCertificate::noCertificateInstalled($hostName);
        }

        return CouldNotDownloadCertificate::unknownError($hostName, $errorDescription);
    }
}

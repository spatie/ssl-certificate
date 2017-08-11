<?php

namespace Spatie\SslCertificate;

use Throwable;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

class Downloader
{
    /** @var int */
    protected $port = 443;

    /** @var int */
    protected $timeout = 30;

    /** @var bool */
    protected $enableSni = true;

    /** @var bool */
    protected $capturePeerChain = false;

    /**
     * @param int $port
     *
     * @return $this
     */
    public function usingPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param int $sni
     *
     * @return $this
     */
    public function usingSni(bool $sni)
    {
        $this->enableSni = $sni;

        return $this;
    }

    /**
     * @param int $ca_chain
     *
     * @return $this
     */
    public function withFullChain(bool $ca_chain)
    {
        $this->capturePeerChain = $ca_chain;

        return $this;
    }

    /**
     * @param int $timeOutInSeconds
     *
     * @return $this
     */
    public function setTimeout(int $timeOutInSeconds)
    {
        $this->timeout = $timeOutInSeconds;

        return $this;
    }

    public function getCertificates(string $hostName): array
    {
        $response = $this->fetchCertificates($hostName);

        $peerCertificate = $response['options']['ssl']['peer_certificate'];

        $peerCertificateChain = $response['options']['ssl']['peer_certificate_chain'] ?? [];

        $fullCertificateChain = array_merge([$peerCertificate], $peerCertificateChain);

        // Filter duplicates: wildcard SSL certs are reported in both
        // 'peer_certificate' as well as 'peer_certificate_chain'
        return array_unique(array_map(function ($certificate) {
            $certificateFields = openssl_x509_parse($certificate);

            return new SslCertificate($certificateFields);
        }, $fullCertificateChain));
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
        ];

        $streamContext = stream_context_create([
            'ssl' => $sslOptions,
        ]);

        try {
            $client = stream_socket_client(
                "ssl://{$hostName}:{$this->port}",
                $errorNumber,
                $errorDescription,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                $streamContext
            );
        } catch (Throwable $thrown) {
            $this->handleRequestFailure($hostName, $thrown);
        }

        if (! $client) {
            throw CouldNotDownloadCertificate::unknownError($hostName, "Could not connect to `{$hostName}`.");
        }

        $response = stream_context_get_params($client);

        return $response;
    }

    protected function handleRequestFailure(string $hostName, Throwable $thrown)
    {
        if (str_contains($thrown->getMessage(), 'getaddrinfo failed')) {
            throw CouldNotDownloadCertificate::hostDoesNotExist($hostName);
        }

        if (str_contains($thrown->getMessage(), 'error:14090086')) {
            throw CouldNotDownloadCertificate::noCertificateInstalled($hostName);
        }

        throw CouldNotDownloadCertificate::unknownError($hostName, $thrown->getMessage());
    }
}

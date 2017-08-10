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
    protected $sni = true;

    /** @var bool */
    protected $ca_chain = false;

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
     * @param int $port
     *
     * @return $this
     */
    public function usingSni(bool $sni)
    {
        $this->sni = $sni;

        return $this;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function withFullChain(bool $ca_chain)
    {
        $this->ca_chain = $ca_chain;

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
        $hostName = (new Url($hostName))->getHostName();

        $ssl_options = [
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => $this->ca_chain,
            'SNI_enabled' => $this->sni,
        ];

        $streamContext = stream_context_create([
            'ssl' => $ssl_options,
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
            if (str_contains($thrown->getMessage(), 'getaddrinfo failed')) {
                throw CouldNotDownloadCertificate::hostDoesNotExist($hostName);
            }

            if (str_contains($thrown->getMessage(), 'error:14090086')) {
                throw CouldNotDownloadCertificate::noCertificateInstalled($hostName);
            }

            throw CouldNotDownloadCertificate::unknownError($hostName, $thrown->getMessage());
        }

        if (! $client) {
            throw CouldNotDownloadCertificate::unknownError($hostName, "Could not connect to `{$hostName}`.");
        }

        $response = stream_context_get_params($client);

        $peer_certificate = $response['options']['ssl']['peer_certificate'];
        $peer_certificate_chain = $response['options']['ssl']['peer_certificate_chain'] ?? [];
        $certificates = array_merge ([ $peer_certificate ], $peer_certificate_chain);

        $return = [];
        foreach ($certificates as $certificate) {
          $certificateFields = openssl_x509_parse($certificate);
          $return[] = new SslCertificate($certificateFields);
        }

        return $return;
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
}

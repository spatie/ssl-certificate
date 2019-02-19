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

    /** @var bool */
    protected $verifyPeer = true;

    /** @var bool */
    protected $verifyPeerName = true;

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
     * @param bool $sni
     *
     * @return $this
     */
    public function usingSni(bool $sni)
    {
        $this->enableSni = $sni;

        return $this;
    }

    /**
     * @param bool $fullChain
     *
     * @return $this
     */
    public function withFullChain(bool $fullChain)
    {
        $this->capturePeerChain = $fullChain;

        return $this;
    }

    /**
     * @param bool $verifyPeer
     *
     * @return $this
     */
    public function withVerifyPeer(bool $verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    /**
     * @param bool $verifyPeerName
     *
     * @return $this
     */
    public function withVerifyPeerName(bool $verifyPeerName)
    {
        $this->verifyPeerName = $verifyPeerName;

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

        $certificates = array_map(function ($certificate) {
            $certificateFields = openssl_x509_parse($certificate);

            $fingerprint = openssl_x509_fingerprint($certificate);

            return new SslCertificate($certificateFields, $fingerprint);
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
            'verify_peer' => $this->verifyPeer,
            'verify_peer_name' => $this->verifyPeerName,
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

        fclose($client);

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

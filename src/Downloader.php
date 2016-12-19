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
     * @param int $timeOutInSeconds
     *
     * @return $this
     */
    public function setTimeout(int $timeOutInSeconds)
    {
        $this->timeout = $timeOutInSeconds;

        return $this;
    }

    public function forHost(string $hostName): SslCertificate
    {
        $hostName = (new Url($hostName))->getHostName();

        $streamContext = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
            ],
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

        $certificateFields = openssl_x509_parse($response['options']['ssl']['peer_certificate']);

        return new SslCertificate($certificateFields);
    }

    public static function downloadCertificateFromUrl(string $url, int $timeout = 30): SslCertificate
    {
        return (new static())
            ->setTimeout($timeout)
            ->forHost($url);
    }
}

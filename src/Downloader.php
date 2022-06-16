<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Spatie\SslCertificate\Exceptions\InvalidIpAddress;

class Downloader
{
    protected int $port = 443;

    protected ?string $ipAddress = null;

    protected bool $isIPv6 = false;

    protected bool $usingIpAddress = false;

    protected int $timeout = 30;

    protected bool $enableSni = true;

    protected bool $capturePeerChain = false;

    protected array $socketContextOptions = [];

    protected bool $verifyPeer = true;

    protected bool $verifyPeerName = true;

    protected int $followLocation = 1;

    public function usingPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function usingSni(bool $sni): self
    {
        $this->enableSni = $sni;

        return $this;
    }

    public function withSocketContextOptions(array $socketContextOptions): self
    {
        $this->socketContextOptions = $socketContextOptions;

        return $this;
    }

    public function withFullChain(bool $fullChain): self
    {
        $this->capturePeerChain = $fullChain;

        return $this;
    }

    public function withVerifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;

        return $this;
    }

    public function withVerifyPeerName(bool $verifyPeerName): self
    {
        $this->verifyPeerName = $verifyPeerName;

        return $this;
    }

    public function setTimeout(int $timeOutInSeconds): self
    {
        $this->timeout = $timeOutInSeconds;

        return $this;
    }

    public function setFollowLocation(int $followLocation): self
    {
        $this->followLocation = $followLocation;

        return $this;
    }

    public function fromIpAddress(string $ipAddress): self
    {
        $isValidIPv4 = filter_var($ipAddress, FILTER_VALIDATE_IP) !== false;
        $isValidIPv6 = filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        if (! $isValidIPv4 && ! $isValidIPv6) {
            throw InvalidIpAddress::couldNotValidate($ipAddress);
        }
        if ($isValidIPv6) {
            $this->isIPv6 = true;
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

    public function forHost(string $hostName): SslCertificate | bool
    {
        $url = new Url($hostName);

        $this->port = $url->getPort();

        $hostName = $url->getHostName();

        $certificates = $this->getCertificates($hostName);

        return $certificates[0] ?? false;
    }

    public static function downloadCertificateFromUrl(string $url, int $timeout = 30, bool $verifyCertificate = true): SslCertificate | bool
    {
        return (new static())
            ->setTimeout($timeout)
            ->withVerifyPeer($verifyCertificate)
            ->withVerifyPeerName($verifyCertificate)
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
            'follow_location' => $this->followLocation,
        ];

        $streamContext = stream_context_create([
            'socket' => $this->socketContextOptions,
            'ssl' => $sslOptions,
        ]);

        if ($this->usingIpAddress) {
            $connectTo = ($this->isIPv6) ? "[" . $this->ipAddress . ']' : $this->ipAddress;
        } else {
            $connectTo = $hostName;
        }

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

    protected function buildFailureException(string $hostName, string $errorDescription): CouldNotDownloadCertificate
    {
        if (str_contains($errorDescription, 'getaddrinfo') && str_contains($errorDescription, 'failed')) {
            return CouldNotDownloadCertificate::hostDoesNotExist($hostName);
        }

        if (str_contains($errorDescription, 'error:14090086')) {
            return CouldNotDownloadCertificate::noCertificateInstalled($hostName);
        }

        return CouldNotDownloadCertificate::unknownError($hostName, $errorDescription);
    }
}

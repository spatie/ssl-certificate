<?php

use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\HostDoesNotExist;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate\UnknownError;
use Spatie\SslCertificate\SslCertificate;

beforeEach(function () {
    $this->domainWithDifferentPort = 'psd2.b2b.belfius.be';
    $this->ipDomainWithDifferentPort = '141.96.1.12';
    $this->differentPort = 8443;
});

it('can download a certificate from a host name', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

it('can download a certificate from a host name with hostport ', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

it('can download a certificate from a host name with strange characters', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('https://www.hüpfburg.de');

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

test('can download a certificate from a host name with strange characters with hostport', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('https://' . $this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

it('can download a certificate for a host name from an ip address', function () {
    $sslCertificate = SslCertificate::download()
        ->fromIpAddress('164.92.244.169')
        ->forHost('spatie.be');

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

it('can download a certificate for a host name from an ip address with hostport', function () {
    $sslCertificate = SslCertificate::download()
        ->fromIpAddress($this->ipDomainWithDifferentPort)
        ->forHost($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
});

it('can download a certificate for a host name from an ipv6 address', function () {
    $sslCertificate = SslCertificate::download()
        ->fromIpAddress('2607:f8b0:4003:c00::6a')
        ->forHost('google.com');

    expect($sslCertificate)->toBeInstanceOf(SslCertificate::class);
})->todo('Find an IPv6 address that works');

it('sets a fingerprint on the downloaded certificate', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

    expect($sslCertificate->getFingerprint())->notg19->toBeEmpty();
});

it('sets a fingerprint on the downloaded certificate with hostport', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($sslCertificate->getFingerprint())->not->toBeEmpty();
});

it('can download all certificates from a host name', function () {
    $sslCertificates = (new Downloader())->getCertificates('spatie.be');

    expect($sslCertificates)->toHaveCount(1);
});

it('can download all certificates from a host name with socket context options', function () {
    $sslCertificates = (new Downloader())
        ->withSocketContextOptions([
            'bindto' => '0:0',
        ])
        ->getCertificates('spatie.be');

    expect($sslCertificates)->toHaveCount(1);
});

it('throws an exception for non existing host')
    ->tap(fn () => Downloader::downloadCertificateFromUrl('spatie-non-existing.be'))
    ->throws(HostDoesNotExist::class);

it('throws an exception when downloading a certificate from a host that contains none')
    ->tap(fn () => Downloader::downloadCertificateFromUrl('3564020356.org'))
    ->throws(UnknownError::class);

it('throws an exception when downloading a certificate for a missing host name from an ip address', function () {
    $sslCertificate = SslCertificate::download()
        ->fromIpAddress('138.197.187.74')
        ->forHost('fake.subdomain.spatie.be');
})->throws(UnknownError::class);

it('can retrieve the ip address of the server that served the certificates', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl('spatie.be');

    expect($sslCertificate->getRemoteAddress())->toEqual('164.92.244.169:443');
});

it('can retrieve the ip address of the server that served the certificates with hostport', function () {
    $sslCertificate = Downloader::downloadCertificateFromUrl($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($sslCertificate->getRemoteAddress())->toEqual($this->ipDomainWithDifferentPort . ':' . $this->differentPort);
});

<?php

use Carbon\Carbon;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

use Spatie\SslCertificate\SslCertificate;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create('2020', '01', '13', '03', '18', '13', 'utc'));

    $certificate = file_get_contents(__DIR__ . '/stubs/spatieCertificate.pem');

    $this->certificate = SslCertificate::createFromString($certificate);

    $this->domainWithDifferentPort = 'psd2.b2b.belfius.be';
    $this->differentPort = 8443;
});

it('can determine the issuer')
    ->expect(fn () => $this->certificate->getIssuer())
    ->toEqual("Let's Encrypt Authority X3");

it('can determine the organization')
    ->expect(fn () => $this->certificate->getOrganization())
    ->toEqual("Let's Encrypt");

it('can determine the domain')
    ->expect(fn () => $this->certificate->getDomain())
    ->toEqual('analytics.spatie.be');

it('can determine the signature algorithm')
    ->expect(fn () => $this->certificate->getSignatureAlgorithm())
    ->toEqual('RSA-SHA256');

it('can determine the additional domains')
    ->expect(fn () => $this->certificate->getAdditionalDomains())->toHaveCount(1)
    ->and(fn () => $this->certificate->getAdditionalDomains()[0])->toEqual('analytics.spatie.be');

it('can determine the valid from date')
    ->expect(fn () => $this->certificate->validFromDate())->toBeInstanceOf(Carbon::class)
    ->and(fn () => $this->certificate->validFromDate()->format('Y-m-d H:i:s'))->toEqual('2020-01-13 03:18:13');

it('can determine the expiration date')
    ->expect(fn () => $this->certificate->expirationDate())->toBeInstanceOf(Carbon::class)
    ->and(fn () => $this->certificate->expirationDate()->format('Y-m-d H:i:s'))->toEqual('2020-04-12 03:18:13');

it('can determine if the certificate is valid', function () {
    Carbon::setTestNow(Carbon::create('2016', '05', '19', '16', '45', '00', 'utc'));
    expect($this->certificate->isValid())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '51', '00', 'utc'));
    expect($this->certificate->isValid())->toBeTrue();

    Carbon::setTestNow(Carbon::create('2020', '03', '17', '16', '49', '00', 'utc'));
    expect($this->certificate->isValid())->toBeTrue();

    Carbon::setTestNow(Carbon::create('2016', '08', '17', '16', '51', '00', 'utc'));
    expect($this->certificate->isValid())->toBeFalse();
});

it('can determine if the certificate is expired', function () {
    Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '45', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2020', '02', '13', '16', '51', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2020', '02', '17', '16', '49', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeFalse();

    Carbon::setTestNow(Carbon::create('2020', '08', '17', '16', '51', '00', 'utc'));
    expect($this->certificate->isExpired())->toBeTrue();
});

it('provides a fluent interface to set all options', function () {
    $downloadedCertificate = SslCertificate::download()
        ->usingPort(443)
        ->setTimeout(30)
        ->forHost('spatie.be');

    expect($downloadedCertificate->getDomain())->toEqual('spatie.be');
});

it('provides a fluent interface to set all options with hostport', function () {
    $downloadedCertificate = SslCertificate::download()
        ->setTimeout(30)
        ->forHost($this->domainWithDifferentPort . ':' . $this->differentPort);

    expect($downloadedCertificate->getDomain())->toEqual($this->domainWithDifferentPort);
});

it('can convert the certificate to json', function () {
    assertMatchesJsonSnapshot($this->certificate->getRawCertificateFieldsJson());
})->skip(getenv('GITHUB_ACTIONS'), 'Github Actions has different output');

it('can convert the certificate to a string', function () {
    expect($this->certificate->getRawCertificateFieldsJson())
        ->toEqual((string) $this->certificate);
});

it('can get the hash of a certificate', function() {
    expect($this->certificate->getHash())->toEqual('0547c1a78dcdbe96f907aaaf42db5b8f');
})->skip(getenv('GITHUB_ACTIONS'), 'Github Actions results in different output');

it('can get all domains')
    ->expect(fn () => $this->certificate->getDomains())
    ->toEqual([
        0 => 'analytics.spatie.be',
    ]);

it('can get the days until the expiration date')
    ->expect(fn () => $this->certificate->daysUntilExpirationDate())
    ->toEqual(90);

it('can determine if it is self signed')
    ->expect(fn () => $this->certificate->isSelfSigned())
    ->toBeFalse();

it('can determine if it uses sha1 hasing')
    ->expect(fn () => $this->certificate->usesSha1Hash())
    ->toBeFalse();

it('can determine if the certificate has a certain domain', function () {
    expect($this->certificate->containsDomain('analytics.spatie.be'))->toBeTrue()
        ->and([
            $this->certificate->containsDomain('www.example.com'),
            $this->certificate->containsDomain('notreallyspatie.be'),
            $this->certificate->containsDomain('spatie.be.example.com'),
        ])->each->toBeFalse();
});

it('does not notify on wrong domains', function () {
    $rawCertificateFields = json_decode(
        file_get_contents(__DIR__ . '/stubs/certificateWithRandomWildcardDomains.json'),
        true
    );

    $this->certificate = new SslCertificate($rawCertificateFields);

    expect($this->certificate->appliesToUrl('https://coinmarketcap.com'))->toBeFalse();
});

it('correctly compares uppercase domain names', function () {
    $rawCertificateFields = json_decode(
        file_get_contents(__DIR__ . '/stubs/certificateWithUppercaseDomains.json'),
        true
    );

    $this->certificate = new SslCertificate($rawCertificateFields);

    expect($this->certificate->appliesToUrl('spatie.be'))->toBeTrue()
        ->and($this->certificate->appliesToUrl('www.spatie.be'))->toBeTrue();
});

it('correctly identifies pre certificates', function () {
    $rawCertificateFieldsNormalCertificate = json_decode(
        file_get_contents(__DIR__ . '/stubs/spatieCertificateFields.json'),
        true
    );

    $rawCertificateFieldsPreCertificate = json_decode(
        file_get_contents(__DIR__ . '/stubs/preCertificate.json'),
        true
    );

    $certificateNormal = new SslCertificate($rawCertificateFieldsNormalCertificate);
    $certificatePreCertificate = new SslCertificate($rawCertificateFieldsPreCertificate);

    expect($certificateNormal->isPreCertificate())->toBeFalse()
        ->and($certificatePreCertificate->isPreCertificate())->toBeTrue();
});

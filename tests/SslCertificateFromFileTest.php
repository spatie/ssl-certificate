<?php

use Carbon\Carbon;
use Spatie\SslCertificate\SslCertificate;

beforeEach(function () {
    Carbon::setTestNow(Carbon::create('2020', '01', '13', '03', '18', '13', 'utc'));
});

it('can load pem certificate')
    ->defer(fn () => $this->certificate = SslCertificate::createFromFile(__DIR__ . '/stubs/spatieCertificate.pem'))
    ->expect(fn () => $this->certificate->getOrganization())->toEqual("Let's Encrypt")
    ->and(fn () => $this->certificate->getDomain())->toEqual("analytics.spatie.be");

it('can load der certificate')
    ->defer(fn () => $this->certificate = SslCertificate::createFromFile(__DIR__ . '/stubs/derCertificate.der'))
    ->expect(fn () => $this->certificate->getOrganization())->toEqual("Let's Encrypt")
    ->and(fn () => $this->certificate->getDomain())->toEqual("analytics.spatie.be");

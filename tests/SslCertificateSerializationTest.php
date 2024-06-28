<?php

use Carbon\Carbon;
use Spatie\SslCertificate\SslCertificate;

it('cannot json encode certificate array data', function() {
    json_encode(
        SslCertificate::createFromFile(__DIR__ . '/stubs/spatieCertificate.pem')->toArray()
    );
    
    expect(json_last_error_msg())->toEqual('Malformed UTF-8 characters, possibly incorrectly encoded');
});

it('can json encode serialized certificate', function() {
    $json = json_encode(
        serialize(SslCertificate::createFromFile(__DIR__ . '/stubs/spatieCertificate.pem'))
    );
    
    expect(json_last_error_msg())->toEqual('No error');
});

it('can unserialize serialized certificate', function() {
    $serialized = serialize(SslCertificate::createFromFile(__DIR__ . '/stubs/spatieCertificate.pem'));

    $unserialized = unserialize($serialized);

    expect($unserialized->getDomain())->toEqual("analytics.spatie.be");
});
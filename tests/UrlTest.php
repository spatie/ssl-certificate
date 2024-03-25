<?php

use Spatie\SslCertificate\Exceptions\InvalidUrl;
use Spatie\SslCertificate\Url;

it('can determine a host name', function () {
    $url = new Url('https://spatie.be/opensource');

    expect($url->getHostName())->toEqual('spatie.be');
});

it('can determine a host name when not specifying a protocol', function () {
    $url = new Url('spatie.be');

    expect($url->getHostName())->toEqual('spatie.be');
});

it('throws an exception when creating an url from an empty string')
    ->defer(fn () => new Url(''))
    ->throws(InvalidUrl::class);

it('can assume a default port when not explicitly defined', function () {
    $url = new Url('spatie.be');

    expect($url->getPort())->toEqual(443);
});

it('can retrieve the custom port when defined', function () {
    $url = new Url('https://spatie.be:12345');

    expect($url->getPort())->toEqual(12345);
});

it('can parse really long paths', function () {
    $url = new Url('https://random.host/this-is-a-very/and-i-mean-very/long-path/to-work/with/and-somehow/the-idna-functions-in-php/are-limited-to/61-chars/yes?really=true&ohmy');

    expect($url->getHostName())->toEqual('random.host');
});

<?php

use function Spatie\SslCertificate\ends_with;
use function Spatie\SslCertificate\length;
use function Spatie\SslCertificate\starts_with;
use function Spatie\SslCertificate\str_contains;

it('can determine if a string starts with a given string', function () {
    expect([
        starts_with('jason', 'jas'),
        starts_with('jason', 'jason'),
        starts_with('jason', ['jas']),
        starts_with('jason', ['day', 'jas'])
    ])->each->toBeTrue();

    expect([
        starts_with('jason', 'day'),
        starts_with('jason', ['day']),
        starts_with('jason', '')
    ])->each->toBeFalse();
});

it('can determine if a string end with a given string', function () {
    expect([
        ends_with('jason', 'on'),
        ends_with('jason', 'jason'),
        ends_with('jason', ['on']),
        ends_with('jason', ['no', 'on'])
    ])->each->toBeTrue()
        ->and([
            ends_with('jason', 'no'),
            ends_with('jason', ['no']),
            ends_with('jason', ''),
            ends_with('7', ' 7')
        ])->each->toBeFalse();
});

it('can create substring of a given stirng', function () {
    expect(\Spatie\SslCertificate\substr('БГДЖИЛЁ', -1))->toEqual('Ё')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', -2))->toEqual('ЛЁ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', -3, 1))->toEqual('И')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 2, -1))->toEqual('ДЖИЛ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 4, -4))->toBeEmpty()
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', -3, -1))->toEqual('ИЛ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 1))->toEqual('ГДЖИЛЁ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 1, 3))->toEqual('ГДЖ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 0, 4))->toEqual('БГДЖ')
        ->and(\Spatie\SslCertificate\substr('БГДЖИЛЁ', -1, 1))->toEqual('Ё')
        ->and(\Spatie\SslCertificate\substr('Б', 2))->toBeEmpty();
});

it('can determine the lenght of a string', function () {
    expect(length('foo bar baz'))->toEqual(11);
});

it('can determine if a string str contains another string', function () {
    expect([
        str_contains('taylor', 'ylo'),
        str_contains('taylor', 'taylor'),
        str_contains('taylor', ['ylo']),
        str_contains('taylor', ['xxx', 'ylo']),
    ])->each->toBeTrue()
        ->and([
            str_contains('taylor', 'xxx'),
            str_contains('taylor', ['xxx']),
            str_contains('taylor', '')
        ])->each->toBeFalse();
});

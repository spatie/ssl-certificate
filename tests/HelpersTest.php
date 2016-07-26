<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit_Framework_TestCase;

class HelpersTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_determine_if_a_string_starts_with_a_test()
    {
        $this->assertTrue(\Spatie\SslCertificate\starts_with('jason', 'jas'));
        $this->assertTrue(\Spatie\SslCertificate\starts_with('jason', 'jason'));
        $this->assertTrue(\Spatie\SslCertificate\starts_with('jason', ['jas']));
        $this->assertTrue(\Spatie\SslCertificate\starts_with('jason', ['day', 'jas']));
        $this->assertFalse(\Spatie\SslCertificate\starts_with('jason', 'day'));
        $this->assertFalse(\Spatie\SslCertificate\starts_with('jason', ['day']));
        $this->assertFalse(\Spatie\SslCertificate\starts_with('jason', ''));
    }
}

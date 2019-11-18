<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;
use function Spatie\SslCertificate\ends_with;
use function Spatie\SslCertificate\length;
use function Spatie\SslCertificate\starts_with;
use function Spatie\SslCertificate\str_contains;

class HelpersTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_string_starts_with_a_given_string()
    {
        $this->assertTrue(starts_with('jason', 'jas'));
        $this->assertTrue(starts_with('jason', 'jason'));
        $this->assertTrue(starts_with('jason', ['jas']));
        $this->assertTrue(starts_with('jason', ['day', 'jas']));
        $this->assertFalse(starts_with('jason', 'day'));
        $this->assertFalse(starts_with('jason', ['day']));
        $this->assertFalse(starts_with('jason', ''));
    }

    /** @test */
    public function it_can_determine_if_a_string_end_with_a_given_string()
    {
        $this->assertTrue(ends_with('jason', 'on'));
        $this->assertTrue(ends_with('jason', 'jason'));
        $this->assertTrue(ends_with('jason', ['on']));
        $this->assertTrue(ends_with('jason', ['no', 'on']));
        $this->assertFalse(ends_with('jason', 'no'));
        $this->assertFalse(ends_with('jason', ['no']));
        $this->assertFalse(ends_with('jason', ''));
        $this->assertFalse(ends_with('7', ' 7'));
    }

    /** @test */
    public function it_can_create_substring_of_a_given_stirng()
    {
        $this->assertEquals('Ё', \Spatie\SslCertificate\substr('БГДЖИЛЁ', -1));
        $this->assertEquals('ЛЁ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', -2));
        $this->assertEquals('И', \Spatie\SslCertificate\substr('БГДЖИЛЁ', -3, 1));
        $this->assertEquals('ДЖИЛ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', 2, -1));
        $this->assertEmpty(\Spatie\SslCertificate\substr('БГДЖИЛЁ', 4, -4));
        $this->assertEquals('ИЛ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', -3, -1));
        $this->assertEquals('ГДЖИЛЁ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', 1));
        $this->assertEquals('ГДЖ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', 1, 3));
        $this->assertEquals('БГДЖ', \Spatie\SslCertificate\substr('БГДЖИЛЁ', 0, 4));
        $this->assertEquals('Ё', \Spatie\SslCertificate\substr('БГДЖИЛЁ', -1, 1));
        $this->assertEmpty(\Spatie\SslCertificate\substr('Б', 2));
    }

    /** @test */
    public function it_can_determine_the_lenght_of_a_string()
    {
        $this->assertEquals(11, length('foo bar baz'));
    }

    /** @test */
    public function it_can_determine_if_a_string_str_contains_another_string()
    {
        $this->assertTrue(str_contains('taylor', 'ylo'));
        $this->assertTrue(str_contains('taylor', 'taylor'));
        $this->assertTrue(str_contains('taylor', ['ylo']));
        $this->assertTrue(str_contains('taylor', ['xxx', 'ylo']));
        $this->assertFalse(str_contains('taylor', 'xxx'));
        $this->assertFalse(str_contains('taylor', ['xxx']));
        $this->assertFalse(str_contains('taylor', ''));
    }
}

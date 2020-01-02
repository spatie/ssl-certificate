<?php

namespace Spatie\SslCertificate\Test;

use PHPUnit\Framework\TestCase;

class LocalTest extends TestCase
{
    private function getCertificate($path):string
    {
        return file_get_contents(__DIR__ . '/stubs/' . $path);
    }

    public function it_can_parse_a_valid_certificate()
    {}

    public function it_cannot_parse_a_empty_certificate()
    {}

    public function it_can_find_a_certificate_from_valid_path()
    {}

    public function it_cannot_find_a_certificate_from_non_existing_path()
    {}

    public function it_can_read_from_a_certificate_file_with_permission()
    {}

    public function it_cannot_read_from_a_certificate_file_without_permission()
    {}

    public function it_cannot_parse_a_invalid_certificate()
    {}
}
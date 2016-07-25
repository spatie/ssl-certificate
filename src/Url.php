<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\InvalidUrl;

class Url
{
    /** @var string */
    protected $url;

    /** @var array */
    protected $parsedUrl;

    public function __construct(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw InvalidUrl::couldNotValidate($url);
        }

        $this->url = $url;

        $this->parsedUrl = parse_url($url);
    }

    public function getHostName()
    {
        if (!isset($this->parsedUrl['host'])) {
            throw InvalidUrl::couldNotDetermineHost($this->url);
        }
    }
}

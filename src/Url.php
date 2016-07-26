<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\InvalidUrl;
use Spatie\SslCertificate\starts_with;

class Url
{
    /** @var string */
    protected $url;

    /** @var array */
    protected $parsedUrl;

    public function __construct(string $url)
    {
        if (!starts_with($url, ['http://', 'https://'])) {
            $url = "https://{$url}";
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw InvalidUrl::couldNotValidate($url);
        }

        $this->url = $url;

        $this->parsedUrl = parse_url($url);
    }

    public function getHostName(): string
    {
        if (!isset($this->parsedUrl['host'])) {
            throw InvalidUrl::couldNotDetermineHost($this->url);
        }

        return $this->parsedUrl['host'];
    }
}

<?php

namespace Spatie\SslCertificate;

use Spatie\SslCertificate\Exceptions\InvalidUrl;

class Url
{
    /**
     * @var string
     *
     * @see \GuzzleHttp\Psr7\Uri::getAuthority()
     */
    protected $authority = '';

    /**
     * @var string[]
     */
    protected $components = [];

    public function __construct(string $url)
    {
        $this->components = parse_url($url);
        $this->components = $this->components === false ? [] : array_map('trim', $this->components);

        if (empty($this->components['host']) && empty($this->components['path'])) {
            throw InvalidUrl::couldNotDetermineHost($url);
        }

        $this->components += [
          'host' => '',
          'port' => '',
          'user' => '',
          'pass' => '',
          'path' => '',
          'query' => '',
          'scheme' => 'https',
          'fragment' => '',
        ];

        $this->computeAuthority();

        if (! filter_var((string) $this, FILTER_VALIDATE_URL)) {
            throw InvalidUrl::couldNotValidate($url);
        }
    }

    public function __toString(): string
    {
        $string = $this->components['scheme'].'://'.$this->authority.$this->components['path'];

        if (! empty($this->components['query'])) {
            $string .= '?'.$this->components['query'];
        }

        if (! empty($this->components['fragment'])) {
            $string .= '#'.$this->components['fragment'];
        }

        return $string;
    }

    public function getHostName(): string
    {
        return $this->components['host'];
    }

    public function getPort(): int
    {
        return $this->components['port'] ?: 443;
    }

    /**
     * Computes the authority path.
     *
     * @internal
     */
    protected function computeAuthority()
    {
        if ($this->components['user'] !== '') {
            $this->authority .= $this->components['user'];

            if ($this->components['pass'] !== '') {
                $this->authority .= ':'.$this->components['pass'];
            }

            $this->authority .= '@';
        }

        // If "example.com" or "localhost" or something similar passed to the
        // constructor the "host" will be empty and the actual value will in
        // the "path".
        if ($this->components['host'] === '') {
            list($this->components['host'], $this->components['path']) = explode('/', $this->components['path'].'/', 2);

            $this->components['path'] = '/'.ltrim($this->components['path'], '/');
        }

        if (function_exists('idn_to_ascii')) {
            // Transform "économiessanté.ca" to "xn--conomiessant-9dbm.ca". Otherwise,
            // the hostname cannot be reached.
            $this->components['host'] = idn_to_ascii($this->components['host'], 0, INTL_IDNA_VARIANT_UTS46)
              ?: $this->components['host'];
        }

        $this->authority .= $this->components['host'];

        if ($this->components['port'] !== '') {
            $this->authority .= ':'.$this->components['port'];
        }

        $this->authority = trim($this->authority);
    }
}

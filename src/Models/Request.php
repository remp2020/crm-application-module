<?php

namespace Crm\ApplicationModule\Models;

use Nette\Http\Request as HttpRequest;

class Request
{
    private string $cookieDomain;

    public function __construct(
        private readonly HttpRequest $httpRequest,
    ) {
    }

    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'cli';
        }
        return $ip;
    }

    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function getCookieDomain(): string
    {
        if (isset($this->cookieDomain)) {
            return $this->cookieDomain;
        }

        // attempt to resolve automatically, will not work with multi-level TLDs (.co.uk, .com.ua, ...)
        $cookieDomain = $this->httpRequest->getUrl()->getHost();

        if (($index = strrpos($cookieDomain, '.')) !== false) {
            // remove subdomain from host so we store the cookie as broadly as possible
            if (($index = strrpos($cookieDomain, '.', $index - strlen($cookieDomain) - 1)) !== false) {
                $cookieDomain = substr($cookieDomain, $index);
            }
        }

        return $cookieDomain;
    }

    public function setCookieDomain(string $cookieDomain): void
    {
        $this->cookieDomain = $cookieDomain;
    }

    public static function isApi()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return false;
        }
        return preg_match('#^/api/v\d+/#', $_SERVER['REQUEST_URI']);
    }
}

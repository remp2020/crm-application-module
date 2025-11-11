<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Request;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public static function cookieDomainDataProvider()
    {
        return [
            // Regular domains
            ['host' => 'example.com', 'domain' => 'example.com'],
            ['host' => 'foo.example.com', 'domain' => '.example.com'],
            ['host' => 'foo.bar.example.com:8080', 'domain' => '.example.com'],
            ['host' => 'invalid_host', 'domain' => 'invalid_host'],

            // Edge cases
            ['host' => 'localhost', 'domain' => 'localhost'],
            ['host' => 'localhost:8080', 'domain' => 'localhost'],
            ['host' => 'invalid_host', 'domain' => 'invalid_host'],

            // Multi-part TLD cases are not supported at the moment. Configure applicationRequest in config.neon
            // and add "setup" directive with specific domain instead as shown in testConfiguredCookieDomain.
//            ['host' => 'example.com.ua', 'domain' => '.example.com.ua'],
//            ['host' => 'foo.example.com.ua', 'domain' => '.example.com.ua'],
//            ['host' => 'example.co.uk', 'domain' => '.example.co.uk'],
//            ['host' => 'test.co.uk', 'domain' => '.test.co.uk'],

        ];
    }

    #[DataProvider('cookieDomainDataProvider')]
    public function testHostBasedCookieDomain(string $host, string $domain): void
    {
        $url = $host ? 'https://' . $host : 'https://';
        $httpRequest = new HttpRequest(new UrlScript($url));
        $request = new Request($httpRequest);

        $result = $request->getCookieDomain();
        $this->assertEquals($domain, $result);
    }

    #[DataProvider('cookieDomainDataProvider')]
    public function testConfiguredCookieDomain(string $host, string $domain): void
    {
        $url = $host ? 'https://' . $host : 'https://';
        $httpRequest = new HttpRequest(new UrlScript($url));
        $cookieDomain = '.cookie.crm.press';

        $request = new Request($httpRequest);
        $request->setCookieDomain($cookieDomain);

        $result = $request->getCookieDomain();
        $this->assertEquals($cookieDomain, $result);
    }
}

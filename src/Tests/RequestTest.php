<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        unset($_REQUEST['HTTP_HOST']);
    }

    public static function emptySeriesDataProvider()
    {
        return [
            ['host' => 'example.com', 'domain' => 'example.com'],
            ['host' => 'foo.example.com', 'domain' => '.example.com'],
            ['host' => 'foo.bar.example.com:8080', 'domain' => '.example.com'],
            ['host' => 'invalid_host', 'domain' => 'invalid_host'],
        ];
    }

    #[DataProvider('emptySeriesDataProvider')]
    public function testGetDomain(string $host, string $domain): void
    {
        $_SERVER['HTTP_HOST'] = $host;
        $this->assertEquals(
            $domain,
            Request::getDomain(),
        );
    }
}

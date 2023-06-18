<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        unset($_REQUEST['HTTP_HOST']);
    }

    public function emptySeriesDataProvider()
    {
        return [
            ['url' => 'example.com', 'example.com'],
            ['url' => 'foo.example.com', '.example.com'],
            ['url' => 'foo.bar.example.com:8080', '.example.com'],
            ['url' => 'invalid_host', 'invalid_host'],
        ];
    }

    /** @dataProvider emptySeriesDataProvider */
    public function testGetDomain(string $host, string $domain): void
    {
        $_SERVER['HTTP_HOST'] = $host;
        $this->assertEquals(
            $domain,
            Request::getDomain(),
        );
    }
}

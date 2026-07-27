<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Commands\DumpSchemaForTests;
use PHPUnit\Framework\TestCase;

class DumpSchemaForTestsTest extends TestCase
{
    public function testNormalizeCreateTable(): void
    {
        $createTable = <<<'SQL'
        CREATE TABLE `users` (
          `id` int NOT NULL AUTO_INCREMENT,
          `email` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=12345 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

        $expected = <<<'SQL'
        CREATE TABLE `users` (
            `id` int NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL;

        $this->assertSame($expected, DumpSchemaForTests::normalizeCreateTable($createTable));
    }
}

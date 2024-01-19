<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Database\ReplicaConfig;
use Crm\ApplicationModule\Models\Database\ReplicaManager;
use Nette\Database\Connection;
use Nette\Database\Explorer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReplicaManagerTest extends TestCase
{
    #[DataProvider('replicaTraitDataProvider')]
    public function testReplicaTrait(string $table, array $dsns, array $allowedTables, bool $mightReturnReplica)
    {
        $replicaConfig = new ReplicaConfig();
        foreach ($dsns as $dsn) {
            $replicaConfig->addReplica($this->mockDatabase($dsn));
        }
        foreach ($allowedTables as $allowedTable) {
            $replicaConfig->addTable($allowedTable);
        }
        $replicaManager = new ReplicaManager(
            $this->mockDatabase('primary'),
            $table,
            $replicaConfig
        );

        // test "read" and verify we receive one of the allowed connections
        $db = $replicaManager->getDatabase(true);

        if ($mightReturnReplica) {
            $dsns = array_merge($dsns, ['primary']);
            $this->assertContains($db->getConnection()->getDsn(), $dsns);
        } else {
            $this->assertEquals('primary', $db->getConnection()->getDsn());
        }

        // test "write" and verify we always receive "primary" connection

        $replicaManager->setWriteFlag();

        $db = $replicaManager->getDatabase(true);
        $this->assertEquals('primary', $db->getConnection()->getDsn());
    }

    public static function replicaTraitDataProvider()
    {
        return [
            'NoReplica_TableNotAllowed_ShouldReturnPrimary' => [
                'table' => 'foo',
                'dsns' => [],
                'allowedTables' => [],
                'mightReturnReplica' => false,
            ],
            'MultiReplica_TableNotAllowed_ShouldReturnPrimary' => [
                'table' => 'foo',
                'dsns' => [
                    'secondary',
                    'tertiary',
                ],
                'allowedTables' => ['bar', 'baz'],
                'mightReturnReplica' => false,
            ],
            'MultiReplica_TableAllowed_CouldReturnReplica' => [
                'table' => 'foo',
                'dsns' => [
                    'secondary',
                    'tertiary',
                ],
                'allowedTables' => ['foo', 'baz'],
                'mightReturnReplica' => true,
            ],
            'MultiReplica_TableAllowed_ShouldReturnPrimary' => [
                'table' => 'foo',
                'dsns' => [],
                'allowedTables' => ['foo', 'baz'],
                'mightReturnReplica' => false,
            ],
        ];
    }

    public function mockDatabase(string $dsn)
    {
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('getDsn')
            ->andReturn($dsn);
        $database = \Mockery::mock(Explorer::class);
        $database->shouldReceive('getConnection')
            ->andReturn($connection);
        return $database;
    }
}

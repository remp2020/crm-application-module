<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\SeederManager;
use Crm\ApplicationModule\Seeders\ISeeder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class SeederManagerTest extends TestCase
{
    /** @var SeederManager */
    private $seederManager;

    protected function setUp(): void
    {
        $this->seederManager = new SeederManager();
    }

    public function testAddAndGetSeeders(): void
    {
        $seederA = new class implements ISeeder {
            public function seed(OutputInterface $output)
            {
            }
        };

        $seederB = new class implements ISeeder {
            public function seed(OutputInterface $output)
            {
            }
        };

        $seederC = new class implements ISeeder {
            public function seed(OutputInterface $output)
            {
            }
        };

        $seederD = new class implements ISeeder {
            public function seed(OutputInterface $output)
            {
            }
        };

        $this->seederManager->addSeeder($seederA, 50);
        $this->seederManager->addSeeder($seederB, 200);
        $this->seederManager->addSeeder($seederC, 10);
        $this->seederManager->addSeeder($seederD, 200);

        $this->assertCount(4, $this->seederManager->getSeeders());
        $this->assertEquals([$seederC, $seederA, $seederB, $seederD], $this->seederManager->getSeeders());
    }
}

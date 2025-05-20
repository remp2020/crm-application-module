<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Database\SlugColumnException;
use Crm\ApplicationModule\Models\Database\SlugColumnTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    #[DataProvider('slugColumnTraitDataProvider')]
    public function testSlugColumnTrait(array $slugs, array $data, bool $exception = false)
    {
        if ($exception) {
            $this->expectException(SlugColumnException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $dummyRepository = $this->getDummyRepository($slugs);
        $dummyRepository->test($data);
    }

    public static function slugColumnTraitDataProvider()
    {
        $data = [
            'id' => 123,
            'code' => 'lorem-ipsum',
            'description' => 'Lorem ipsum',
        ];

        return [
            'NoSlugColumn_ShouldValidate' => [
                'slugs' => [],
                'data' => $data,
            ],
            'MultiSlugColumns_ShouldValidate' => [
                'slugs' => ['code', 'other_code'],
                'data' => $data,
            ],
            'SingleSlugColumn_EmptySlug_ShouldValidate' => [
                'slugs' => ['code'],
                'data' => ['code' => null],
            ],
            'SingleSlugColumn_NoSlug_ShouldValidate' => [
                'slugs' => ['code'],
                'data' => [],
            ],
            'SingleSlugColumn_UnderscoreSlug_ShouldValidate' => [
                'slugs' => ['code'],
                'data' => ['code' => 'lorem_ipsum'],
            ],
            'MultiSlugColumns_ShouldNotValidate' => [
                'slugs' => ['code', 'description'],
                'data' => $data,
                'exception' => true,
            ],
        ];
    }

    private function getDummyRepository($slugs)
    {
        return new class($slugs)
        {
            use SlugColumnTrait;

            public function __construct($slugs)
            {
                $this->slugs = $slugs;
            }
            public function test($data)
            {
                $this->assertSlugs($data);
            }
        };
    }
}

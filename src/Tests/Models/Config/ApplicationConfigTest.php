<?php

namespace Crm\ApplicationModule\Tests\Models\Config;

use Crm\ApplicationModule\Application\Core;
use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\ApplicationModule\Models\Config\LocalConfig;
use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Crm\ApplicationModule\Seeders\ConfigsSeeder;
use Crm\ApplicationModule\Tests\DatabaseTestCase;
use Nette\Caching\Storage;

class ApplicationConfigTest extends DatabaseTestCase
{
    private ApplicationConfig $applicationConfig;

    private ConfigsRepository $configsRepository;

    public function requiredRepositories(): array
    {
        return [
            ConfigsRepository::class,
        ];
    }

    public function requiredSeeders(): array
    {
        return [
            ConfigsSeeder::class
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->applicationConfig = $this->inject(ApplicationConfig::class);
        $this->configsRepository = $this->getRepository(ConfigsRepository::class);
    }

    public function testGetCacheExpirationZero()
    {
        $siteUrlDefaultValue = Core::env('CRM_HOST');
        $siteUrlKey = 'site_url';

        // confirm that correctly seeded config value is in DB
        $siteTitleConfig = $this->configsRepository->findBy('name', $siteUrlKey);
        $this->assertEquals($siteUrlDefaultValue, $siteTitleConfig->value);

        // confirm that same config value is in cache
        $this->assertEquals($siteUrlDefaultValue, $this->applicationConfig->get($siteUrlKey));

        // ********************************************************************

        // set setCacheExpiration to 0 (all changes to configs should propagate immediately)
        $this->applicationConfig->setCacheExpiration(0);

        // update URL
        $newValue = 'http://testing-different-url.press';
        $this->configsRepository->update($siteTitleConfig, ['value' => $newValue]);

        // test if change propagated into cache
        $this->assertEquals($newValue, $this->applicationConfig->get($siteUrlKey)); // new config should be in cache
    }

    public function testGetCacheExpirationNonZero()
    {
        $siteUrlDefaultValue = Core::env('CRM_HOST');
        $siteUrlKey = 'site_url';

        // confirm that correctly seeded config value is in DB
        $siteTitleConfig = $this->configsRepository->findBy('name', $siteUrlKey);
        $this->assertEquals($siteUrlDefaultValue, $siteTitleConfig->value);

        // confirm that same config value is in cache
        $this->assertEquals($siteUrlDefaultValue, $this->applicationConfig->get($siteUrlKey));

        // ********************************************************************

        // set setCacheExpiration to 0 (all changes to configs should propagate immediately)
        $this->applicationConfig->setCacheExpiration(120);

        // update URL
        $newValue = 'http://testing-different-url.press';
        $this->configsRepository->update($siteTitleConfig, ['value' => $newValue]);

        // test if change propagated into cache
        $this->assertEquals($siteUrlDefaultValue, $this->applicationConfig->get($siteUrlKey)); // old config value is in cache
    }

    // Verify that we are not touching cache storage when cache expiration is set to ZERO.
    // Since we are mocking cache storage, this test is separate from above tests of cache itself.
    public function testGetCacheExpirationZeroCheckCacheStorageWriteRead()
    {
        $siteUrlDefaultValue = Core::env('CRM_HOST');
        $siteUrlKey = 'site_url';

        // set observer (mocked handler) to observe hermes handler
        // whole GenerateInvoiceHandler is tested by separate class GenerateInvoiceHandlerTest
        $cacheStorageObserver = $this->createMock(Storage::class);
        // handler should be received only once and should contain correct payment id
        $cacheStorageObserver->expects($this->never())
            ->method('read')
            ->with(ApplicationConfig::CACHE_KEY);
        $cacheStorageObserver->expects($this->never())
            ->method('write')
            ->with(ApplicationConfig::CACHE_KEY);

        $applicationConfig = new ApplicationConfig(
            $this->configsRepository,
            $this->inject(LocalConfig::class),
            $cacheStorageObserver,
        );

        $applicationConfig->setCacheExpiration(0);

        // confirm that correctly seeded config value is in DB
        $siteTitleConfig = $this->configsRepository->findBy('name', $siteUrlKey);
        $this->assertEquals($siteUrlDefaultValue, $siteTitleConfig->value);
        // confirm that same config value as in DB is in cache
        // this shouldn't internally call cache storage
        // (nor Storage->read(ApplicationConfig::CACHE_KEY) nor Storage->write(ApplicationConfig::CACHE_KEY))
        $this->assertEquals($siteUrlDefaultValue, $applicationConfig->get($siteUrlKey));
        // this is just random call to different config
        // this shouldn't internally call cache storage
        // (nor Storage->read(ApplicationConfig::CACHE_KEY) nor Storage->write(ApplicationConfig::CACHE_KEY))
        $this->assertNotNull($applicationConfig->get('site_title'));
    }

    // Verify that we are touching cache storage only once (first time) when cache expiration is NON ZERO.
    // Since we are mocking cache storage, this test is separate from above tests of cache itself.
    public function testGetCacheExpirationNonZeroCheckCacheStorageWriteRead()
    {
        $siteUrlDefaultValue = Core::env('CRM_HOST');
        $siteUrlKey = 'site_url';

        // set observer (mocked handler) to observe hermes handler
        // whole GenerateInvoiceHandler is tested by separate class GenerateInvoiceHandlerTest
        $cacheStorageObserver = $this->createMock(Storage::class);
        // handler should be received only once and should contain correct payment id
        $cacheStorageObserver->expects($this->once())
            ->method('read')
            ->with(ApplicationConfig::CACHE_KEY);
        $cacheStorageObserver->expects($this->once())
            ->method('write')
            ->with(ApplicationConfig::CACHE_KEY);

        $applicationConfig = new ApplicationConfig(
            $this->configsRepository,
            $this->inject(LocalConfig::class),
            $cacheStorageObserver,
        );

        $applicationConfig->setCacheExpiration(120);

        // confirm that correctly seeded config value is in DB
        $siteTitleConfig = $this->configsRepository->findBy('name', $siteUrlKey);
        $this->assertEquals($siteUrlDefaultValue, $siteTitleConfig->value);
        // confirm that same config value as in DB is in cache
        // this shouldn't internally call cache storage
        // (nor Storage->read(ApplicationConfig::CACHE_KEY) nor Storage->write(ApplicationConfig::CACHE_KEY))
        $this->assertEquals($siteUrlDefaultValue, $applicationConfig->get($siteUrlKey));
        // this is just random call to different config
        // this shouldn't internally call cache storage
        // (nor Storage->read(ApplicationConfig::CACHE_KEY) nor Storage->write(ApplicationConfig::CACHE_KEY))
        $this->assertNotNull($applicationConfig->get('site_title'));
    }
}

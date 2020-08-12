<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ContentModule\Access\UrlSetInterface;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;

// TODO: add UserDataProviders to check if modules are ready?
class HealthCheckPresenter extends FrontendPresenter
{
    // TODO: keeping this off so we can keep internal/health-check URL online
    public $autoCanonicalize = false;

    private $configsRepository;

    private $urlSetStorage;

    public function __construct(
        ConfigsRepository $configsRepository,
        UrlSetInterface $urlSetStorage
    ) {
        parent::__construct();
        $this->configsRepository = $configsRepository;
        $this->urlSetStorage = $urlSetStorage;
    }

    public function renderDefault()
    {
        // check db (fetching site_url which is always present in seeded DB)
        $siteUrl = $this->configsRepository->findBy('name', 'site_url');
        if (!$siteUrl) {
            throw new BadRequestException();
        }

        // check redis
        $this->urlSetStorage->addUrl('healthcheck', 'https://example.com/');
        $status = $this->urlSetStorage->urlExists('healthcheck', 'https://example.com/');
        if (!$status) {
            throw new BadRequestException();
        }
        $this->urlSetStorage->removeUrl('healthcheck', 'https://example.com/');

        $this->sendResponse(new TextResponse('ok'));
    }
}

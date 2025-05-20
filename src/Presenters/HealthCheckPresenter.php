<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Models\User\UserDataStorageInterface;
use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;

// TODO: add UserDataProviders to check if modules are ready?
class HealthCheckPresenter extends FrontendPresenter
{
    private $configsRepository;

    private $userDataStorage;

    public function __construct(
        ConfigsRepository $configsRepository,
        UserDataStorageInterface $userDataStorage,
    ) {
        parent::__construct();
        $this->configsRepository = $configsRepository;
        $this->userDataStorage = $userDataStorage;
    }

    public function renderDefault()
    {
        // check db (fetching site_url which is always present in seeded DB)
        $siteUrl = $this->configsRepository->findBy('name', 'site_url');
        if (!$siteUrl) {
            throw new BadRequestException();
        }

        // check redis
        $this->userDataStorage->store('healthcheck', 1);
        $status = $this->userDataStorage->load('healthcheck');
        if (!$status) {
            throw new BadRequestException();
        }
        $this->userDataStorage->remove('healthcheck');

        $this->sendResponse(new TextResponse('ok'));
    }
}

<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\ApplicationManager;
use Crm\ApplicationModule\Components\SimpleWidgetFactoryInterface;
use Crm\ApplicationModule\Components\SingleStatWidgetFactoryInterface;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Core;
use Crm\ApplicationModule\Events\AuthenticatedAccessRequiredEvent;
use Crm\ApplicationModule\Events\AuthenticationEvent;
use Crm\ApplicationModule\LayoutManager;
use Crm\ApplicationModule\Snippet\Control\SnippetFactory;
use Kdyby\Autowired\AutowireComponentFactories;
use Kdyby\Translation\Translator;
use League\Event\Emitter;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends Presenter
{
    use AutowireComponentFactories;

    /** @var  ApplicationManager @inject */
    public $applicationManager;

    /** @var  ApplicationConfig @inject */
    public $applicationConfig;

    /** @var LayoutManager @inject */
    public $layoutManager;

    /** @var Translator @inject */
    public $translator;

    /** @var Emitter @inject */
    public $emitter;

    public $locale;

    public $layoutPath;

    public $homeRoute;

    public function startup()
    {
        parent::startup();
        if ($this->getRequest()->hasFlag(\Nette\Application\Request::RESTORED)) {
            $this->redirect('this');
        }

        if ($this->getUser()->isLoggedIn()) {
            try {
                $this->emitter->emit(new AuthenticationEvent($this->getHttpRequest(), $this->getUser()->id));
            } catch (AuthenticationException $e) {
                $this->getUser()->logout(true);
                $this->flashMessage($e->getMessage(), 'warning');
                $this->redirect('this');
            }
        }

        $this->locale = $this->translator->getLocale();
        $this->homeRoute = ':' . $this->applicationConfig->get('home_route');
        $this->template->homeRoute = $this->homeRoute;

        $this->template->ENV = Core::env('CRM_ENV');
        $this->template->locale = $this->locale;
        $this->template->siteTitle = $this->applicationConfig->get('site_title');
        $this->template->siteDescription = $this->applicationConfig->get('site_description');
    }

    protected function createTemplate()
    {
        $template = parent::createTemplate();

        $this->translator->createTemplateHelpers()
            ->register($template->getLatte());

        return $template;
    }

    protected function getIp()
    {
        return \Crm\ApplicationModule\Request::getIp();
    }

    public function onlyLoggedIn()
    {
        $this->emitter->emit(new AuthenticatedAccessRequiredEvent());
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect($this->applicationConfig->get('not_logged_in_route'), ['back' => $this->storeRequest()]);
        }
    }

    protected function createComponentSimpleWidget(SimpleWidgetFactoryInterface $factory)
    {
        $control = $factory->create();
        return $control;
    }

    protected function createComponentSingleStatWidget(SingleStatWidgetFactoryInterface $factory)
    {
        $control = $factory->create();
        return $control;
    }

    protected function createComponentSnippet(SnippetFactory $snippetFactory)
    {
        $control = $snippetFactory->create();
        return $control;
    }

    public function formatLayoutTemplateFiles()
    {
        if ($this->layout) {
            return [$this->layoutManager->getLayout($this->layout)];
        }

        return parent::formatLayoutTemplateFiles();
    }
}

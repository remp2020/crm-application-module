<?php

namespace Crm\ApplicationModule\Presenters;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\ApplicationManager;
use Crm\ApplicationModule\Components\SimpleWidgetFactoryInterface;
use Crm\ApplicationModule\Components\SingleStatWidgetFactoryInterface;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Core;
use Crm\ApplicationModule\Events\AuthenticatedAccessRequiredEvent;
use Crm\ApplicationModule\Events\AuthenticationEvent;
use Crm\ApplicationModule\LayoutManager;
use Crm\ApplicationModule\Snippet\Control\SnippetFactory;
use Crm\UsersModule\Auth\UserAuthenticator;
use Crm\UsersModule\Repository\UsersRepository;
use Kdyby\Autowired\AutowireComponentFactories;
use League\Event\Emitter;
use Locale;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;

/**
 * @property-read \Nette\Bridges\ApplicationLatte\Template $template
 */
abstract class BasePresenter extends Presenter
{
    public const SESSION_RELOAD_USER = 'reloadUser';

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

    /** @var Container @inject */
    public $container;

    /** @persistent */
    public ?string $locale = null;

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
                $this->reloadSessionUser();
            } catch (AuthenticationException $e) {
                $this->getUser()->logout(true);
                if ($e->getMessage()) {
                    $this->flashMessage($e->getMessage(), 'warning');
                }
                $this->redirect('this');
            }
        }

        $this->homeRoute = ':' . $this->applicationConfig->get('home_route');
    }

    protected function beforeRender()
    {
        if ($this->locale) {
            $this->translator->setLocale($this->locale);
        }
        $this->template->locale = $this->translator->getLocale();
        $this->template->language = Locale::getPrimaryLanguage($this->translator->getLocale());

        $this->template->homeRoute = $this->homeRoute;
        $this->template->ENV = Core::env('CRM_ENV');
        $this->template->siteTitle = $this->applicationConfig->get('site_title');
        $this->template->siteDescription = $this->applicationConfig->get('site_description');
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

    public function formatLayoutTemplateFiles(): array
    {
        if ($this->layout) {
            return [$this->layoutManager->getLayout($this->layout)];
        }

        return parent::formatLayoutTemplateFiles();
    }

    /**
     * getContext returns DI container as it used in the previous versions of Nette 3.
     *
     * Nette deprecated use of this method since it's not used internally anymore, but CRM still requires it for proper
     * injection of autowired dependencies (e.g. in BaseWidget).
     */
    public function getContext(): Container
    {
        return $this->container;
    }

    private function reloadSessionUser()
    {
        // reload user in session if flagged for reload
        if (!$this->isAjax() && $this->session->getSection('auth')->get(self::SESSION_RELOAD_USER)) {
            $this->session->getSection('auth')->remove(self::SESSION_RELOAD_USER);
            $currentUser = $this->container->getByType(UsersRepository::class)?->find($this->getUser()->id);
            if ($currentUser) {
                $userIdentity = $this->container->getByType(UserAuthenticator::class)?->getIdentity($currentUser);
                if ($userIdentity) {
                    $this->user->getStorage()?->saveAuthentication($userIdentity);
                }
            }
        }
    }
}

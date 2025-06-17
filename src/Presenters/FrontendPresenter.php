<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Components\FrontendMenu\FrontendMenu;
use Crm\ApplicationModule\Events\FrontendRequestEvent;
use Crm\SubscriptionsModule\Repositories\SubscriptionsRepository;
use Crm\UsersModule\Models\Auth\AutoLogin\AutoLogin;
use Crm\UsersModule\Repositories\UsersRepository;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\SessionSection;
use Nette\Http\UrlImmutable;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

class FrontendPresenter extends BasePresenter
{
    #[Inject]
    public UsersRepository $usersRepository;

    #[Inject]
    public SubscriptionsRepository $subscriptionsRepository;

    #[Inject]
    public Response $response;

    #[Inject]
    public AutoLogin $autologin;

    #[Inject]
    public Request $request;

    #[Persistent]
    public $from;

    /** @var SessionSection */
    protected SessionSection $rtmSession;

    public function startup()
    {
        parent::startup();

        $this->buildTrackingParamsSession();
        $this->setLayout($this->getLayoutName());

        // mega krasny header P3P - security my ass
        $this->response->setHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
        $this->template->path = $this->request->getUrl()->path;
        $this->template->user = $this->getUser();
        $this->template->headerCode = $this->applicationConfig->get('header_block');
        $this->template->cmsUrl = $this->applicationConfig->get('cms_url');
        $this->template->siteUrl = $this->applicationConfig->get('site_url');
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        /** @var FrontendRequestEvent $event */
        $event = $this->emitter->emit(new FrontendRequestEvent());
        foreach ($event->getFlashMessages() as $flashMessage) {
            $this->flashMessage($flashMessage['message'], $flashMessage['type']);
        }

        // TODO: move this functionality to mail-related module (to a handler of FrontendRequestEvent)
        $mailAutologinToken = $this->params['login_t'] ?? $this->params['token'] ?? null;
        if ($mailAutologinToken && $this->getParameter('autologin') !== 'done' && !$this->getUser()->isLoggedIn()) {
            $redirect = false;
            try {
                $this->getUser()->login(['mailToken' => $mailAutologinToken]);
                // Do not refresh POST/PUT requests (otherwise data will get lost)
                if (!in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
                    $redirect = true;
                }
            } catch (AuthenticationException $exp) {
                if ($exp->getMessage()) {
                    $this->flashMessage($exp->getMessage(), 'notice');
                }
            }
            if ($redirect) {
                $params = $this->params;
                $params['autologin'] = 'done';
                $this->redirect($this->action, $params);
            }
        }
    }

    protected function getLayoutName()
    {
        $layoutName = $this->applicationConfig->get('layout_name');
        if ($layoutName) {
            return $layoutName;
        }
        return 'frontend';
    }

    public function createComponentFrontendMenu(FrontendMenu $menu)
    {
        $menu->setMenuContainer($this->applicationManager->getFrontendMenuContainer());
        return $menu;
    }

    /**
     * Returns array with RTM parameters of campaign
     *
     * @return array
     */
    public function rtmParams() : array
    {
        if (!isset($this->rtmSession)) {
            return [];
        }

        return array_filter([
            'rtm_source' => $this->rtmSession->rtmSource,
            'rtm_medium' => $this->rtmSession->rtmMedium,
            'rtm_campaign' => $this->rtmSession->rtmCampaign,
            'rtm_content' => $this->rtmSession->rtmContent,
            'rtm_variant' => $this->rtmSession->rtmVariant,
        ]);
    }

    /**
     * Store sales funnel UTM parameters
     * and additional tracking parameters to session
     */
    protected function buildTrackingParamsSession()
    {
        $this->rtmSession = $this->getSession('rtm_session');
        $this->rtmSession->setExpiration('30 minutes');

        $rtmSource = $this->getParameter('rtm_source') ?? $this->getHttpRequest()->getCookie('rtm_source');
        $rtmMedium = $this->getParameter('rtm_medium') ?? $this->getHttpRequest()->getCookie('rtm_medium');
        $rtmCampaign = $this->getParameter('rtm_campaign') ?? $this->getHttpRequest()->getCookie('rtm_campaign');
        $rtmContent = $this->getParameter('rtm_content') ?? $this->getHttpRequest()->getCookie('rtm_content');
        $rtmVariant = $this->getParameter('rtm_variant') ?? $this->getHttpRequest()->getCookie('rtm_variant');

        if ($rtmSource) {
            $this->rtmSession->rtmSource = $rtmSource;
        }
        if ($rtmMedium) {
            $this->rtmSession->rtmMedium = $rtmMedium;
        }
        if ($rtmCampaign) {
            $this->rtmSession->rtmCampaign = $rtmCampaign;
        }
        if ($rtmContent) {
            $this->rtmSession->rtmContent = $rtmContent;
        }
        if ($rtmVariant) {
            $this->rtmSession->rtmVariant = $rtmVariant;
        }
    }

    /**
     * Referer (either set as 'url' GET parameter or HTTP header)
     * Make sure it's validated before using as a redirect URL (@see RedirectValidator)
     */
    public function getReferer(): ?string
    {
        $refererUrl = null;

        try {
            if ($this->request->getQuery('referer')) {
                $refererUrl = new UrlImmutable($this->request->getQuery('referer'));
            } else {
                $refererUrl = $this->request->getReferer();
                if ($refererUrl?->getQueryParameter('referer')) {
                    $refererUrl = new UrlImmutable($refererUrl->getQueryParameter('referer'));
                }
            }
        } catch (InvalidArgumentException $e) {
            // occasionally bots send invalid (non-URL) referer; no action necessary
        }

        return $refererUrl?->__toString();
    }
}

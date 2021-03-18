<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Components\FrontendMenu;
use Crm\SubscriptionsModule\Repository\SubscriptionsRepository;
use Crm\UsersModule\Auth\AutoLogin\AutoLogin;
use Crm\UsersModule\Repository\UsersRepository;
use Kdyby\Translation\Translator;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\SessionSection;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;

class FrontendPresenter extends BasePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var SubscriptionsRepository @inject */
    public $subscriptionsRepository;

    /** @var Translator @inject */
    public $translator;

    /** @var Response @inject */
    public $response;

    /** @var AutoLogin @inject */
    public $autologin;

    /** @var Request @inject */
    public $request;

    /** @persistent */
    public $from;

    /** @var SessionSection */
    protected $rtmSession;

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
        $this->template->jsDomain = $this->getJavascriptDomain();
        $this->template->cmsUrl = $this->applicationConfig->get('cms_url');
        $this->template->siteUrl = $this->applicationConfig->get('site_url');
    }

    protected function getJavascriptDomain()
    {
        $parts = explode('.', $this->request->getUrl()->getHost());
        if (count($parts) > 2) {
            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }
        return implode('.', $parts);
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        // tento kod by bolo dobre nejak oddelit
        // a spravit nejaky mechanizmus aby jednotlive moduly vedeli pridavat tuto funkcioianlitu dynamicky
        if (!$this->getUser()->isLoggedIn() && $this->getParameter('autologin') !== 'done' &&
            (isset($this->params['login_t']) || $this->request->getCookie('n_token') || isset($this->params['token']))) {
            try {
                $mailAutologinToken = isset($this->params['login_t']) ? $this->params['login_t'] : null;
                if (!$mailAutologinToken && isset($this->params['token'])) {
                    $mailAutologinToken = $this->params['token'];
                }
                $accessToken = $this->request->getCookie('n_token') ? $this->request->getCookie('n_token') : null;
                $this->getUser()->login(['mailToken' => $mailAutologinToken, 'accessToken' => $accessToken]);

                $redirect = true;
            } catch (AuthenticationException $exp) {
                if ($exp->getMessage()) {
                    $this->flashMessage($exp->getMessage(), 'notice');
                }

                if ($this->request->getCookie('n_token')) {
                    $this->response->deleteCookie('n_token');
                }
                $redirect = false;
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
        $menu->setMenuItems($this->applicationManager->getFrontendMenuItems());
        return $menu;
    }

    /**
     * Returns array with RTM parameters of campaign
     *
     * @return array
     */
    public function rtmParams() : array
    {
        return array_filter([
            'rtm_source' => $this->rtmSession->rtmSource,
            'rtm_medium' => $this->rtmSession->rtmMedium,
            'rtm_campaign' => $this->rtmSession->rtmCampaign,
            'rtm_content' => $this->rtmSession->rtmContent,
        ]);
    }


    /**
     * Backward compatible function (if a widget requires UTM parameters)
     *
     * @deprecated will be removed in the future
     * @return array
     */
    public function utmParams(): array
    {
        return array_filter([
            'utm_source' => $this->rtmSession->rtmSource,
            'utm_medium' => $this->rtmSession->rtmMedium,
            'utm_campaign' => $this->rtmSession->rtmCampaign,
            'utm_content' => $this->rtmSession->rtmContent,
        ]);
    }

    public function trackingParams()
    {
        return $this->rtmParams();
    }

    /**
     * Store sales funnel UTM parameters
     * and additional tracking parameters to session
     */
    protected function buildTrackingParamsSession()
    {
        $this->rtmSession = $this->getSession('rtm_session');
        $this->rtmSession->setExpiration('30 minutes');

        // Transition from UTM to RTM
        $utmSession = $this->getSession('utm_session');

        $rtmSource = $this->getParameter('rtm_source') ?? $this->getParameter('utm_source');
        $rtmMedium = $this->getParameter('rtm_medium') ?? $this->getParameter('utm_medium');
        $rtmCampaign = $this->getParameter('rtm_campaign') ?? $this->getParameter('utm_campaign');
        $rtmContent = $this->getParameter('rtm_content') ?? $this->getParameter('utm_content');
        $rtmVariant = $this->getParameter('rtm_variant') ?? $this->getParameter('banner_variant');

        if ($rtmSource) {
            $this->rtmSession->rtmSource = $rtmSource;
        } elseif (isset($utmSession->utmSource)) { // Deprecated, will be removed after transition
            $this->rtmSession->rtmSource = $utmSession->utmSource;
        }
        unset($utmSession->utmSource);

        if ($rtmMedium) {
            $this->rtmSession->rtmMedium = $rtmMedium;
        } elseif (isset($utmSession->utmMedium)) { // Deprecated, will be removed after transition
            $this->rtmSession->rtmMedium = $utmSession->utmMedium;
        }
        unset($utmSession->utmMedium);

        if ($rtmCampaign) {
            $this->rtmSession->rtmCampaign = $rtmCampaign;
        } elseif (isset($utmSession->utmCampaign)) { // Deprecated, will be removed after transition
            $this->rtmSession->rtmCampaign = $utmSession->utmCampaign;
        }
        unset($utmSession->utmCampaign);

        if ($rtmContent) {
            $this->rtmSession->rtmContent = $rtmContent;
        } elseif (isset($utmSession->utmContent)) { // Deprecated, will be removed after transition
            $this->rtmSession->rtmContent = $utmSession->utmContent;
        }
        unset($utmSession->utmContent);

        if ($rtmVariant) {
            $this->rtmSession->rtmVariant = $rtmVariant;
        } elseif (isset($this->rtmSession->bannerVariant)) { // Migration from bannerVariant -> rtmVariant
            $this->rtmSession->rtmVariant = $this->rtmSession->bannerVariant;
        }
        unset($this->rtmSession->bannerVariant);
    }

    public function getReferer()
    {
        $referer = null;
        if (isset($_GET['referer'])) {
            $referer = $_GET['referer'];
        } else {
            $refererUrl = $this->request->getReferer();
            if ($refererUrl) {
                $referer = $refererUrl->__toString();
            }
        }

        $url = new Url($referer);
        $refererParam = $url->getQueryParameter('referer');
        if ($refererParam) {
            $referer = $refererParam;
        }

        return $referer;
    }
}

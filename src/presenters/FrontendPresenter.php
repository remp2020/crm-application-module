<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Components\FrontendMenuFactoryInterface;
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
    protected $utmSession;

    /** @var SessionSection */
    protected $additionalTrackingSession;

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
        if (!$this->getUser()->isLoggedIn() &&
            (isset($this->params['login_t']) || $this->request->getCookie('n_token') || isset($this->params['token']))) {
            $redirect = true;

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
                    $redirect = false;
                }
            }

            if ($redirect) {
                $params = $this->params;
                if (isset($params['login_t'])) {
                    unset($params['login_t']);
                }
                if (isset($params['token'])) {
                    unset($params['token']);
                }
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

    public function createComponentFrontendMenu(FrontendMenuFactoryInterface $factory)
    {
        $menu = $factory->create();
        $menu->setMenuItems($this->applicationManager->getFrontendMenuItems());

        return $menu;
    }

    protected function getPaymentConfig($payment)
    {
        $serviceName = $payment->payment_gateway->code . 'Config';
        if ($this->context->hasService($serviceName)) {
            return $this->context->getService($serviceName);
        }
        return null;
    }

    /**
     * Returns array with UTM parameters of campaign
     *
     * @return array
     */
    public function utmParams() : array
    {
        return array_filter([
            'utm_source' => $this->utmSession->utmSource,
            'utm_medium' => $this->utmSession->utmMedium,
            'utm_campaign' => $this->utmSession->utmCampaign,
            'utm_content' => $this->utmSession->utmContent,
        ]);
    }

    /**
     * Returns array with parameters for Tracker.
     *
     * @return array
     */
    protected function additionalTrackingParams()
    {
        return array_filter([
            'banner_variant' => $this->additionalTrackingSession->bannerVariant,
        ]);
    }

    protected function trackingParams()
    {
        return array_merge(
            $this->utmParams(),
            $this->additionalTrackingParams()
        );
    }

    /**
     * Store sales funnel UTM parameters
     * and additional tracking parameters to session
     */
    protected function buildTrackingParamsSession()
    {
        $this->utmSession = $this->getSession('utm_session');
        $this->utmSession->setExpiration('30 minutes');

        if ($this->getParameter('utm_source')) {
            $this->utmSession->utmSource = $this->getParameter('utm_source');
        }
        if ($this->getParameter('utm_medium')) {
            $this->utmSession->utmMedium = $this->getParameter('utm_medium');
        }
        if ($this->getParameter('utm_campaign')) {
            $this->utmSession->utmCampaign = $this->getParameter('utm_campaign');
        }
        if ($this->getParameter('utm_content')) {
            $this->utmSession->utmContent = $this->getParameter('utm_content');
        }

        // store additional parameters
        $this->additionalTrackingSession = $this->getSession('additional_tracking_params');
        $this->additionalTrackingSession->setExpiration('30 minutes');

        if ($this->getParameter('banner_variant')) {
            $this->additionalTrackingSession->bannerVariant = $this->getParameter('banner_variant');
        }
    }

    protected function getReferer()
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

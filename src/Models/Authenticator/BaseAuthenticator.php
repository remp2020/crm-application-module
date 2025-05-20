<?php

namespace Crm\ApplicationModule\Models\Authenticator;

use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\UsersModule\Events\LoginAttemptEvent;
use Crm\UsersModule\Events\UserSignInEvent;
use League\Event\Emitter;
use Nette\Http\Request;

abstract class BaseAuthenticator implements AuthenticatorInterface
{
    private $emitter;

    private $hermesEmitter;

    private $request;

    /** @var string */
    protected $source = UserSignInEvent::SOURCE_WEB;

    /** @var bool */
    protected $api = false;

    public function __construct(
        Emitter $emitter,
        \Tomaj\Hermes\Emitter $hermesEmitter,
        Request $request,
    ) {
        $this->emitter = $emitter;
        $this->hermesEmitter = $hermesEmitter;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function authenticate()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * Sets attributes needed globally.
     */
    public function setCredentials(array $credentials) : AuthenticatorInterface
    {
        $this->api = false;
        if (array_key_exists('source', $credentials)) {
            $this->source = $credentials['source'];

            if (preg_match('/^api*/', $credentials['source'])) {
                $this->api = true;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSource() : string
    {
        return $this->source;
    }

    public function shouldRegenerateToken(): bool
    {
        return true;
    }

    protected function addAttempt($email, $user, $source, $status, $message = null)
    {
        $date = new \DateTime();
        $this->emitter->emit(new LoginAttemptEvent(
            $email,
            $user,
            $source,
            $status,
            $message,
            $date,
        ));
        $this->hermesEmitter->emit(new HermesMessage(
            'login-attempt',
            [
                'status' => $status,
                'source' => $source,
                'date' => $date->getTimestamp(),
                'browser_id' => $this->request->getCookie('browser_id'),
                'user_id' => $user ? $user->id : null,
            ],
        ), HermesMessage::PRIORITY_DEFAULT);
    }
}

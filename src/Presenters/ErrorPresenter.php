<?php

namespace Crm\ApplicationModule\Presenters;

use Crm\ApplicationModule\Models\Snippet\Control\SnippetFactory;
use Kdyby\Autowired\AutowireComponentFactories;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;

/**
 * Error presenter.
 */
class ErrorPresenter extends Presenter
{
    use AutowireComponentFactories;

    /**
     * @throws AbortException
     */
    public function renderDefault(\Throwable $exception)
    {
        if ($exception instanceof BadRequestException) {
            $code = $exception->getHttpCode();
            // load template 403.latte or 404.latte or ... 4xx.latte
            $this->setView(in_array($code, [403, 404, 405, 410, 500], true) ? (string) $code : '4xx');
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::EXCEPTION); // and log exception
        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->payload->error = true;
            $this->sendPayload();
        }
    }

    protected function createComponentSnippet(SnippetFactory $snippetFactory)
    {
        $control = $snippetFactory->create();
        return $control;
    }
}

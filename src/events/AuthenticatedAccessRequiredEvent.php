<?php

namespace Crm\ApplicationModule\Events;

use League\Event\AbstractEvent;

/**
 * AuthenticatedAccessRequiredEvent is meant to be triggered when accessed CRM resource should be available only
 * for authenticated users.
 *
 * Handlers can verify if user is authenticated, login user if necessary, or redirect user away in case they need to
 * control what's being displayed to the client.
 */
class AuthenticatedAccessRequiredEvent extends AbstractEvent
{
}

# Migration guide to CRM 1.0

---

## Table of Contents

- [Minimal requirements](#minimal-requirements)
- [Nette 3.0](#nette-30)
- [Nette 3.1](#nette-31)
- [Nette API](#nette-api)

## Minimal requirements

- PHP 7.4

- MySQL 5.7
  
  - _Note: We are using Percona 8.0 in production._

- Nette 3.1
  
  - Packages dependant on Nette were updated too (e.g. `latte/latte`, `kdyby/translation`, `contributte/forms-multiplier`). Check `composer.json` for current minimal versions.

---

## Nette 3.0

### Official guide  - Migrating to Version 3.0

Check the official [Nette guide for migrating to version 3.0](https://doc.nette.org/en/3.0/migration-3-0). The following steps are changes we had to apply in our CRM extensions.

---

### rector/rector

Package `rector/rector` is the tool that helped us with the upgrade of all our CRM extensions. Check GitHub for more details.

This is the config we used for the upgrade itself.

```php
<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Nette\Set\NetteSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor
    $parameters->set(Option::PATHS, [
        // set this to path to your extension;
        // no need to check CRM extensions
        __DIR__ . '/extensions',

        // updates nette packages automatically;
        // check result against crm-application-module/composer.json
        __DIR__ . '/composer.json',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // apply upgrade rules for Nette 3.0
    $containerConfigurator->import(NetteSetList::NETTE_30);
};
```

---

### Update neon configs

- Replace keyword `class` with `factory` when setting up factory service.
  
  ```diff
  gatewayFactory:
  -    class: Crm\PaymentsModule\GatewayFactory
  +    factory: Crm\PaymentsModule\GatewayFactory
          setup:
              - registerGateway(paypal, Crm\PaymentsModule\Gateways\Paypal)
  ```

- Replace use of `on`/`off` values with `yes`/`no` or `true`/`false`.
  
  ```diff
  translation:
      resolvers:
  -        session: on
  -        header: off
  +        session: yes
  +        header: no
  ```

- Replace keyword `_extends` with `_prevent_merging` when overriding setup of service.
  
  ```diff
  # override setup of HermesDriver for tests
  hermesDriver:
          factory: Crm\ApplicationModule\Hermes\DummyDriver
          setup:
  -            _extends: true
  +            _prevent_merging: true
  ```

---

### Update your extension's registration to DI

Static `Nette\DI\Compiler::loadDefinitions()` is deprecated in favor of non-static method `Compiler::loadDefinitionsFromConfig()` (compiler is accessible through `$this->compiler`).

Example of changes in our `PaymentsModuleExtension`:

```diff
namespace Crm\PaymentsModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\CompilerExtension;

final class PaymentsModuleExtension extends CompilerExtension implements ITranslationProvider
{
    private $defaults = [];

    public function loadConfiguration()
    {
-        $builder = $this->getContainerBuilder();

        // set default values if user didn't define them
        $this->config = $this->validateConfig($this->defaults);

        // load services from config and register them to Nette\DI Container
-        Compiler::loadDefinitions(
-            $builder,
+        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );
    }
```

---

### Nette's interface changes

- Interface `Nette\Mail\IMailer::send()` method now returns `void`. No changes are required if you use our [REMP Mailer](https://github.com/remp2020/remp/tree/master/Mailer).

- Inteface `Nette\Application\IResponse::send()` method now returns `void`. No changes required if you use our responses (eg. `JsonResponse`, `RedirectResponse`, `XmlResponse`).

- Interface `Nette\Routing\IRouter` was renamed to `Nette\Routing\Router`. Return types were added. No changes are required if you don't implement your own router.

- Multiple `Nette\Database\Table` classes and interfaces now have return types. We had to fix classes that extend or implement them. Eg. `Crm\ApplicationModule\ActiveRow`, `Crm\ApplicationModule\DataRow`, `Crm\ApplicationModule\Selection`).

- Misc renames & return types (Nette interfaces changed) // TODO

---

### Remove constructor calls from your components

Quote from Nette migration guide:

> Constructor of `Nette\ComponentModel\Component` has not been used for years and was removed in version 3.0. It's a BC break. If you call parent constructor in your component or presenter inheriting from `Nette\Application\UI\Presenter`, you must remove it.

All CRM components were fixed. Eg. `Crm\AdminModule\Components\AdminMenu`:

```diff
class AdminMenu extends UI\Control

    public function __construct(User $user)
    {
-        parent::__construct();
        $this->user = $user;
    }
```

---

### Fix template links, provide ID instead of object

If you used an object with `{plink}` or `{link}` in Latte templates, switch to the object's ID. Latte link helpers now expect string.

```diff
- <li><a href="{link :Users:UsersAdmin:Show $current_user}"> //...
+ <li><a href="{link :Users:UsersAdmin:Show, $current_user->id}"> //..
```

---

### Methods of `Nette\Security\Passwords` are no longer static

> The `Nette\Security\Passwords` class is now used as an object, ie the methods are no longer static.

If you use methods from `Nette\Security\Passwords`, you need to inject (or require from DI) service `Nette\Security\Passwords` and use it as the object. Eg.

```diff
class DemoClass
{
+    /** @var Nette\Security\Passwords */
+    private $passwords;
+
+    public function __construct(Nette\Security\Passwords $passwords)
+    {
+        $this->passwords = $passwords;
+        parent::__construct();
+    }

    public function demoMethod($password)
    {
-        Nette\Security\Passwords::hash($password);
+        $this->passwords->hash($password);
    }
}

```

---

### Deprecation of `$whenBrowserIsClosed` in `Nette\Security\User::setExpiration()`

Second parameter `$whenBrowserIsClosed` of `Nette\Security\User::setExpiration()` was deprecated. Set option `Nette\Security\IUserStorage::CLEAR_IDENTITY` if you want to clear the identity after session expiration (see what it means in [Nette docs](https://doc.nette.org/en/3.0/access-control#toc-identity)).

```diff
public function formSucceeded($form, $values)
{
    if ($values->remember) {
-        $this->user->setExpiration('14 days', false);
+        $this->user->setExpiration('14 days');
    } else {
-        $this->user->setExpiration('20 minutes', true);
+        $this->user->setExpiration(
+            '20 minutes',
+            Nette\Security\IUserStorageIUserStorage::CLEAR_IDENTITY
+        );
    }
```

---

### Deprecation of `Nette\Forms\Controls\BaseControl::setAttribute()`

Use `Nette\Forms\Controls\BaseControl::setHtmlAttribute()` instead.

---

### Update `netteForms.js`

Copy `netteForms.js` into `www/layout` folders:

- `www/layouts/admin/js/`

- `www/layouts/default/js/`

Download `netteForms.js` from link: https://nette.github.io/resources/js/3/netteForms.js

---

### Notes

- Class `Crm\ApiModule\Api\JsonResponse` doesn't extend ``Nette\Application\Responses\JsonResponse`` anymore. The parent method was changed to `final`. We copied methods from it into our response (to fulfill interface). No changes are required.

- Extension `contributte/forms-multiplier` was updated. They renamed namespace `WebChemistry` to `Contributte`. If you use forms multiplier (`$form->addMultiplier()`), you have to fix imports.


## Nette 3.1

### Official guide  - Migrating to Version 3.1

Check the official [Nette guide for migrating to version 3.1](https://doc.nette.org/en/3.1/migration-3-1). The following steps are changes we had to apply in our CRM extensions.

---

### rector/rector

Package `rector/rector` is the tool that helped us with the upgrade of all our CRM extensions. Check GitHub for more details.

This is the config we used for the upgrade to Nette 3.1.

```php
<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Nette\Set\NetteSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor
    $parameters->set(Option::PATHS, [
        // set this to path to your extension;
        // no need to check CRM extensions
        __DIR__ . '/extensions',

        // updates nette packages automatically;
        // check result against crm-application-module/composer.json
        __DIR__ . '/composer.json',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // apply upgrade rules for Nette 3.1
    $containerConfigurator->import(NetteSetList::NETTE_31);
};
```

---

### `Nette\Configurator` was moved

`Nette\Configurator` was moved to `Nette\Bootstrap\Configurator`. It is used in `ApplicationModule`'s `Core.php` to configure and create Nette's DI container. If you have own `Core.php` alternative / implementation, you'll need to fix`Configurator` import.

### `Nette\Database` deprecations & cleaning

- `Nette\Database\IRow` is deprecated; use `Nette\Database\Row`.
- `Nette\Database\Table\IRow` is deprecated; use `Nette\Database\Table\ActiveRow`.

We switched everywhere to `Nette\Database\Table\ActiveRow`. Using `Nette\Database\Row` is not satisfactory for our needs. And also `Nette\Database` changed few arguments to typed. So using IRow/Row is not possible in some cases. You'll have to switch to `ActiveRow` too _(in some cases; and you should in the rest places)_.

#### `DataRow` removed; using new `ActiveRowFactory`

`Crm\ApplicationModule\DataRow` was used as dummy wrapper for sending emails to email addresses without user entry. `NotificationEvent` now requires `ActiveRow` so `DataRow` had to be replaced. In case you need to send `NotificationEvent` to email without user, you can now use `Crm\ApplicationModule\ActiveRowFactory`.

```php
class ExampleClass
{
    /** @var \League\Event\Emitter @inject */
    public $emitter;

    /** @var \Crm\ApplicationModule\ActiveRowFactory @inject */
    public $activeRowFactory;

    public function sendNotificationToExample()
    {
        $userRow = $this->activeRowFactory->create([
            'email' => 'example@example.com',
        ]);

        $this->emitter->emit(new NotificationEvent($this->emitter, $userRow, 'example_template'));
    }
}
```

### `Presenter->getContext()` was deprecated in Nette

Getting container directly from Presenters (all extending `Nette\Application\UI\Presenter`) is now deprecated in Nette 3 in favor of using DI. Since some parts of the CRM still require the DI container to be available in presenter, we've overriden `BasePresenter::getContext()` and made `BasePresenter::$container` available.

If your presenters extend `Crm\ApplicationModule\Presenters\BasePresenter`, no change is necessary. If your presenters don't extend it and need DI container, you'll have to inject it manually.

Notes:

- Consider using proper DI instead of loading service manually from container.
- Not extending `Crm\ApplicationModule\Presenters\BasePresenter` or not injecting DI container to your presenters might cause deprecation notices in widgets rendering other widgets.

### Nette's interfaces renamed

Multiple Nette interfaces lost **I** prefix. Follow migration guide mentioned above. Here are interfaces we had to fix in extensions:

- `Nette\Application\IResponse` is deprecated; use `Nette\Application\Response`.
- `Nette\Application\UI\ITemplate` is deprecated; use `Nette\Application\UI\Template`.
- `Nette\Caching\IStorage` is deprecated; use `Nette\Caching\Storage`.
- `Nette\Mail\IMailer` is deprecated; use `Nette\Mail\Mailer`.
- `Nette\Security\IAuthorizator` is deprecated; use `Nette\Security\Authorizator`.
- `Nette\Security\Identity` is deprecated; use `Nette\Security\SimpleIdentity`.
- `Nette\Localization\ITranslator` is deprecated; use `Nette\Localization\Translator`.

### Latte changes

- Changed deprecated `{ifCurrent 'link'}` latte tag to `{isLinkCurrent('link')}` latte function. _(To be consistent, we changed also `{$presenter->isLinkCurrent('link')}`to new `{isLinkCurrent('link')}`)_.
- Fixed deprecated use of vars without dollar sign. `{var myVariable = ...}` changed to `{var $myVariable = ...}`.

### Notes

- We switched from `$form->values['field_name]` to `$form->getValues()['field_name]` _(recommended by rector)_.
- Check validation methods of forms _(when used with `$form->onValidate[]`)_. It's possible that `$form->values` (or `$form->getValues()`) won't return all values for validation. Use `$form->getUnsafeValues()` instead.

## Nette API

Previously CRM used a custom implementation of API library, which later evolved to https://github.com/tomaj/nette-api/. The library has come a long way since then and we want to bring all of the nice features to the CRM.

Unfortunately, there's no nice upgrade guide, nor rector rules at the moment. Most of the changes are backwards compatible, but you'll still need to change your API handlers.

However, the backwards compatible changes point to the deprecated parts of the API and it's recommended to check the docs of `tomaj/nette-api` and utilize the newer features directly. We'll give a notice before removing deprecated classes.

### Update API handlers

- Changed signature of `handle(ApiAuthorizationInterface $authorization)`  to `handle(array $params): ApiResponseInterface`. There are couple of side effects of this:
  
  - You don't need to ask `ParamsProcessor` to retrieve the params since they're passed to the `handle()` method directly:
    ```php
    $paramsProcessor = new ParamsProcessor($this->params());
    $params = $paramsProcessor->getValues();
    ```
    
  - The params are already validated by `Crm\ApiModule\Presenters\ApiPresenter`. You can disable the validation on per-handler basis with `protected boolean $enableValidation = false`. This is handy if you need to manually control error response for invalid params. It's highly recommended to get rid of custom params validation blocks in your APIs.
    
  - The authorization is not present by default anymore. If you work with the `ApiAuthorizationInterface $authorization` in your handler, you can retrieve it with:
    ```php
    $authorization = $this->getAuthorization();
    ```

- Changed signature of `Crm\ApiModule\Params\ParamsProcessor::isError()`.

  - As stated in previous part, params are already validated by the base API handler. If you keep the validation snippets in your handlers, it's no longer possible to use `Crm\ApiModule\Params\ParamsProcessor::isError()` to both 1) check if the params contain error, 2) and retrieve the error within the same call. If you still need to run the validation yourself, change this block:

    ```php
    $error = $paramsProcessor->isError();
    if ($error) {
        $response = new JsonResponse(['status' => 'error', 'message' => $error]);
        $response->setHttpCode(Response::S400_BAD_REQUEST);
        return $response;
    }
    ```

    The minimal change would be to use `hasError()` instead of `isError()`. However, it's recommended to use library-provided methods `isError()` and `getErrors()` instead:
    
    ```php
    if ($paramsProcessor->isError()) {
        $response = new JsonResponse([
            'status' => 'error',
            'code' => 'invalid_request',
            'errors' => $paramsProcessor->getErrors(),
        ]);
    }
    ```

- Changed signature of `params()` to `params(): array`.

### Update API authorizations

If you implement your own API authorization (`Crm\ApiModule\Authorization\ApiAuthorizationInterface`), follow the changes:

- Change signature of `authorized($resource = Authorizator::ALL)` method to `authorized($resource = Authorizator::ALL): bool`.
- Change signature of `getErrorMessage()` method to `getErrorMessage(): ?string`.

### Update API response implementations

If you implement your own API response wrapper (`Crm\ApiModule\Response\ApiResponseInterface`), follow the changes:

- Change signature of `private $httpCode` property to `private int $code`.

### Deprecations

##### API handlers

- Definition of param through `InputParam` is deprecated in favor of specific params defined in `Tomaj\NetteApi\Params` namespace.
- 

##### API response implementations

- `Crm\ApiModule\Response\ApiResponseInterface::setHttpCode()` is deprecated in favor of `Crm\ApiModule\Response\ApiResponseInterface::setCode()`.
- `Crm\ApiModule\Response\ApiResponseInterface::getHttpCode()` is deprecated in favor of `Crm\ApiModule\Response\ApiResponseInterface::getCode()`.
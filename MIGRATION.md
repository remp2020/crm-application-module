# Migration guide to CRM 1.0

---

## Minimal requirements

- PHP 7.4

- MySQL 5.7
  
  - _Note: We are using Percona 8.0 in production._

- Nette 3.0
  
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

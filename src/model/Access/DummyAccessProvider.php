<?php

namespace Crm\ApplicationModule\Access;

class DummyAccessProvider implements ProviderInterface
{
    private $rules;

    /**
     * @param array $rules
     *
     * Array with configuration which "access" type should be allowed and which should not.
     * Rules can be configured statically or via callables:
     *
     *  [
     *      'web' => true,
     *      'foo' => function ($userId) {
     *          if ($userId == 1) return true;
     *          return false;
     *      }
     *  ]
     */
    public function configure(array $rules)
    {
        $this->rules = $rules;
    }

    public function hasAccess($userId, $access)
    {
        if (isset($this->rules[$access])) {
            if (is_callable($this->rules[$access])) {
                return $this->rules[$access]($userId);
            }
            return $this->rules[$access];
        }
        return true;
    }

    public function available($access)
    {
        return true;
    }
}

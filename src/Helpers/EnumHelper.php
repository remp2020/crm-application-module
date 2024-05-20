<?php

namespace Crm\ApplicationModule\Helpers;

/**
 * @method static array cases() IDE helper because there can't be defined self::cases() as an abstract static method.
 */
trait EnumHelper
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * List of enum values as 'enum value'=>'friendly name'. Initially intended for use in a Nette forms.
     * Without overriding the method returns the 'friendly name' the same as the 'enum value' or
     * in case of non-backed enum it returns a pair of 'enum keys'.
     */
    public static function getFriendlyList(): array
    {
        $values = self::values();

        $isNonBackedEnum = count($values) === 0;
        if ($isNonBackedEnum) {
            $names = self::names();
            return array_combine($names, $names);
        }

        return array_combine($values, $values);
    }
}

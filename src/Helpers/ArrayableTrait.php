<?php

namespace Crm\ApplicationModule\Helpers;

trait ArrayableTrait
{
    /**
     * Serialize all *accessible* (as defined in get_object_vars() function) object properties.
     * Key is be property name in snake_case.
     * Value is either primitive value or in case of object implementing Arrayable interface, result of toArray() method.
     * Other objects and arrays are ignored.
     */
    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            if (is_scalar($value)) {
                $result[self::toSnakeCase($key)] = $value;
            } elseif ($value instanceof Arrayable) {
                $result[self::toSnakeCase($key)] = $value->toArray();
            }
        }
        return $result;
    }

    public static function toSnakeCase(string $key): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $key));
    }
}

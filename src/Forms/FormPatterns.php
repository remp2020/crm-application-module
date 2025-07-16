<?php

namespace Crm\ApplicationModule\Forms;

class FormPatterns
{
    public const STREET_NAME = '(?=[\p{L}0-9])[\p{L}0-9\s\-\.\p{L}]*';
    public const STREET_NUMBER = '(?=[0-9])[0-9A-z\-\/]*';
    public const ZIP_CODE = '(?=[0-9A-z])[0-9A-z\-\s]*';
    public const PHONE_NUMBER = '(?=[0-9])[0-9\+\s]*';
    public const PHONE_NUMBER_INTERNATIONAL = '^[+][-\s\.\/\d]{12,}$';
}

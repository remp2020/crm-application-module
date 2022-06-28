<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Helpers\LocalizedDateHelper;
use DateTime;
use Nette\Localization\Translator;

class LocalizedDateHelperTest extends CrmTestCase
{
    private LocalizedDateHelper $localizedDateHelper;
    private Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localizedDateHelper = $this->inject(LocalizedDateHelper::class);
        $this->translator = $this->inject(Translator::class);
    }

    public function testShortFormats()
    {
        $date = new DateTime('2022-06-28 10:52:15');
        $locale = 'en_US';
        $localeFormat = 'dd/MM/YYYY';
        $this->translator->setLocale($locale);

        // no locale format set, use default
        $this->assertEquals('6/28/22, 10:52:15 AM', $this->localizedDateHelper->process($date));

        // locale format set
        $this->localizedDateHelper->setShortFormat($localeFormat, $locale);
        $this->assertEquals('28/06/2022', $this->localizedDateHelper->process($date));
    }

    public function testLongFormats()
    {
        $date = new DateTime('2022-06-28 10:52:15');
        $locale = 'en_US';
        $localeFormat = 'dd/MM/YYYY';
        $this->translator->setLocale($locale);

        // no locale format set, use default
        $this->assertEquals('June 28, 2022 at 10:52:15 AM', $this->localizedDateHelper->process($date, true));

        // locale format set
        $this->localizedDateHelper->setLongFormat($localeFormat, $locale);
        $this->assertEquals('28/06/2022', $this->localizedDateHelper->process($date, true));
    }
}

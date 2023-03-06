<?php

namespace Crm\ApplicationModule\Tests;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\Helpers\LocalizedDateHelper;
use DateTime;

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

        // We need to match against one of the two possible values, because of the change in the ICU system library 72.1.
        // Since it's not possible to determine current version, and there might or might not be a non-breaking space
        // between the time and AM/PM, we just test this against one of the two values.
        //
        // Official php-fpm docker image uses ICU v67.1, but the php-apline uses 72.1 and this behaved unexpectedly.
        // This should be save to remove in 2024.
        //
        // https://github.com/php/php-src/issues/10262
        $this->assertContains(
            $this->localizedDateHelper->process($date),
            [
                '6/28/22, 10:52:15 AM',
                '6/28/22, 10:52:15 AM'
            ]
        );

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

        $this->assertContains(
            $this->localizedDateHelper->process($date, true),
            [
                'June 28, 2022 at 10:52:15 AM',
                'June 28, 2022 at 10:52:15 AM'
            ]
        );

        // locale format set
        $this->localizedDateHelper->setLongFormat($localeFormat, $locale);
        $this->assertEquals('28/06/2022', $this->localizedDateHelper->process($date, true));
    }
}

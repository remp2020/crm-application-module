<?php

namespace Crm\ApplicationModule\Models\Repository;

use Crm\ApplicationModule\Models\Traits\SlugColumnException;
use Nette\Utils\Strings;

trait SlugColumnTrait
{
    /**
     * @var string[]
     *
     * Defines the repository columns which should be asserted as slugs.
     */
    protected $slugs = [];

    /**
     * @throws SlugColumnException Thrown if provided string is not URL friendly.
     */
    public function assertSlugs(array $data)
    {
        foreach ($this->slugs as $slug) {
            if (!isset($data[$slug])) {
                continue;
            }
            $webalized = Strings::webalize($data[$slug], '_');
            if ($webalized !== $data[$slug]) {
                throw new SlugColumnException(
                    "Provided string '{$data[$slug]}' is not URL friendly. Try to use '{$webalized}'."
                );
            }
        }
    }
}

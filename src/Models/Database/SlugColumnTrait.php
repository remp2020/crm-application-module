<?php

namespace Crm\ApplicationModule\Models\Database;

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
            $webalized = $this->webalize($data[$slug]);
            if ($webalized !== $data[$slug]) {
                throw new SlugColumnException(
                    "Provided string '{$data[$slug]}' is not URL friendly. Try to use '{$webalized}'."
                );
            }
        }
    }

    /**
     * Modifies the UTF-8 string to the form used in the URL, ie removes diacritics and replaces all characters
     * except letters of the English alphabet and numbers with a hyphens.
     *
     * Allowed characters: _
     */
    public function webalize(string $string): string
    {
        return Strings::webalize($string, '_');
    }
}

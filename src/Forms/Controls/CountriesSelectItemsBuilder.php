<?php

namespace Crm\ApplicationModule\Forms\Controls;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\UsersModule\Repositories\CountriesRepository;
use Locale;
use Nette\Database\Table\ActiveRow;

class CountriesSelectItemsBuilder
{
    public function __construct(
        private readonly Translator $translator,
        private readonly CountriesRepository $countriesRepository,
        private readonly ApplicationConfig $applicationConfig
    ) {
    }

    public function getAllPairs(): array
    {
        $countries = [];
        foreach ($this->countriesRepository->all() as $country) {
            $countries[$country->sorting ?: 0][$country->id] = $this->translate($country);
        }

        return $this->sort($countries);
    }

    public function getAllIsoPairs(): array
    {
        $countries = [];
        foreach ($this->countriesRepository->all() as $country) {
            $countries[$country->sorting ?: 0][$country->iso_code] = $this->translate($country);
        }

        return $this->sort($countries);
    }

    public function getDefaultCountryPair(): array
    {
        $defaultCountry = $this->countriesRepository->defaultCountry();
        return [
            $defaultCountry->id => $this->translate($defaultCountry),
        ];
    }

    public function getDefaultCountryIsoPair(): array
    {
        $defaultCountry = $this->countriesRepository->defaultCountry();
        return [
            $defaultCountry->iso_code => $this->translate($defaultCountry),
        ];
    }

    private function translate(ActiveRow $country): string
    {
        $localizedCountries = $this->applicationConfig->get('localized_countries');
        if (!$localizedCountries) {
            return $country->name;
        }

        return Locale::getDisplayRegion(
            $country->iso_code . '_' . $country->iso_code, // fake locale with correct country
            $this->translator->getLocale(),
        );
    }

    private function sort(array $countries): array
    {
        $localizedCountries = $this->applicationConfig->get('localized_countries');

        krsort($countries, SORT_NUMERIC);
        $result = [];

        if ($localizedCountries) {
            foreach ($countries as $sorting => $group) {
                $collator = collator_create($this->translator->getLocale());
                uasort($group, function ($a, $b) use ($collator) {
                    return collator_get_sort_key($collator, $a) <=> collator_get_sort_key($collator, $b);
                });
                $result += $group;
            }
        } else {
            foreach ($countries as $sorting => $group) {
                $result += $group;
            }
        }

        return $result;
    }
}

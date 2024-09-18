<?php

namespace Crm\ApplicationModule\Models\DataProvider;

class DataProviderManager
{
    private array $providers = [];

    public function registerDataProvider($path, DataProviderInterface $provider, $priority = 100): void
    {
        if (isset($this->providers[$path][$priority])) {
            do {
                $priority++;
            } while (isset($this->providers[$path][$priority]));
        }
        $this->providers[$path][$priority] = $provider;
    }

    /**
     * @param string $path
     * @param string|null $validateInterface name of the interface the providers should implement. DataProviderException will be thrown if not
     * @return DataProviderInterface[]
     * @throws DataProviderException
     */
    public function getProviders(string $path, ?string $validateInterface = null): array
    {
        if (isset($this->providers[$path])) {
            $result = $this->providers[$path];
            ksort($result);

            if (!is_null($validateInterface)) {
                foreach ($this->providers[$path] as $provider) {
                    if (!($provider instanceof $validateInterface)) {
                        throw new DataProviderException("dataprovider doesn't implement {$validateInterface}: " . get_class($provider));
                    }
                }
            }
            return $result;
        }
        return [];
    }
}

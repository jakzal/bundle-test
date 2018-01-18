<?php

namespace Zalas\BundleTest\HttpKernel;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class KernelConfiguration
{
    const DEFAULT_ENVIRONMENT = 'test';

    /**
     * @var string
     */
    protected $environment = self::DEFAULT_ENVIRONMENT;

    /**
     * @var bool
     */
    protected $debug = true;

    /**
     * @var BundleInterface[]
     */
    protected $bundles = [];

    /**
     * @var array
     */
    protected $bundleConfigurations = [];

    public function withEnvironment(string $environment): self
    {
        $config = clone $this;
        $config->environment = $environment;

        return $config;
    }

    public function withDebug(bool $debug): self
    {
        $config = clone $this;
        $config->debug = $debug;

        return $config;
    }

    public function withBundle(BundleInterface $bundle): self
    {
        $config = clone $this;
        $config->bundles[] = $bundle;

        return $config;
    }

    /**
     * @param BundleInterface[] $bundles
     */
    public function withBundles(array $bundles): self
    {
        return array_reduce($bundles, function (self $config, BundleInterface $bundle) {
            return $config->withBundle($bundle);
        }, $this);
    }

    public function withBundleConfiguration(string $extensionName, array $configuration): self
    {
        $config = clone $this;
        $config->bundleConfigurations[$extensionName] = array_merge_recursive(
            $config->bundleConfigurations[$extensionName] ?? [],
            $configuration
        );

        return $config;
    }

    public function getHash()
    {
        return sha1(serialize([
            $this->environment,
            $this->debug,
            array_map(function (BundleInterface $bundle) {
                return get_class($bundle);
            }, $this->bundles),
            $this->bundleConfigurations
        ]));
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @return BundleInterface[]
     */
    public function getBundles(): array
    {
        return $this->bundles;
    }

    public function getAllBundleConfigurations(): array
    {
        return $this->bundleConfigurations;
    }
}
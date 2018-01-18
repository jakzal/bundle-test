<?php

namespace Zalas\BundleTest\HttpKernel;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class KernelConfiguration
{
    const DEFAULT_ENVIRONMENT = 'test';
    const DEFAULT_NAMESPACE = 'tests';

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

    /**
     * @var string|null
     */
    protected $tempDir;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string[]
     */
    private $publicServiceIds = [];

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->namespace = $namespace;
    }

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

    public function withTempDir(string $tempDir): self
    {
        $config = clone $this;
        $config->tempDir = $tempDir;

        return $config;
    }

    public function withPublicServiceId(string $serviceId): self
    {
        $config = clone $this;
        $config->publicServiceIds[] = $serviceId;

        return $config;
    }

    /**
     * @param string[] $serviceIds
     */
    public function withPublicServiceIds(array $serviceIds): self
    {
        return array_reduce($serviceIds, function (self $config, string $serviceId) {
            return $config->withPublicServiceId($serviceId);
        }, $this);
    }

    /**
     * Computes an unique identifier of the current configuration.
     *
     * Cache will be scoped (so also refreshed) based on this value.
     */
    public function getHash(): string
    {
        return sha1(serialize([
            $this->getEnvironment(),
            $this->isDebug(),
            array_map(function (BundleInterface $bundle) {
                return get_class($bundle);
            }, $this->getBundles()),
            $this->getAllBundleConfigurations(),
            $this->tempDir,
            $this->namespace,
            $this->getPublicServiceIds(),
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

    public function getTempDir(): string
    {
        return sprintf('%s/%s/%s', $this->tempDir ?? sys_get_temp_dir(), $this->namespace, $this->getHash());
    }

    final public function getCacheDir(): string
    {
        return sprintf('%s/var/cache/%s', $this->getTempDir(), $this->environment);
    }

    final public function getLogDir(): string
    {
        return sprintf('%s/var/log', $this->getTempDir());
    }

    /**
     * @return string[]
     */
    public function getPublicServiceIds(): array
    {
        return $this->publicServiceIds;
    }
}
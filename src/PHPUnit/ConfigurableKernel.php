<?php
declare(strict_types=1);

namespace Zalas\BundleTest\PHPUnit;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;

/**
 * Builds on top of `TestKernel` trait to provide more convenient ways of setting up the kernel with given* methods.
 */
trait ConfigurableKernel
{
    use TestKernel {
        getKernelClass as private getDefaultKernelClass;
    }

    /**
     * @var KernelConfiguration|null
     */
    protected static $kernelConfiguration;

    /**
     * @var string
     */
    protected static $kernelClass;

    /**
     * @before
     */
    protected function initializeKernelConfiguration(): void
    {
        static::$kernelConfiguration = new KernelConfiguration($this->getTestNamespace());
    }

    /**
     * Scopes the current test case in the working directory (temp directory).
     */
    protected function getTestNamespace(): string
    {
        return str_replace('\\', '', __CLASS__);
    }

    protected function givenEnvironment(string $environment): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withEnvironment($environment);

        return $this;
    }

    protected function givenDebugIsEnabled(): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withDebug(true);

        return $this;
    }

    protected function givenDebugIsDisabled(): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withDebug(false);

        return $this;
    }

    protected function givenKernel(string $kernelClass): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelClass = $kernelClass;

        return $this;
    }

    protected function givenBundleConfiguration($extensionName, array $configuration): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withBundleConfiguration($extensionName, $configuration);

        return $this;
    }

    protected function givenPublicServiceId(string $serviceId): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withPublicServiceId($serviceId);

        return $this;
    }

    protected function givenPublicServiceIds(array $serviceIds): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withPublicServiceIds($serviceIds);

        return $this;
    }

    /**
     * @param BundleInterface[] $bundles
     */
    protected function givenBundlesAreEnabled(array $bundles): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withBundles($bundles);

        return $this;
    }

    protected function givenBundleIsEnabled(BundleInterface $bundle): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withBundle($bundle);

        return $this;
    }

    protected function givenTempDir(string $tempDir): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelConfiguration = static::$kernelConfiguration->withTempDir($tempDir);

        return $this;
    }

    protected static function createKernelConfiguration(array $options): KernelConfiguration
    {
        $environment = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? static::$kernelConfiguration->getEnvironment();
        $isDebug = (bool)($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? static::$kernelConfiguration->isDebug());

        return static::$kernelConfiguration = static::$kernelConfiguration
            ->withEnvironment($environment)
            ->withDebug($isDebug);
    }

    protected static function getKernelClass(): string
    {
        return static::$kernelClass ?? static::getDefaultKernelClass();
    }

    private function ensureKernelNotBooted(): void
    {
        if (null !== static::$kernel) {
            throw new \LogicException('Configuration cannot be changed once kernel is booted.');
        }
    }
}

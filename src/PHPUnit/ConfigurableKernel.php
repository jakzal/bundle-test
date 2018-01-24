<?php
declare(strict_types=1);

namespace Zalas\BundleTest\PHPUnit;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Zalas\BundleTest\HttpKernel\KernelBuilder;

/**
 * Builds on top of `TestKernel` trait to provide more convenient ways of setting up the kernel with given* methods.
 */
trait ConfigurableKernel
{
    use TestKernel;

    /**
     * @var KernelBuilder
     */
    protected static $kernelBuilder;

    /**
     * @before
     */
    protected function initializeKernelBuilder(): void
    {
        static::$kernelBuilder = new KernelBuilder($this->getTestNamespace());
        static::$kernelBuilder->withKernelClass(static::getKernelClass());

        if (null !== $environment = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null) {
            static::$kernelBuilder->withEnvironment((string) $environment);
        }

        if (null !== $debug = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? null) {
            static::$kernelBuilder->withDebug((bool) $debug);
        }
    }

    protected static function createKernelBuilder(array $options): KernelBuilder
    {
        if (null !== $environment = $options['environment'] ?? null) {
            static::$kernelBuilder->withEnvironment((string) $environment);
        }

        if (null !== $isDebug = $options['debug'] ?? null) {
            static::$kernelBuilder->withDebug((bool) $isDebug);
        }

        if (null !== $kernelClass = $options['kernel_class'] ?? null) {
            static::$kernelBuilder->withKernelClass((string) $kernelClass);
        }

        return static::$kernelBuilder;
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

        static::$kernelBuilder->withEnvironment($environment);

        return $this;
    }

    protected function givenDebugIsEnabled(): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withDebug(true);

        return $this;
    }

    protected function givenDebugIsDisabled(): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withDebug(false);

        return $this;
    }

    protected function givenKernel(string $kernelClass): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withKernelClass($kernelClass);

        return $this;
    }

    protected function givenBundleConfiguration($extensionName, array $configuration): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withBundleConfiguration($extensionName, $configuration);

        return $this;
    }

    protected function givenPublicServiceId(string $serviceId): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withPublicService($serviceId);

        return $this;
    }

    protected function givenPublicServiceIds(array $serviceIds): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withPublicServices($serviceIds);

        return $this;
    }

    /**
     * @param BundleInterface[] $bundles
     */
    protected function givenBundlesAreEnabled(array $bundles): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withBundles($bundles);

        return $this;
    }

    protected function givenBundleIsEnabled(BundleInterface $bundle): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withBundle($bundle);

        return $this;
    }

    protected function givenTempDir(string $tempDir): self
    {
        $this->ensureKernelNotBooted();

        static::$kernelBuilder->withTempDir($tempDir);

        return $this;
    }

    private function ensureKernelNotBooted(): void
    {
        if (null !== static::$kernel) {
            throw new \LogicException('Configuration cannot be changed once kernel is booted.');
        }
    }
}

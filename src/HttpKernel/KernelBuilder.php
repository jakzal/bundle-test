<?php
declare(strict_types=1);

namespace Zalas\BundleTest\HttpKernel;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zalas\BundleTest\HttpKernel\Exception\ClassNotFoundException;
use Zalas\BundleTest\HttpKernel\Exception\KernelNotSupportedException;

final class KernelBuilder
{
    /**
     * @var string
     */
    private $kernelClass = TestKernel::class;

    /**
     * @var KernelConfiguration
     */
    private $configuration;

    public function __construct(string $namespace = KernelConfiguration::DEFAULT_NAMESPACE)
    {
        $this->configuration = new KernelConfiguration($namespace);
    }

    public function createKernel(): KernelInterface
    {
        return new $this->kernelClass($this->configuration);
    }

    public function bootKernel(): KernelInterface
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        return $kernel;
    }

    public function withEnvironment(string $environment): self
    {
        $this->configuration = $this->configuration->withEnvironment($environment);

        return $this;
    }

    public function withDebug(bool $debug): self
    {
        $this->configuration = $this->configuration->withDebug($debug);

        return $this;
    }

    public function withBundles(array $bundles): self
    {
        $this->configuration = $this->configuration->withBundles($bundles);

        return $this;
    }

    public function withBundle(BundleInterface $bundle): self
    {
        $this->configuration = $this->configuration->withBundle($bundle);

        return $this;
    }

    public function withBundleConfiguration(string $extensionName, array $configuration): self
    {
        $this->configuration = $this->configuration->withBundleConfiguration($extensionName, $configuration);

        return $this;
    }

    public function withPublicService(string $serviceId): self
    {
        $this->configuration = $this->configuration->withPublicServiceId($serviceId);

        return $this;
    }

    public function withPublicServices(array $serviceIds): self
    {
        $this->configuration = $this->configuration->withPublicServiceIds($serviceIds);

        return $this;
    }

    public function withTempDir(string $tempDir): self
    {
        $this->configuration = $this->configuration->withTempDir($tempDir);

        return $this;
    }

    public function withKernelClass(string $kernelClass): self
    {
        $this->guardKernelClass($kernelClass);

        $this->kernelClass = $kernelClass;

        return $this;
    }

    private function guardKernelClass(string $kernelClass): void
    {
        if (!\class_exists($kernelClass)) {
            throw new ClassNotFoundException($kernelClass);
        }

        if (TestKernel::class !== $kernelClass && !\in_array(TestKernel::class, \class_parents($kernelClass))) {
            throw new KernelNotSupportedException($kernelClass, TestKernel::class);
        }
    }
}

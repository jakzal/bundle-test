<?php
declare(strict_types=1);

namespace Zalas\BundleTest\PHPUnit;

use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;
use Zalas\BundleTest\HttpKernel\TestKernel as TheTestKernel;

/**
 * Reproduces the behaviour of `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` for the `TestKernel`.
 *
 * Compared to its inspiration this trait offers a more dynamic way of booting the Symfony kernel.
 * Cache will be generated for each variation of the kernel configuration.
 */
trait TestKernel
{
    /**
     * @var KernelInterface|null
     */
    protected static $kernel;

    /**
     * @before
     */
    protected function resetKernel()
    {
        static::$kernel = null;
    }

    protected static function getKernelClass(): string
    {
        return $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'] ?? TheTestKernel::class;
    }

    protected static function bootKernel(array $options = []): KernelInterface
    {
        self::ensureKernelShutdown();

        static::$kernel = static::createKernel($options);
        static::$kernel->boot();

        return static::$kernel;
    }

    /**
     * Creates a test kernel.
     *
     * Compared to the original `WebTestCase` this method offers an additional `kernel_class` option due to a more
     * dynamic nature of this trait.
     *
     * Options:
     *
     *  * environment
     *  * debug
     *  * kernel_class
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        $kernelClass = $options['kernel_class'] ?? static::getKernelClass($options);

        if (!class_exists($kernelClass)) {
            throw new \RuntimeException(sprintf('Class `%s` does not exist or cannot be autoloaded.', $kernelClass));
        }

        if (TheTestKernel::class !== $kernelClass && !in_array(TheTestKernel::class, class_parents($kernelClass))) {
            throw new \LogicException(sprintf('Only the `%s` kernel implementations are supported, but `%s` was given.', TheTestKernel::class, $kernelClass));
        }

        return new $kernelClass(self::createKernelConfiguration($options));
    }

    protected static function createKernelConfiguration(array $options): KernelConfiguration
    {
        return (new KernelConfiguration())
            ->withEnvironment(
                $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test'
            )
            ->withDebug(
                (bool)($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true)
            );
    }

    /**
     * @after
     */
    protected static function ensureKernelShutdown(): void
    {
        if (null !== static::$kernel) {
            $container = static::$kernel->getContainer();
            static::$kernel->shutdown();
            if ($container instanceof ResettableContainerInterface) {
                $container->reset();
            }
        }
    }
}

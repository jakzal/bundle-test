<?php
declare(strict_types=1);

namespace Zalas\BundleTest\PHPUnit;

use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zalas\BundleTest\HttpKernel\KernelBuilder;
use Zalas\BundleTest\HttpKernel\TestKernel as TheTestKernel;

/**
 * Reproduces the behaviour of `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase` for the `TestKernel`.
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

        return static::$kernel = static::createKernelBuilder($options)->bootKernel();
    }

    /**
     * Creates a test kernel.
     *
     * Compared to the original `KernelTestCase` this method offers an additional `kernel_class` option due to a more
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
        return self::createKernelBuilder($options)->createKernel();
    }

    protected static function createKernelBuilder(array $options): KernelBuilder
    {
        $builder = new KernelBuilder();
        $builder->withKernelClass($options['kernel_class'] ?? static::getKernelClass());
        $builder->withEnvironment($options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test');
        $builder->withDebug((bool) ($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true));

        return $builder;
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

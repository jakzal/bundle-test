<?php

namespace Zalas\BundleTest\Tests\HttpKernel;

use Symfony\Component\HttpKernel\KernelInterface;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;
use Zalas\BundleTest\HttpKernel\TestKernel;
use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle;

class TestKernelTest extends TestCase
{
    public function test_it_is_a_symfony_kernel()
    {
        $kernel = new TestKernel(
            (new KernelConfiguration())
                ->withEnvironment('foo')
                ->withDebug(false)
        );

        $this->assertInstanceOf(KernelInterface::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    public function test_bundles_are_read_from_the_kernel_configuration()
    {
        $fooBundle = new FooBundle();

        $kernel = new TestKernel(
            (new KernelConfiguration())
                ->withBundle($fooBundle)
        );

        $this->assertSame([$fooBundle], $kernel->registerBundles());
    }
}

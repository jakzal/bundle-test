<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\PHPUnit;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\PHPUnit\TestKernel;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel;

class TestKernelTest extends TestCase
{
    use TestKernel;

    public function test_bootKernel_creates_the_test_kernel_with_defaults()
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    public function test_bootKernel_creates_the_test_kernel_for_given_environment_and_debug_flag()
    {
        $kernel = self::bootKernel([
            'environment' => 'foo',
            'debug' => false,
        ]);

        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    public function test_bootKernel_gives_access_to_the_previously_booted_kernel()
    {
        $kernel = self::bootKernel();

        $this->assertSame($kernel, self::$kernel);
    }

    public function test_bootKernel_boots_the_created_kernel()
    {
        $kernel = self::bootKernel();

        $this->assertNotNull($kernel->getContainer());
    }

    public function test_bootKernel_ensures_the_kernel_was_shut_down()
    {
        $kernel1 = self::bootKernel();
        $kernel2 = self::bootKernel();

        $this->assertNull($kernel1->getContainer());
        $this->assertNotNull($kernel2->getContainer());
    }

    public function test_ensureKernelShutdown_shuts_down_the_kernel()
    {
        $kernel = self::bootKernel();

        self::ensureKernelShutdown();

        $this->assertNull($kernel->getContainer());
    }

    public function test_ensureKernelShutdown_resets_the_container()
    {
        $kernel = self::bootKernel();

        $container = $kernel->getContainer();
        $container->set('foo.bar', new \stdClass());

        self::ensureKernelShutdown();

        $this->assertFalse($container->has('foo.bar'));
    }

    public function test_createKernel_creates_the_test_kernel_with_defaults()
    {
        $kernel = self::createKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    public function test_createKernel_creates_the_test_kernel_for_given_environment_and_debug_flag()
    {
        $kernel = self::createKernel([
            'environment' => 'foo',
            'debug' => false,
            'kernel_class' => CustomKernel::class,
        ]);

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    /**
     * @env APP_ENV=foo
     * @env APP_DEBUG=0
     * @env KERNEL_CLASS=Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel
     */
    public function test_createKernel_creates_the_kernel_based_on_environment_variables()
    {
        $kernel = self::createKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    /**
     * @server APP_ENV=foo
     * @server APP_DEBUG=0
     * @server KERNEL_CLASS=Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel
     */
    public function test_createKernel_creates_the_kernel_based_on_server_variables()
    {
        $kernel = self::createKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }
}

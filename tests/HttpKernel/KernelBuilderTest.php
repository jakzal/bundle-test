<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\HttpKernel;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\HttpKernel\Exception\ClassNotFoundException;
use Zalas\BundleTest\HttpKernel\Exception\KernelNotSupportedException;
use Zalas\BundleTest\HttpKernel\KernelBuilder;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;
use Zalas\BundleTest\HttpKernel\TestKernel;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\CustomKernel;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\DummyKernel;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle\Foo;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle\FooBundle;

class KernelBuilderTest extends TestCase
{
    public function test_it_throws_an_exception_if_the_class_does_not_exist()
    {
        $this->expectException(ClassNotFoundException::class);

        $builder = new KernelBuilder();
        $builder->withKernelClass('Foo\\Bar\\Baz');
    }

    public function test_it_throws_an_exception_if_class_is_not_a_test_kernel()
    {
        $this->expectException(KernelNotSupportedException::class);

        $builder = new KernelBuilder();
        $builder->withKernelClass(DummyKernel::class);
    }

    public function test_bootKernel_creates_the_test_kernel_with_defaults()
    {
        $builder = new KernelBuilder();

        $kernel = $builder->bootKernel();

        $this->assertInstanceOf(TestKernel::class, $kernel);
        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    public function test_it_creates_the_configured_kernel()
    {
        $builder = new KernelBuilder();
        $builder->withKernelClass(CustomKernel::class);

        $kernel = $builder->bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    public function test_bootKernel_creates_the_test_kernel_for_given_environment_and_debug_flag()
    {
        $builder = new KernelBuilder();
        $builder->withEnvironment('foo');
        $builder->withDebug(false);

        $kernel = $builder->bootKernel();

        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    public function test_bootKernel_boots_the_created_kernel()
    {
        $builder = new KernelBuilder();

        $kernel = $builder->bootKernel();

        $this->assertNotNull($kernel->getContainer());
    }

    public function test_createKernel_creates_the_test_kernel_with_defaults()
    {
        $builder = new KernelBuilder();

        $kernel = $builder->createKernel();

        $this->assertInstanceOf(TestKernel::class, $kernel);
        $this->assertSame('test', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    public function test_createKernel_creates_the_test_kernel_for_given_environment_and_debug_flag()
    {
        $builder = new KernelBuilder();
        $builder->withKernelClass(CustomKernel::class);
        $builder->withEnvironment('foo');
        $builder->withDebug(false);

        $kernel = $builder->createKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    public function test_it_enables_bundles()
    {
        $builder = new KernelBuilder();
        $builder->withBundles([new FooBundle()]);

        $kernel = $builder->bootKernel();
        $bundles = $kernel->getBundles();

        $this->assertCount(1, $bundles, 'One bundle was registered.');
        $this->assertArrayHasKey('FooBundle', $bundles, 'The "FooBundle" was registered.');
        $this->assertInstanceOf(FooBundle::class, $bundles['FooBundle'], 'The "FooBundle" was registered.');
    }

    public function test_it_enables_a_bundle()
    {
        $builder = new KernelBuilder();
        $builder->withBundle(new FooBundle());

        $kernel = $builder->bootKernel();
        $bundles = $kernel->getBundles();

        $this->assertCount(1, $bundles, 'One bundle was registered.');
        $this->assertArrayHasKey('FooBundle', $bundles, 'The "FooBundle" was registered.');
        $this->assertInstanceOf(FooBundle::class, $bundles['FooBundle'], 'The "FooBundle" was registered.');
    }

    public function test_it_configures_the_bundle()
    {
        $builder = new KernelBuilder();
        $builder->withBundle(new FooBundle());
        $builder->withBundleConfiguration('foo', ['enabled' => true]);
        $builder->withPublicService(Foo::class);

        $kernel = $builder->bootKernel();

        $this->assertTrue($kernel->getContainer()->has(Foo::class));
    }

    public function test_it_exposes_private_services_as_public()
    {
        $builder = new KernelBuilder();
        $builder->withBundle(new FooBundle());
        $builder->withBundleConfiguration('foo', ['enabled' => true]);
        $builder->withPublicServices([Foo::class]);

        $kernel = $builder->bootKernel();

        $this->assertTrue($kernel->getContainer()->has(Foo::class));
    }

    public function test_it_changes_the_temp_dir()
    {
        $builder = new KernelBuilder();
        $builder->withTempDir($tempDir = sys_get_temp_dir().'/KernelBuilderTest');

        $kernel = $builder->bootKernel();

        $this->assertStringStartsWith($tempDir, $kernel->getCacheDir());
    }

    public function test_it_uses_the_default_namespace_if_not_set()
    {
        $builder = new KernelBuilder();

        $kernel = $builder->bootKernel();

        $this->assertContains(KernelConfiguration::DEFAULT_NAMESPACE, $kernel->getCacheDir());
    }

    public function test_it_changes_the_namespace()
    {
        $builder = new KernelBuilder('FooBarBaz');

        $kernel = $builder->bootKernel();

        $this->assertContains('FooBarBaz', $kernel->getCacheDir());
    }
}

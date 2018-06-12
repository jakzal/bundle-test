<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\PHPUnit;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\HttpKernel\TestKernel;
use Zalas\BundleTest\PHPUnit\ConfigurableKernel;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\FooBundle\Foo;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\FooBundle\FooBundle;

class ConfigurableKernelTest extends TestCase
{
    use ConfigurableKernel;

    public function test_it_boots_the_test_kernel_by_default()
    {
        $kernel = self::bootKernel();

        $this->assertInstanceOf(TestKernel::class, $kernel);
    }

    public function test_it_configures_the_kernel_environment()
    {
        $this->givenEnvironment('foo');

        $kernel = self::bootKernel();

        $this->assertSame('foo', $kernel->getEnvironment());
    }

    public function test_it_enables_the_kernel_debug_flag()
    {
        $this->givenDebugIsEnabled();

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->isDebug());
    }

    public function test_it_disables_the_kernel_debug_flag()
    {
        $this->givenDebugIsDisabled();

        $kernel = self::bootKernel();

        $this->assertFalse($kernel->isDebug());
    }

    public function test_it_configures_the_kernel_class()
    {
        $this->givenKernel(CustomKernel::class);

        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
    }

    public function test_options_passed_while_configuring_the_kernel_take_precedence_over_previously_configured_ones()
    {
        $this->givenEnvironment('foo');
        $this->givenDebugIsDisabled();

        $kernel = self::bootKernel([
            'environment' => 'bar',
            'debug' => true,
            'kernel_class' => CustomKernel::class,
        ]);

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('bar', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    /**
     * @env APP_ENV=bar
     * @env APP_DEBUG=0
     * @env KERNEL_CLASS=Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel
     */
    public function test_environment_variables_are_read_if_kernel_was_not_configured_explicitly()
    {
        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('bar', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    /**
     * @server APP_ENV=bar
     * @server APP_DEBUG=0
     * @server KERNEL_CLASS=Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel
     */
    public function test_server_variables_are_read_if_kernel_was_not_configured_explicitly()
    {
        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('bar', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    /**
     * @env APP_ENV=bar
     * @env APP_DEBUG=1
     * @env KERNEL_CLASS=Zalas\BundleTest\HttpKernel\TestKernel
     */
    public function test_environment_variables_are_overridden_by_custom_configuration()
    {
        $this->givenEnvironment('foo');
        $this->givenDebugIsDisabled();
        $this->givenKernel(CustomKernel::class);

        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    /**
     * @server APP_ENV=bar
     * @server APP_DEBUG=1
     * @server KERNEL_CLASS=Zalas\BundleTest\HttpKernel\TestKernel
     */
    public function test_server_variables_are_overridden_by_custom_configuration()
    {
        $this->givenEnvironment('foo');
        $this->givenDebugIsDisabled();
        $this->givenKernel(CustomKernel::class);

        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('foo', $kernel->getEnvironment());
        $this->assertFalse($kernel->isDebug());
    }

    public function test_it_enables_bundles()
    {
        $this->givenBundlesAreEnabled([new FooBundle()]);

        $kernel = self::bootKernel();
        $bundles = $kernel->getBundles();

        $this->assertCount(1, $bundles, 'One bundle was registered.');
        $this->assertArrayHasKey('FooBundle', $bundles, 'The "FooBundle" was registered.');
        $this->assertInstanceOf(FooBundle::class, $bundles['FooBundle'], 'The "FooBundle" was registered.');
    }

    public function test_it_enables_a_bundle()
    {
        $this->givenBundleIsEnabled(new FooBundle());

        $kernel = self::bootKernel();
        $bundles = $kernel->getBundles();

        $this->assertCount(1, $bundles, 'One bundle was registered.');
        $this->assertArrayHasKey('FooBundle', $bundles, 'The "FooBundle" was registered.');
        $this->assertInstanceOf(FooBundle::class, $bundles['FooBundle'], 'The "FooBundle" was registered.');
    }

    public function test_it_configures_the_bundle()
    {
        $this->givenBundleIsEnabled(new FooBundle());
        $this->givenBundleConfiguration('foo', ['enabled' => true]);
        $this->givenPublicServiceId(Foo::class);

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->getContainer()->has(Foo::class));
    }

    public function test_it_exposes_private_services_as_public()
    {
        $this->givenBundleIsEnabled(new FooBundle());
        $this->givenBundleConfiguration('foo', ['enabled' => true]);
        $this->givenPublicServiceIds([Foo::class]);

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->getContainer()->has(Foo::class));
    }

    public function test_it_changes_the_temp_dir()
    {
        $this->givenTempDir($tempDir = \sys_get_temp_dir().'/ConfigurableKernelTest');

        $kernel = self::bootKernel();

        $this->assertStringStartsWith($tempDir, $kernel->getCacheDir());
    }

    public function test_that_namespace_is_defined_by_default()
    {
        $kernel = self::bootKernel();

        $this->assertContains('ConfigurableKernelTest', $kernel->getCacheDir());
    }

    public function test_environment_cannot_be_changed_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenEnvironment('foo');
    }

    public function test_debug_cannot_be_disabled_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenDebugIsDisabled();
    }

    public function test_debug_cannot_be_enabled_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenDebugIsEnabled();
    }

    public function test_kernel_class_cannot_be_changed_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenKernel(CustomKernel::class);
    }

    public function test_bundle_configuration_cannot_be_changed_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenBundleConfiguration('foo', ['enabled' => true]);
    }

    public function test_public_service_cannot_be_added_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenPublicServiceId('foo');
    }

    public function test_public_services_cannot_be_added_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenPublicServiceIds(['foo']);
    }

    public function test_bundle_cannot_be_enabled_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenBundleIsEnabled(new FooBundle());
    }

    public function test_bundles_cannot_be_enabled_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenBundlesAreEnabled([new FooBundle()]);
    }

    public function test_temp_dir_cannot_be_changed_once_kernel_is_booted()
    {
        $this->expectException(\LogicException::class);

        self::bootKernel();

        $this->givenTempDir('/tmp');
    }
}

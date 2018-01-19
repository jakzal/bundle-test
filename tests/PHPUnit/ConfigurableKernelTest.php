<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\PHPUnit;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\PHPUnit\ConfigurableKernel;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\CustomKernel;
use Zalas\BundleTest\Tests\PHPUnit\Fixtures\FooBundle\FooBundle;

class ConfigurableKernelTest extends TestCase
{
    use ConfigurableKernel;

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

    public function test_it_confgiures_the_kernel_class()
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
     * @backupGlobals enabled
     */
    public function test_environment_variables_take_precedence_over_options_previously_configured()
    {
        $this->givenEnvironment('foo');
        $this->givenDebugIsDisabled();

        $_ENV['APP_ENV'] = 'bar';
        $_ENV['APP_DEBUG'] = '1';
        $_ENV['KERNEL_CLASS'] = CustomKernel::class;

        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('bar', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
    }

    /**
     * @backupGlobals enabled
     */
    public function test_server_variables_take_precedence_over_options_previously_configured()
    {
        $this->givenEnvironment('foo');
        $this->givenDebugIsDisabled();

        $_SERVER['APP_ENV'] = 'bar';
        $_SERVER['APP_DEBUG'] = '1';
        $_SERVER['KERNEL_CLASS'] = CustomKernel::class;

        $kernel = self::bootKernel();

        $this->assertInstanceOf(CustomKernel::class, $kernel);
        $this->assertSame('bar', $kernel->getEnvironment());
        $this->assertTrue($kernel->isDebug());
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
        $this->givenPublicServiceId('foo.foo');

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->getContainer()->has('foo.foo'));
    }

    public function test_it_exposes_private_services_as_public()
    {
        $this->givenBundleIsEnabled(new FooBundle());
        $this->givenBundleConfiguration('foo', ['enabled' => true]);
        $this->givenPublicServiceIds(['foo.foo']);

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->getContainer()->has('foo.foo'));
    }

    public function test_it_changes_the_temp_dir()
    {
        $this->givenTempDir($tempDir = sys_get_temp_dir().'/ConfigurableKernelTest');

        $kernel = self::bootKernel();

        $this->assertStringStartsWith($tempDir, $kernel->getCacheDir());
    }

    public function test_that_namespace_is_defined_by_default()
    {
        $kernel = self::bootKernel();

        $this->assertContains('ConfigurableKernelTest', $kernel->getCacheDir());
    }
}

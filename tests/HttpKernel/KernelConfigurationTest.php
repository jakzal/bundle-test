<?php

namespace Zalas\BundleTest\Tests\HttpKernel;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\BarBundle;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle;

class KernelConfigurationTest extends TestCase
{
    public function test_a_new_object_is_created_when_environment_is_changed()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withEnvironment('foo');

        $this->assertNotSame($config, $newConfig);
        $this->assertSame(KernelConfiguration::DEFAULT_ENVIRONMENT, $config->getEnvironment());
        $this->assertSame('foo', $newConfig->getEnvironment());
    }

    public function test_a_new_object_is_created_when_debug_flag_is_changed()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withDebug(false);

        $this->assertNotSame($config, $newConfig);
        $this->assertTrue($config->isDebug());
        $this->assertFalse($newConfig->isDebug());
    }

    public function test_a_new_object_is_created_when_a_bundle_is_added()
    {
        $fooBundle = new FooBundle();

        $config = new KernelConfiguration();
        $newConfig = $config->withBundle($fooBundle);

        $this->assertNotSame($config, $newConfig);
        $this->assertSame([], $config->getBundles());
        $this->assertSame([$fooBundle], $newConfig->getBundles());
    }

    public function test_a_new_object_is_created_when_multiple_bundles_are_added()
    {
        $fooBundle = new FooBundle();
        $barBundle = new BarBundle();

        $config = new KernelConfiguration();
        $newConfig = $config->withBundles([$fooBundle, $barBundle]);

        $this->assertNotSame($config, $newConfig);
        $this->assertSame([], $config->getBundles());
        $this->assertSame([$fooBundle, $barBundle], $newConfig->getBundles());
    }

    public function test_bundles_are_added_to_the_previously_added_ones()
    {
        $fooBundle = new FooBundle();
        $barBundle = new BarBundle();

        $config = (new KernelConfiguration())
            ->withBundle($fooBundle)
            ->withBundle($barBundle);
        $otherConfig = (new KernelConfiguration())
            ->withBundles([$fooBundle])
            ->withBundles([$barBundle]);

        $this->assertSame([$fooBundle, $barBundle], $config->getBundles());
        $this->assertSame([$fooBundle, $barBundle], $otherConfig->getBundles());
    }

    public function test_a_new_object_is_created_when_a_bundle_is_configured()
    {
        $config = new KernelConfiguration();
        $newConfig = (new KernelConfiguration())
            ->withBundleConfiguration('foo', ['enabled' => true])
            ->withBundleConfiguration('bar', ['enabled' => false]);

        $this->assertNotSame($config, $newConfig);
        $this->assertSame([], $config->getAllBundleConfigurations());
        $this->assertSame(['foo' => ['enabled' => true], 'bar' => ['enabled' => false]], $newConfig->getAllBundleConfigurations());
    }

    public function test_bundle_configurations_are_appended_to_the_previously_added_ones()
    {
        $config = (new KernelConfiguration())
            ->withBundleConfiguration('foo', ['enabled' => true, 'foos' => 'foo1'])
            ->withBundleConfiguration('foo', ['foos' => 'foo2']);

        $this->assertSame(['foo' => ['enabled' => true, 'foos' => ['foo1', 'foo2']]], $config->getAllBundleConfigurations());
    }

    public function test_the_hash_is_the_same_for_same_environments()
    {
        $config1 = new KernelConfiguration();
        $config2 = $config1->withEnvironment(KernelConfiguration::DEFAULT_ENVIRONMENT);

        $this->assertSameHash($config1, $config2);
    }

    public function test_the_hash_is_unique_for_different_environments()
    {
        $config1 = (new KernelConfiguration())->withEnvironment('test');
        $config2 = $config1->withEnvironment('foo');

        $this->assertNotSameHash($config1, $config2);
    }

    public function test_the_hash_is_the_same_for_the_same_debug_flag()
    {
        $config1 = (new KernelConfiguration())->withDebug(false);
        $config2 = (new KernelConfiguration())->withDebug(false);

        $this->assertSameHash($config1, $config2);
    }

    public function test_the_hash_is_different_for_different_debug_flags()
    {
        $config1 = (new KernelConfiguration())->withDebug(true);
        $config2 = (new KernelConfiguration())->withDebug(false);

        $this->assertNotSameHash($config1, $config2);
    }

    public function test_the_hash_is_the_same_for_the_same_collection_of_bundles()
    {
        $config1 = (new KernelConfiguration())->withBundle(new FooBundle());
        $config2 = (new KernelConfiguration())->withBundle(new FooBundle());

        $this->assertSameHash($config1, $config2);
    }

    public function test_the_hash_is_different_for_different_collections_of_bundles()
    {
        $config1 = (new KernelConfiguration())->withBundle(new FooBundle());
        $config2 = (new KernelConfiguration())->withBundles([new FooBundle(), new BarBundle()]);

        $this->assertNotSameHash($config1, $config2);
    }

    public function test_the_hash_is_the_same_for_same_bundle_configurations()
    {
        $config1 = (new KernelConfiguration())->withBundleConfiguration('foo', ['enabled' => true]);
        $config2 = (new KernelConfiguration())->withBundleConfiguration('foo', ['enabled' => true]);

        $this->assertSameHash($config1, $config2);
    }

    public function test_the_hash_is_different_for_different_bundle_configurations()
    {
        $config1 = (new KernelConfiguration())->withBundleConfiguration('foo', ['enabled' => true]);
        $config2 = (new KernelConfiguration())->withBundleConfiguration('foo', ['enabled' => false]);

        $this->assertNotSameHash($config1, $config2);
    }

    private function assertSameHash(KernelConfiguration $conf1, KernelConfiguration $conf2)
    {
        $this->assertSame($conf1->getHash(), $conf2->getHash());
    }

    private function assertNotSameHash(KernelConfiguration $conf1, KernelConfiguration $conf2)
    {
        $this->assertNotEquals($conf1->getHash(), $conf2->getHash());
    }
}

<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\HttpKernel;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\HttpKernel\KernelConfiguration;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\BarBundle\BarBundle;
use Zalas\BundleTest\Tests\HttpKernel\Fixtures\FooBundle\FooBundle;

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

    public function test_a_new_object_is_created_if_the_temp_dir_is_changed()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withTempDir('/tmp');

        $this->assertNotSame($config, $newConfig);
    }

    public function test_the_temp_dir_is_scoped_by_the_configuration_hash()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withTempDir('/tmp');

        $tempDir = \sys_get_temp_dir().'/'.KernelConfiguration::DEFAULT_NAMESPACE.'/'.$config->getHash();
        $newTempDir = '/tmp/'.KernelConfiguration::DEFAULT_NAMESPACE.'/'.$newConfig->getHash();

        $this->assertSame($tempDir, $config->getTempDir());
        $this->assertSame($newTempDir, $newConfig->getTempDir());
    }

    public function test_the_namespace_can_be_customised()
    {
        $config = (new KernelConfiguration('Foo'))->withTempDir('/tmp');

        $this->assertSame('/tmp/Foo/'.$config->getHash(), $config->getTempDir());
    }

    public function test_the_cache_dir_is_in_the_temp_dir()
    {
        $config = (new KernelConfiguration('Foo'))->withEnvironment('foo')->withTempDir('/tmp');
        $cacheDir = \sprintf('/tmp/Foo/%s/var/cache/foo', $config->getHash());

        $this->assertSame($cacheDir, $config->getCacheDir());
    }

    public function test_the_log_dir_is_in_the_temp_dir()
    {
        $config = (new KernelConfiguration('Foo'))->withEnvironment('foo')->withTempDir('/tmp');
        $logDir = \sprintf('/tmp/Foo/%s/var/log', $config->getHash());

        $this->assertSame($logDir, $config->getLogDir());
    }

    public function test_a_new_object_is_created_if_a_public_service_is_configured()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withPublicServiceId('foo.bar');

        $this->assertNotSame($config, $newConfig);
        $this->assertEmpty($config->getPublicServiceIds());
        $this->assertSame(['foo.bar'], $newConfig->getPublicServiceIds());
    }

    public function test_a_new_object_is_created_if_public_services_are_configured()
    {
        $config = new KernelConfiguration();
        $newConfig = $config->withPublicServiceIds(['foo.bar']);

        $this->assertNotSame($config, $newConfig);
        $this->assertEmpty($config->getPublicServiceIds());
        $this->assertSame(['foo.bar'], $newConfig->getPublicServiceIds());
    }

    public function test_public_service_id_configurations_are_appended_to_the_previously_added_ones()
    {
        $config = (new KernelConfiguration())
            ->withPublicServiceId('foo.bar1')
            ->withPublicServiceId('foo.bar2');

        $this->assertSame(['foo.bar1', 'foo.bar2'], $config->getPublicServiceIds());
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

    public function test_the_hash_is_unique_per_namespace()
    {
        $this->assertSameHash(new KernelConfiguration('foo1'), new KernelConfiguration('foo1'));
        $this->assertNotSameHash(new KernelConfiguration('foo1'), new KernelConfiguration('foo2'));
    }

    public function test_the_hash_is_unique_per_temp_dir()
    {
        $this->assertSameHash((new KernelConfiguration())->withTempDir('/tmp'), (new KernelConfiguration())->withTempDir('/tmp'));
        $this->assertNotSameHash((new KernelConfiguration())->withTempDir('/tmp'), new KernelConfiguration());
    }

    public function test_the_hash_is_unique_per_public_service_id_collection()
    {
        $this->assertSameHash((new KernelConfiguration())->withPublicServiceId('foo.bar'), (new KernelConfiguration())->withPublicServiceId('foo.bar'));
        $this->assertNotSameHash((new KernelConfiguration())->withPublicServiceId('foo.bar1'), (new KernelConfiguration())->withPublicServiceId('foo.bar2'));
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

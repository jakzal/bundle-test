# Bundle Test

[![Build Status](https://travis-ci.org/jakzal/bundle-test.svg?branch=master)](https://travis-ci.org/jakzal/bundle-test)

Tools for testing Symfony Bundles.

**This is work in progress**.

## Introduction

Symfony made it easy to test bundles with a Kernel booted thanks to the `KernelTestCase` and the `MicrokernelTrait`.
However, there's no convenient way to recreate scenarios of bundles configured in various ways.
One solution would be to create several test kernels with different configurations. This is boring, tedious and in most
cases not maintainable. Also, changing the configuration on the fly, per test case, would mean having to deal
with manual cache clearing before every test case is executed.

The Symfony2Extension Behat extension "suffers" from similar issues. "suffers" is put in quotes as those tools
were simply designed for different use cases.

This library offers an alternative - a configurable test kernel that automatically puts each configuration in its own scope.
Its aim is to make writing integration tests for Symfony Bundles easier and more high level.

## PHPUnit

```php
namespace Acme\Tests;

use Acme\FooBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Zalas\BundleTest\PHPUnit\ConfigurableKernel;
use Zalas\BundleTest\PHPUnit\ContainerAssertions;

class FooBundleTest extends TestCase
{
    use ConfigurableKernel;
    use ContainerAssertions;

    public function testTheFooServiceIsDisabledByDefault()
    {
        $this->givenBundlesAreEnabled([new FrameworkBundle(), new FooBundle()]);

        $kernel = self::bootKernel();

        $this->assertServiceIsNotDefined('foo', $kernel->getContainer());
    }

    public function testTheFooServiceIsEnabledInConfiguration()
    {
        $this->givenBundlesAreEnabled([new FrameworkBundle(), new FooBundle()]);
        $this->givenBundleConfiguration('foo', ['enabled' => true]);

        $kernel = self::bootKernel();

        $this->assertServiceIsDefined('foo', Foo::class, $kernel->getContainer());
    }
}
```

### ConfigurableKernel

The `Zalas\BundleTest\PHPUnit\ConfigurableKernel` trait provides a convenient way to configure and then boot the
Symfony kernel in tests.

Here's a full example of available methods:

```php
namespace Acme\Tests;

use PHPUnit\Framework\TestCase;
use Zalas\BundleTest\PHPUnit\ConfigurableKernel;

class FooTest extends TestCase
{
    use ConfigurableKernel;

    public function testItEnablesTheFooService()
    {
        $this->givenEnvironment('foo')
            ->givenDebugIsEnabled()
            ->givenBundleIsEnabled([new FooBundle()])
            ->givenBundleConfiguration('foo', ['enabled' => true, 'bar' => 'baz'])
            ->givenPublicServiceId('foo')
            ->givenKernel(CustomKernel::class)
            ->givenTempDir('/tmp');

        $kernel = self::bootKernel();

        $this->assertTrue($kernel->getContainer()->has('foo'));
    }
}
```

A new kernel configuration is generated for each set of given* calls. If another test case uses the same
given* calls the kernel cache will be reused. Otherwise a new one's generated.

## Behat

```php
use Acme\FooBundle;
use Behat\Behat\Context;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class FooContext implements Context
{
    private $applicationRunner;

    public function __construct()
    {
        $this->applicationRunner = new ApplicationRunner();
        $this->applicationRunner->enableBundle(new FrameworkBundle());
    }

    /**
     * @Given the :bundle bundle is enabled
     */
    public function givenTheBundleIsEnabled(string $bundle)
    {
        $class = 'Foo\'.$bundle;
        if (!class_exists($class)) {
            throw new \LogicException(sprintf('The bundle "%s" does not exist.', $class));
        }

        $this->applicationRunner->enableBundle(new $class());
    }

    /**
     * @When the :event event is dispatched with :userId
     */
    public function whenTheEventIsDispatchedWithUserId(string $event, string $userId)
    {
        $supportedEvents = [
            'user.verified' => 'Foo\Event\UserVerifiedEvent',
        ];

        if (!isset($supportedEvents[$event])) {
            throw new \LogicException(sprintf('The event "%s" is not recognised.', $event));
        }

        $eventClass = $supportedEvents[$event];

        $this->applicationRunner->dispatchEvent($event, new $eventClass($userId));
    }

    /**
     * @Then the :userId user should enabled
     */
    public function thenTheUserShouldBeEnabled(string $userId)
    {
        $user = $this->applicationRunner->getService('user.repository')->find($userId);

        if (!$user->isEnabled()) {
            throw new \LogicException(sprintf('The user %s should be enabled', $userId));
        }
    }
}
```

<?php

namespace Zalas\BundleTest\Tests\PHPUnit\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class DummyKernel extends Kernel
{
    public function registerBundles()
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}
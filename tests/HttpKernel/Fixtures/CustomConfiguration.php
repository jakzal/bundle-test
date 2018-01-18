<?php
declare(strict_types=1);

namespace Zalas\BundleTest\Tests\HttpKernel\Fixtures;

use Zalas\BundleTest\KernelConfiguration;

class CustomConfiguration extends KernelConfiguration
{
    protected $environment = 'foo';
}

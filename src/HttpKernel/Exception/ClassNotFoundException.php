<?php
declare(strict_types=1);

namespace Zalas\BundleTest\HttpKernel\Exception;

use Zalas\BundleTest\HttpKernel\Exception;

final class ClassNotFoundException extends \RuntimeException implements Exception
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Class `%s` does not exist or cannot be autoloaded.', $class));
    }
}

<?php
declare(strict_types=1);

namespace Zalas\BundleTest\HttpKernel\Exception;

use Zalas\BundleTest\HttpKernel\Exception;

final class KernelNotSupportedException extends \LogicException implements Exception
{
    public function __construct(string $actualClass, string $supportedParent)
    {
        parent::__construct(sprintf('Only the `%s` kernel implementations are supported, but `%s` was given.', $supportedParent, $actualClass));
    }
}

<?php

namespace Zalas\BundleTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /**
     * @var KernelConfiguration
     */
    private $configuration;

    public function __construct(KernelConfiguration $configuration)
    {
        parent::__construct($configuration->getEnvironment(), $configuration->isDebug());

        $this->configuration = $configuration;
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles()
    {
        return $this->configuration->getBundles();
    }

    /**
     * Loads the container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }
}
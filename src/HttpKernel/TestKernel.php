<?php
declare(strict_types=1);

namespace Zalas\BundleTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    public function getCacheDir()
    {
        return $this->configuration->getCacheDir();
    }

    public function getLogDir()
    {
        return $this->configuration->getLogDir();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $this->loadExtensionConfigurations($container);
        });
        $loader->load(function (ContainerBuilder $container) {
            $this->makeServicesPublic($container);
        });
    }

    private function loadExtensionConfigurations(ContainerBuilder $container)
    {
        foreach ($this->configuration->getAllBundleConfigurations() as $extension => $configuration) {
            $container->loadFromExtension($extension, $configuration);
        }
    }

    private function makeServicesPublic(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new class($this->configuration) implements CompilerPassInterface {
                private $configuration;

                public function __construct(KernelConfiguration $configuration)
                {
                    $this->configuration = $configuration;
                }

                public function process(ContainerBuilder $container)
                {
                    foreach ($this->configuration->getPublicServiceIds() as $serviceId) {
                        if ($container->hasDefinition($serviceId)) {
                            $container->getDefinition($serviceId)->setPublic(true);
                        }
                        if ($container->hasAlias($serviceId)) {
                            $container->getAlias($serviceId)->setPublic(true);
                        }
                    }
                }
            }
        );
    }
}

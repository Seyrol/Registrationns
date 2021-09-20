<?php


declare(strict_types=1);

namespace Provider\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Provider\Controller\ProviderController;
use Provider\Model\Table\ProviderTable;

class ProviderControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ProviderController(
            $container->get(ProviderTable::class)
        );
    }
}

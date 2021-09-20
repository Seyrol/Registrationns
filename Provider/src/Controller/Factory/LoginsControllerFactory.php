<?php


declare(strict_types=1);

namespace Provider\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Provider\Controller\LoginsController;
use Provider\Controller\ProviderController;
use Provider\Model\Table\ProviderTable;

class LoginsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LoginsController(
            $container->get(Adapter::class),
            $container->get(providerTable::class)
        );
    }
}

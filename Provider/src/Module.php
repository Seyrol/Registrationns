<?php


namespace Provider;

use Laminas\Db\Adapter\Adapter;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Provider\Model\Table\ProviderTable;

class Module implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }


    public function getServiceConfig(): array
    {
        return [
            'factories' => [

                ProviderTable::class => function ($sm) {
                    $dbAdapter = $sm->get(Adapter::class);
                    return new ProviderTable($dbAdapter);
                },

            ]
        ];
    }
}
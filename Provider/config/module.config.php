<?php


declare(strict_types=1);

namespace Provider;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'signups' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/signups',
                    'defaults' => [
                        'controller' => Controller\ProviderController::class,
                        'action' => 'creates'
                    ],
                ],
            ],
            'logins' => [
                'type' => Segment::class, # change route type from Literal to Segment
                'options' => [
                    'route' => '/logins[/:returnUrl]',
                    'constraints' => [
                        'returnUrl' => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\LoginsController::class,
                        'action' => 'test'
                    ],
                ],
            ],
            'profiles' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/profiles[/:id[/:username]]',
                    'constraints' => [
                        'id' => '[0-9]+',
                        'username' => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ProfilesController::class,
                        'action' => 'test'
                    ],
                ],
            ],
            'logouts' => [
                'type' => Segment::class, # change route type from Literal to Segment
                'options' => [
                    'route' => '/logouts',
                    'defaults' => [
                        'controller' => Controller\LogoutsController::class,
                        'action' => 'test'
                    ],
                ],
            ],
            'errors' => [
                'type' => Segment::class, # change route type from Literal to Segment
                'options' => [
                    'route' => '/errors',
                    'defaults' => [
                        'controller' => Controller\ProviderController::class,
                        'action' => 'errors'
                    ],
                ],
            ],

        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ProviderController::class => Controller\Factory\ProviderControllerFactory::class,
            Controller\LoginsController::class => Controller\Factory\LoginsControllerFactory::class,
            Controller\LogoutsController::class => InvokableFactory::class,
            Controller\ProfilesController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [

            /** auth template map */
            'provider/creates' => __DIR__ . '/../view/provider/provider/creates.phtml',
            'provider/index' => __DIR__ . '/../view/provider/provider/logins.phtml',
            'profiles/index' => __DIR__ . '/../view/provider/profile/profiles.phtml',
        ],
        'template_path_stack' => [
            'provider' => __DIR__ . '/../view'
        ]
    ]
];


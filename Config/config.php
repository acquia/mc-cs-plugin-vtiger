<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name' => 'vTiger CRM',
    'description' => 'Enables VTiger CRM integration',
    'version' => '2.0',
    'author'      => 'Mautic',
    'services'    => [
        'events'       => [
        ],
        'forms'        => [
        ],
        'helpers'      => [
        ],
        'other'        => [
            'mautic.guzzle_http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
            'mautic.vtiger.client' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Client::class,
                'arguments' => [
                    'mautic.vtiger.connection',
                    'mautic.vtiger.repository_manager'
                ]
            ],
            'mautic.vtiger.connection' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection::class,
                'arguments' => [
                    'mautic.guzzle_http.client',
                    'mautic.helper.integration'
                ]
            ],
            'mautic.vtiger.repository_manager' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\RepositoryManager::class,
                'arguments' => [
                    'mautic.vtiger.connection'
                ]
            ]
        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.vtiger_crm' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration::class,
                'arguments' => [
                    'mautic.lead.model.field',
                ],
                'tags' => ['mautic.integration', 'mautic.basic_integration', 'mautic.dispatcher_integration', 'mautic.encryption_integration']
            ],
        ],
    ],
    'routes'      => [
        'main'   => [
        ],
        'public' => [
        ],
        'api'    => [
        ],
    ],
    'menu'        => [
    ],
    'parameters'  => [
    ],
];

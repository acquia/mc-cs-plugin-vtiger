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
        'events' => [
            'mautic.vtiger_crm.subscriber' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\IntegrationEventSubscriber::class,
                'arguments' => ['mautic.vtiger_crm.sync.data_exchange', 'mautic.helper.integration'],
            ],
        ],
        'forms'        => [
        ],
        'helpers'      => [
        ],
        'other'        => [
            'mautic.guzzle_http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
            'mautic.vtiger_crm.connection' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection::class,
                'arguments' => [
                    'mautic.guzzle_http.client',
                    'mautic.helper.integration'
                ]
            ],
            'mautic.vtiger_crm.repository.contacts' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.repository.accounts' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.repository.events' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.mapping.field_mapping' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper::class,
                'arguments' => [
                    'service_container'
                ],
            ],
            'mautic.vtiger_crm.sync.data_exchange' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange::class,
                'arguments' => ['mautic.vtiger_crm.mapping.field_mapping', 'mautic.integrations.helper.sync_mapping_helper', 'mautic.vtiger_crm.sync.data_exchange_contacts'],
            ],

            'mautic.vtiger_crm.sync.data_exchange_contacts' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange::class,
                'arguments' => ['mautic.vtiger_crm.repository.contacts', 'mautic.lead.model.lead'],
            ],

        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.vtiger_crm' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration::class,
                'arguments' => [
                    'mautic.lead.model.field',
                    'mautic.lead.model.lead',
                    'translator',
                    'mautic.vtiger_crm.mapping.field_mapping',
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

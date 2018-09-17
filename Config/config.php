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
            'mautic.vtiger_crm.integration_sync' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\IntegrationSyncService::class,
                'arguments' => ['mautic.vtiger_crm.sync.data_exchange', 'mautic.helper.integration'],
                'tag'       => 'mautic.sync_integration'
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
            'mautic.vtiger_crm.settings' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'service_container'
                ]
            ],
            'mautic.vtiger_crm.connection' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection::class,
                'arguments' => [
                    'mautic.guzzle_http.client',
                    'mautic.vtiger_crm.settings'
                ]
            ],
            'mautic.vtiger_crm.repository.contacts' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.repository.leads' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.repository.company_details' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\CompanyDetailsRepository::class,
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
            'mautic.vtiger_crm.repository.users' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.mapping.field_mapping' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper::class,
                'arguments' => [
                    'service_container',
                    'mautic.vtiger_crm.settings'
                ],
            ],
            'mautic.vtiger_crm.sync.data_exchange' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange::class,
                'arguments' => [
                    'mautic.vtiger_crm.mapping.field_mapping',
                    'mautic.integrations.helper.sync_mapping',
                    'mautic.vtiger_crm.sync.data_exchange_contacts',
                    'mautic.vtiger_crm.sync.data_exchange_leads',
                    'mautic.vtiger_crm.sync.data_exchange_company_details',
                    'mautic.vtiger_crm.sync.data_exchange_accounts',
                ],
            ],

            'mautic.vtiger_crm.sync.data_exchange_contacts' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange::class,
                'arguments' => ['mautic.vtiger_crm.repository.contacts', 'mautic.vtiger_crm.settings', 'mautic.lead.model.lead'],
            ],
            'mautic.vtiger_crm.sync.data_exchange_leads' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange::class,
                'arguments' => ['mautic.vtiger_crm.repository.leads', 'mautic.vtiger_crm.settings', 'mautic.lead.model.lead'],
            ],
            'mautic.vtiger_crm.sync.data_exchange_company_details' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\CompanyDetailsDataExchange::class,
                'arguments' => ['mautic.vtiger_crm.repository.company_details', 'mautic.vtiger_crm.settings', 'mautic.lead.model.company'],
            ],
            'mautic.vtiger_crm.sync.data_exchange_accounts' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\AccountDataExchange::class,
                'arguments' => ['mautic.vtiger_crm.repository.accounts', 'mautic.vtiger_crm.settings', 'mautic.lead.model.company'],
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
                    'mautic.vtiger_crm.settings',
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

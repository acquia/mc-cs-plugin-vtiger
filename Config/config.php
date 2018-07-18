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
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Mapping\FieldMapping::class,
                'arguments' => [
                    //'mautic.vtiger_crm.repository.contacts',
                    'service_container',
                ],
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

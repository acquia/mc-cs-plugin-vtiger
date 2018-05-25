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
        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.vtiger_crm' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerIntegration::class,
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

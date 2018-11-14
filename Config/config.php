<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'vTiger CRM',
    'description' => 'Enables VTiger CRM integration',
    'version'     => '2.0',
    'author'      => 'Mautic',
    'services'    => [
        'events'       => [
            'mautic.vtiger_crm.subscriber.events_sync' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\SyncEventsSubscriber::class,
                'arguments' => [
                    'mautic.vtiger_crm.sync.events_service',
                ],
            ],
            'mautic.vtiger_crm.subscriber.config_form_load' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\ConfigFormLoadSubscriber::class,
                'arguments' => [
                    'mautic.vtiger_crm.cache.field_cache',
                ],
            ],
        ],
        'validators' => [
            'mautic.vtiger_crm.validator.connection_validator' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Validator\Constraints\ConnectionValidator::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'translator',
                ],
                'tags' => [
                    'name' => 'validator.constraint_validator',
                ]
            ]
        ],
        'forms'        => [
            'mautic.vtiger_crm.form.config_auth' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigAuthType::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection'
                ]
            ],
            'mautic.vtiger_crm.form.config_features' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigSyncFeaturesType::class,
                'arguments' => [
                    'mautic.vtiger_crm.repository.users',
                ],
            ],
        ],
        'helpers'      => [
        ],
        'other'        => [
            'mautic.guzzle_http.client'                   => [
                'class' => GuzzleHttp\Client::class,
            ],
            'mautic.vtiger_crm.settings'                  => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider::class,
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
            'mautic.vtiger_crm.connection'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection::class,
                'arguments' => [
                    'mautic.guzzle_http.client',
                    'mautic.vtiger_crm.settings',
                ],
            ],
            'mautic.vtiger_crm.transformer.vtiger2mautic' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer::class,
                'arguments' => ['mautic.vtiger_crm.repository.leads','mautic.vtiger_crm.repository.contacts','mautic.vtiger_crm.repository.accounts'],
            ],
            'mautic.vtiger_crm.transformer.mautic2vtiger' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer::class,
                'arguments' => [],
            ],
            'mautic.vtiger_crm.value_normalizer'          => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer::class,
                'arguments' => [
                    'mautic.vtiger_crm.transformer.vtiger2mautic',
                    'mautic.vtiger_crm.transformer.mautic2vtiger',
                ],
            ],
            'mautic.vtiger_crm.validator.general'         => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\GeneralValidator::class,
                'arguments' => ['mautic.vtiger_crm.repository.users'],
            ],
            'mautic.vtiger_crm.validator.contact'         => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator::class,
                'arguments' => ['mautic.vtiger_crm.repository.contacts', 'mautic.vtiger_crm.validator.general'],
            ],
            'mautic.vtiger_crm.repository.contacts'       => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'mautic.vtiger_crm.cache.field_cache',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.vtiger_crm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtiger_crm.validator.lead'            => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator::class,
                'arguments' => ['mautic.vtiger_crm.repository.leads', 'mautic.vtiger_crm.validator.general'],
            ],

            'mautic.vtiger_crm.repository.leads'           => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'mautic.vtiger_crm.cache.field_cache',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.vtiger_crm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtiger_crm.cache.field_cache' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache::class,
                'arguments' => [
                    'mautic.helper.cache_storage',
                ],
            ],
            'mautic.vtiger_crm.validator.account'          => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\AccountValidator::class,
                'arguments' => ['mautic.vtiger_crm.repository.accounts', 'mautic.vtiger_crm.validator.general'],
            ],

            'mautic.vtiger_crm.repository.accounts'   => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'mautic.vtiger_crm.cache.field_cache',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.vtiger_crm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtiger_crm.repository.events'     => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'mautic.vtiger_crm.cache.field_cache',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.vtiger_crm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtiger_crm.repository.users'      => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository::class,
                'arguments' => [
                    'mautic.vtiger_crm.connection',
                    'mautic.vtiger_crm.cache.field_cache',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.vtiger_crm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtiger_crm.mapping.field_mapping' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper::class,
                'arguments' => [
                    'mautic.vtiger_crm.settings',
                    'mautic.vtiger_crm.repository.contacts',
                    'mautic.vtiger_crm.repository.leads',
                    'mautic.vtiger_crm.repository.accounts',
                ],
            ],
            'mautic.vtiger_crm.sync.data_exchange'    => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange::class,
                'arguments' => [
                    'mautic.vtiger_crm.mapping.field_mapping',
                    'mautic.vtiger_crm.sync.data_exchange_contacts',
                    'mautic.vtiger_crm.sync.data_exchange_leads',
                    'mautic.vtiger_crm.sync.data_exchange_accounts',
                    'mautic.integrations.sync.notification.handler_contact'
                ],
            ],

            'mautic.vtiger_crm.sync.data_exchange_contacts'        => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange::class,
                'arguments' => [
                    'mautic.vtiger_crm.settings',
                    'mautic.vtiger_crm.value_normalizer',
                    'mautic.vtiger_crm.repository.contacts',
                    'mautic.vtiger_crm.validator.contact',
                    'mautic.integrations.helper.sync_mapping',
                    'mautic.vtiger_crm.mapping.field_mapping',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.integrations.sync.notification.handler_contact'
                ],
            ],
            'mautic.vtiger_crm.sync.data_exchange_leads'           => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange::class,
                'arguments' => [
                    'mautic.vtiger_crm.settings',
                    'mautic.vtiger_crm.value_normalizer',
                    'mautic.vtiger_crm.repository.leads',
                    'mautic.vtiger_crm.validator.lead',
                    'mautic.vtiger_crm.modelFactory',
                    'mautic.integrations.sync.notification.handler_contact'
                ],
            ],
            'mautic.vtiger_crm.sync.data_exchange_accounts'        => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\AccountDataExchange::class,
                'arguments' => [
                    'mautic.vtiger_crm.settings',
                    'mautic.vtiger_crm.value_normalizer',
                    'mautic.vtiger_crm.repository.accounts',
                    'mautic.vtiger_crm.validator.account',
                    'mautic.vtiger_crm.modelFactory',
                ],
            ],
            'mautic.vtiger_crm.lead_event_supplier'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier::class,
                'arguments' => ['mautic.lead.model.lead', 'doctrine.orm.entity_manager'],
            ],
            'mautic.vtiger_crm.sync.events_service'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService::class,
                'arguments' => ['mautic.vtiger_crm.lead_event_supplier', 'mautic.vtiger_crm.repository.events', 'mautic.vtiger_crm.settings'],
            ],
            'mautic.vtiger_crm.modelFactory'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory::class,
                'arguments' => [],
            ],
            'mautic.vtiger_crm.fieldDirectionFactory' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction\FieldDirectionFactory::class,
                'arguments' => [],
            ],
        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.vtiger_crm'      => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration::class,
                'tags'      => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            'mautic.integration.vtiger_crm.sync' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSyncProvider::class,
                'tag'       => 'mautic.sync_integration',
                'arguments' => [
                    'mautic.vtiger_crm.sync.data_exchange',
                ],
            ],
            'mautic.integration.vtiger_crm.config' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerConfigProvider::class,
                'tag'       => 'mautic.config_integration',
                'arguments' => [
                    'mautic.vtiger_crm.mapping.field_mapping',
                ],
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

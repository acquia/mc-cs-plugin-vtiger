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
    'name'        => 'Vtiger CRM',
    'description' => 'Enables Vtiger CRM integration',
    'version'     => '2.0',
    'author'      => 'Mautic',
    'services'    => [
        'events'       => [
            'mautic.vtigercrm.subscriber.events_sync' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\SyncEventsSubscriber::class,
                'arguments' => [
                    'mautic.vtigercrm.sync.events_service',
                ],
            ],
            'mautic.vtigercrm.subscriber.config_form_load' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\EventListener\ConfigFormLoadSubscriber::class,
                'arguments' => [
                    'mautic.vtigercrm.cache.field_cache',
                ],
            ],
        ],
        'validators' => [
            'mautic.vtigercrm.validator.connection_validator' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Validator\Constraints\ConnectionValidator::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'translator',
                ],
                'tags' => [
                    'name' => 'validator.constraint_validator',
                ]
            ]
        ],
        'forms'        => [
            'mautic.vtigercrm.form.config_auth' => [
                'class' => \MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigAuthType::class,
                'arguments' => [
                    'mautic.vtigercrm.connection'
                ]
            ],
            'mautic.vtigercrm.form.config_features' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigSyncFeaturesType::class,
                'arguments' => [
                    'mautic.vtigercrm.repository.users',
                ],
            ],
        ],
        'helpers'      => [
        ],
        'other'        => [
            'mautic.guzzle_http.client'                   => [
                'class' => GuzzleHttp\Client::class,
            ],
            'mautic.vtigercrm.settings'                  => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider::class,
                'arguments' => [
                    'mautic.integrations.helper',
                ],
            ],
            'mautic.vtigercrm.connection'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection::class,
                'arguments' => [
                    'mautic.guzzle_http.client',
                    'mautic.vtigercrm.settings',
                ],
            ],
            'mautic.vtigercrm.transformer.vtiger2mautic' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer::class,
                'arguments' => ['mautic.vtigercrm.repository.leads','mautic.vtigercrm.repository.contacts','mautic.vtigercrm.repository.accounts'],
            ],
            'mautic.vtigercrm.transformer.mautic2vtiger' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer::class,
                'arguments' => [],
            ],
            'mautic.vtigercrm.value_normalizer'          => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer::class,
                'arguments' => [
                    'mautic.vtigercrm.transformer.vtiger2mautic',
                    'mautic.vtigercrm.transformer.mautic2vtiger',
                ],
            ],
            'mautic.vtigercrm.validator.general'         => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\GeneralValidator::class,
                'arguments' => ['mautic.vtigercrm.repository.users'],
            ],
            'mautic.vtigercrm.validator.contact'         => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator::class,
                'arguments' => ['mautic.vtigercrm.repository.contacts', 'mautic.vtigercrm.validator.general'],
            ],
            'mautic.vtigercrm.repository.contacts'       => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'mautic.vtigercrm.cache.field_cache',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.vtigercrm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtigercrm.validator.lead'            => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator::class,
                'arguments' => ['mautic.vtigercrm.repository.leads', 'mautic.vtigercrm.validator.general'],
            ],

            'mautic.vtigercrm.repository.leads'           => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'mautic.vtigercrm.cache.field_cache',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.vtigercrm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtigercrm.cache.field_cache' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache::class,
                'arguments' => [
                    'mautic.helper.cache_storage',
                ],
            ],
            'mautic.vtigercrm.validator.account'          => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\AccountValidator::class,
                'arguments' => ['mautic.vtigercrm.repository.accounts', 'mautic.vtigercrm.validator.general'],
            ],

            'mautic.vtigercrm.repository.accounts'   => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'mautic.vtigercrm.cache.field_cache',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.vtigercrm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtigercrm.repository.events'     => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'mautic.vtigercrm.cache.field_cache',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.vtigercrm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtigercrm.repository.users'      => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository::class,
                'arguments' => [
                    'mautic.vtigercrm.connection',
                    'mautic.vtigercrm.cache.field_cache',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.vtigercrm.fieldDirectionFactory',
                ],
            ],
            'mautic.vtigercrm.mapping.field_mapping' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper::class,
                'arguments' => [
                    'mautic.vtigercrm.settings',
                    'mautic.vtigercrm.repository.contacts',
                    'mautic.vtigercrm.repository.leads',
                    'mautic.vtigercrm.repository.accounts',
                ],
            ],
            'mautic.vtigercrm.sync.data_exchange'    => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange::class,
                'arguments' => [
                    'mautic.vtigercrm.mapping.field_mapping',
                    'mautic.vtigercrm.sync.data_exchange_contacts',
                    'mautic.vtigercrm.sync.data_exchange_leads',
                    'mautic.vtigercrm.sync.data_exchange_accounts',
                    'mautic.integrations.sync.notification.handler_contact'
                ],
            ],

            'mautic.vtigercrm.sync.data_exchange_contacts'        => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange::class,
                'arguments' => [
                    'mautic.vtigercrm.settings',
                    'mautic.vtigercrm.value_normalizer',
                    'mautic.vtigercrm.repository.contacts',
                    'mautic.vtigercrm.validator.contact',
                    'mautic.integrations.helper.sync_mapping',
                    'mautic.vtigercrm.mapping.field_mapping',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.integrations.sync.notification.handler_contact',
                ],
            ],
            'mautic.vtigercrm.sync.data_exchange_leads'           => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange::class,
                'arguments' => [
                    'mautic.vtigercrm.settings',
                    'mautic.vtigercrm.value_normalizer',
                    'mautic.vtigercrm.repository.leads',
                    'mautic.vtigercrm.validator.lead',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.integrations.sync.notification.handler_contact',
                ],
            ],
            'mautic.vtigercrm.sync.data_exchange_accounts'        => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\AccountDataExchange::class,
                'arguments' => [
                    'mautic.vtigercrm.settings',
                    'mautic.vtigercrm.value_normalizer',
                    'mautic.vtigercrm.repository.accounts',
                    'mautic.vtigercrm.validator.account',
                    'mautic.vtigercrm.modelFactory',
                    'mautic.integrations.sync.notification.handler_company',
                ],
            ],
            'mautic.vtigercrm.lead_event_supplier'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier::class,
                'arguments' => ['mautic.lead.model.lead', 'doctrine.orm.entity_manager'],
            ],
            'mautic.vtigercrm.sync.events_service'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService::class,
                'arguments' => ['mautic.vtigercrm.lead_event_supplier', 'mautic.vtigercrm.repository.events', 'mautic.vtigercrm.settings'],
            ],
            'mautic.vtigercrm.modelFactory'                => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory::class,
                'arguments' => [],
            ],
            'mautic.vtigercrm.fieldDirectionFactory' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction\FieldDirectionFactory::class,
                'arguments' => [],
            ],
        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.vtigercrm'      => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration::class,
                'tags'      => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            'mautic.integration.vtigercrm.sync' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSyncProvider::class,
                'tag'       => 'mautic.sync_integration',
                'arguments' => [
                    'mautic.vtigercrm.sync.data_exchange',
                ],
            ],
            'mautic.integration.vtigercrm.config' => [
                'class'     => \MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerConfigProvider::class,
                'tag'       => 'mautic.config_integration',
                'arguments' => [
                    'mautic.vtigercrm.mapping.field_mapping',
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

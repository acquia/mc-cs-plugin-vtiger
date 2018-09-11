<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Integration\AuthenticationIntegration;
use MauticPlugin\IntegrationsBundle\Integration\BasicIntegration;
use MauticPlugin\IntegrationsBundle\Integration\DispatcherIntegration;
use MauticPlugin\IntegrationsBundle\Integration\EncryptionIntegration;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\AuthenticationInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\DispatcherInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\EncryptionInterface;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\OwnerMapper;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class VtigerCrmIntegration
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Integration
 */
class VtigerCrmIntegration extends BasicIntegration implements
    BasicInterface,
    AuthenticationInterface,
    DispatcherInterface,
    EncryptionInterface
{
    use AuthenticationIntegration;
    use DispatcherIntegration;
    use EncryptionIntegration;

    /**
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ObjectFieldMapper
     */
    private $fieldMapping;

    /**
     * @var VtigerSettingProvider
     */
    private $settingsProvider;

    const NAME = 'VtigerCrm';

    /**
     * VtigerCrmIntegration constructor.
     *
     * @param FieldModel          $fieldModel
     * @param LeadModel           $leadModel
     * @param TranslatorInterface $translator
     * @param ObjectFieldMapper   $fieldMapping
     * @param AccountRepository   $settingsProvider
     */
    public function __construct(
        FieldModel $fieldModel,
        LeadModel $leadModel,
        TranslatorInterface $translator,
        ObjectFieldMapper $fieldMapping,
        VtigerSettingProvider $settingsProvider
    )
    {
        $this->fieldModel = $fieldModel;
        $this->leadModel = $leadModel;
        $this->translator = $translator;
        $this->fieldMapping = $fieldMapping;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string { return self::NAME; }

    /** @inheritdoc */
    public function getIcon() { return 'plugins/MauticVtigerCrmBundle/Assets/img/vtiger_crm.png'; }

    /** @inheritdoc */
    public function getRequiredKeyFields(): array
    {
        return [
            'url'       => 'mautic.vtiger.form.url',
            'username'  => 'mautic.vtiger.form.username',
            'accessKey' => 'mautic.vtiger.form.password',
        ];
    }

    /** @inheritdoc */
    public function getClientIdKey(): string { return 'username'; }

    /** @inheritdoc */
    public function getClientSecretKey(): string { return 'accessKey'; }

    /** @inheritdoc */
    public function getAuthTokenKey(): string { return 'sessionName'; }

    /** @inheritdoc */
    public function getApiUrl(): string { return sprintf('%s/webservice.php', $this->keys['url']); }

    public function appendToForm(FormBuilder $builder, array $data, string $formArea) {
        if ($formArea !== 'features') {
            return;
        }

        $builder->add(
            'updateOwner',
            ChoiceType::class,
            [
                'choices' => [
                    'updateOwner' => 'mautic.plugin.vtiger.updateOwner',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.plugin.vtiger.form.updateOwner',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );
        $builder->add(
            'updateBlanks',
            ChoiceType::class,
            [
                'choices' => [
                    'updateBlanks' => 'mautic.integrations.blanks',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.integrations.form.blanks',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );
        $builder->add(
            'updateDncByDate',
            ChoiceType::class,
            [
                'choices' => [
                    'updateDncByDate' => 'mautic.integrations.update.dnc.by.date',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.integrations.form.update.dnc.by.date.label',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );
        $builder->add(
            'objects_to_pull',
            ChoiceType::class,
            [
                'choices' => [
                    'Leads'     => 'mautic.plugin.vtiger.object.lead',
                    'Contacts'  => 'mautic.plugin.vtiger.object.contact',
                    'Accounts' => 'mautic.plugin.vtiger.object.company',
                    //'Account'  => 'mautic.plugin.vtiger.object.account',
                    //'Activity' => 'mautic.plugin.vtiger.object.activity',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.plugin.vtiger.form.objects_to_pull_from',
                'label_attr'  => ['class' => ''],
                'empty_value' => false,
                'required'    => false,
            ]
        );
        $builder->add(
            'objects',
            ChoiceType::class,
            [
                'choices' => [
                    'lead'     => 'mautic.plugin.vtiger.object.contact',
                    'AbstractLead'  => 'mautic.plugin.vtiger.object.abstract_lead',
                    //'Account'  => 'mautic.plugin.vtiger.object.account',
                    'company' => 'mautic.plugin.vtiger.object.account',
                    //'activity' => 'mautic.plugin.vtiger.object.activity',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.plugin.vtiger.form.objects_to_push',
                'label_attr'  => ['class' => ''],
                'empty_value' => false,
                'required'    => false,
            ]
        );

        $builder->add(
            'activityEvents',
            ChoiceType::class,
            [
                'choices'    => $this->leadModel->getEngagementTypes(),
                'label'      => 'mautic.salesforce.form.activity_included_events',
                'label_attr' => [
                    'class'       => 'control-label',
                    'data-toggle' => 'tooltip',
                    'title'       => $this->translator->trans('mautic.plugin.vtiger.form.activity.events.tooltip'),
                ],
                'multiple'   => true,
                'required'   => false,
            ]
        );

        if ($this->isAuthorized()) {
            $builder->add(
                'owner',
                ChoiceType::class,
                [
                    'choices'    => $this->settingsProvider->getFormOwners(),
                    'label'      => 'mautic.plugin.vtiger.form.owner',
                    'label_attr' => [
                        'class'       => 'control-label',
                    ],
                    'multiple'   => false,
                    'required'   => true,
                ]
            );
        }

//        $builder->add(
//            'leadFields',
//            'integration_fields',
//            [
//                'label'                => 'mautic.integration.leadfield_matches',
//                'required'             => true,
//                'mautic_fields'        => $this->getFormLeadFields(),
//                'data'                 => $data,
//                'integration_fields'   => $fields,
//                'enable_data_priority' => $enableDataPriority,
//                'integration'          => $integrationObject->getName(),
//                'integration_object'   => $integrationObject,
//                'limit'                => $limit,
//                'page'                 => $page,
//                'mapped'               => false,
//                'error_bubbling'       => false,
//            ]
//        );
    }

    /**
     *
     *
     * @return bool
     */
    public function isAuthorized()
    {
        if (!$this->isConfigured()) {
            return false;
        }

        return true;
    }


    /**
     * Checks to see if the integration is configured by checking that required keys are populated.
     *
     * @return bool
     */
    public function isConfigured()
    {
        $credentialsCfg = $this->getDecryptedApiKeys($this->getIntegrationSettings());

        if (!isset($credentialsCfg['accessKey']) || !isset($credentialsCfg['username']) || !isset($credentialsCfg['url'])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $settings
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function getFormLeadFields(array $settings = [])
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $leadFields    = $this->fieldMapping->getObjectFields('Contacts');

        unset($leadFields['assigned_user_id']);

        return $leadFields;
    }

    public function getFormCompanyFields($settings = [])
    {
        $fields = $this->fieldMapping->getObjectFields('Accounts');
        return $fields;
    }
}

<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticIntegrationsBundle\Integration\AuthenticationIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\BasicIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\DispatcherIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\EncryptionIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\AuthenticationInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\BasicInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\DispatcherInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\EncryptionInterface;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\FieldMapping;
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
     * @var FieldMapping
     */
    private $fieldMapping;

    /**
     * VtigerCrmIntegration constructor.
     *
     * @param FieldModel          $fieldModel
     * @param LeadModel           $leadModel
     * @param TranslatorInterface $translator
     * @param FieldMapping        $fieldMapping
     */
    public function __construct(
        FieldModel $fieldModel,
        LeadModel $leadModel,
        TranslatorInterface $translator,
        FieldMapping $fieldMapping
    )
    {
        $this->fieldModel = $fieldModel;
        $this->leadModel = $leadModel;
        $this->translator = $translator;
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string { return 'VtigerCrm'; }

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
            'objects',
            ChoiceType::class,
            [
                'choices' => [
                    'Lead'     => 'mautic.plugin.vtiger.object.lead',
                    'Contact'  => 'mautic.plugin.vtiger.object.contact',
                    'Account'  => 'mautic.plugin.vtiger.object.account',
                    'Activity' => 'mautic.plugin.vtiger.object.activity',
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

    }

    public function isConfigured()
    {
        return true; // Method in Trait does not work, so bypass it temporary
    }

    public function isAuthorized()
    {
        if (!$this->isConfigured()) {
            return false;
        }

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

        $leadFields    = $this->fieldMapping->getLeadFields();

        return $leadFields;
    }

    /**
     * @return array
     */
    public function getSupportedFeatures(): array
    {
        return ($this->isConfigured() && $this->getSupportedFeatures()) ? $this->getSupportedFeatures() : [];
    }

    public function getMappedFields() {
        return $this->fieldMapping->getLeadFields();
        return empty($this->getFeatureSettings()['leadFields']) ? [] : $this->getFeatureSettings()['leadFields'];
    }

    /**
     * @param string $alias
     *
     * @return string
     *
     * @throws UnexpectedValueExceptionf
     */
    public function getFieldDirection(string $alias): string
    {
        if (isset($this->getMappedFieldsDirections()[$alias])) {
            return $this->getMappedFieldsDirections()[$alias];
        }

        throw new UnexpectedValueException("There is no field direction for field '${alias}'.");
    }

    /**
     * Returns direction of what field to sinc where.
     * In format [magento_field_alias => direction].
     *
     * @return array
     *
     * @throws UnexpectedValueException
     */
    public function getMappedFieldsDirections(): array
    {
        if (!$this->fieldDirections) {
            foreach ($this->getRawFieldDirections() as $alias => $rawValue) {
                $rawValueInt = (int) $rawValue;
                if (1 === $rawValueInt) {
                    $value = ObjectMappingDAO::SYNC_TO_MAUTIC;
                } elseif (0 === $rawValueInt) {
                    $value = ObjectMappingDAO::SYNC_TO_INTEGRATION;
                } else {
                    throw new UnexpectedValueException(
                        "Value '${rawValue}' is not supported as a mapped field direction."
                    );
                }

                $this->fieldDirections[$alias] = $value;
            }
        }

        return $this->fieldDirections;
    }

    /**
     * Returns mapped field directions in format [magento_field_alias => 0/1].
     *
     * @return array
     */
    private function getRawFieldDirections(): array
    {
        return empty($this->getFeatureSettings()['update_mautic']) ? [] : $this->getFeatureSettings()['update_mautic'];
    }

    /**
     * @var string[]
     */
    private $fieldDirections = [];
}

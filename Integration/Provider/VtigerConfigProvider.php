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

namespace MauticPlugin\MauticVtigerCrmBundle\Integration\Provider;

use MauticPlugin\IntegrationsBundle\Integration\ConfigurationTrait;
use MauticPlugin\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigAuthType;
use MauticPlugin\MauticVtigerCrmBundle\Form\Type\ConfigSyncFeaturesType;
use MauticPlugin\MauticVtigerCrmBundle\Integration\BasicTrait;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;

class VtigerConfigProvider implements ConfigFormInterface, ConfigFormSyncInterface, ConfigFormAuthInterface, ConfigFormFeaturesInterface
{
    use BasicTrait;
    use ConfigurationTrait;
    use DefaultConfigFormTrait;

    /**
     * @var ObjectFieldMapper
     */
    private $fieldMapping;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * VtigerConfigProvider constructor.
     *
     * @param ObjectFieldMapper $fieldMapping
     */
    public function __construct(ObjectFieldMapper $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * @return string
     */
    public function getAuthConfigFormName(): string
    {
        return ConfigAuthType::class;
    }

    /**
     * @return null|string
     */
    public function getSyncConfigFormName(): ?string
    {
        return ConfigSyncFeaturesType::class;
    }

    /**
     * @return array
     */
    public function getSupportedFeatures(): array
    {
        return [
            ConfigFormFeaturesInterface::FEATURE_SYNC          => 'mautic.integration.feature.sync',
            ConfigFormFeaturesInterface::FEATURE_PUSH_ACTIVITY => 'mautic.integration.feature.push_activity',
        ];
    }

    /**
     * @param string $object
     *
     * @return array
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function getOptionalFieldsForMapping(string $object): array
    {
        $fields = $this->getFields($object);

        $optionalFields = [];
        foreach ($fields as $fieldName => $field) {
            if ($field['required']) {
                continue;
            }

            $optionalFields[$fieldName] = $field['label'];
        }

        return $optionalFields;
    }

    /**
     * @param string $object
     *
     * @return array
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function getRequiredFieldsForMapping(string $object): array
    {
        $fields = $this->getFields($object);

        $requiredFields = [];
        foreach ($fields as $fieldName => $field) {
            if (!$field['required']) {
                continue;
            }

            $requiredFields[$fieldName] = $field['label'];
        }

        return $requiredFields;
    }

    /**
     * @return array
     */
    public function getSyncConfigObjects(): array
    {
        return [
            'Leads'    => 'mautic.plugin.vtiger.object.lead',
            'Contacts' => 'mautic.plugin.vtiger.object.contact',
            'Accounts' => 'mautic.plugin.vtiger.object.company',
        ];
    }

    /**
     * @return array
     */
    public function getSyncMappedObjects(): array
    {
        return $this->fieldMapping->getMapping();
    }

    /**
     * @param string $object
     *
     * @return mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    private function getFields(string $object): array
    {
        if (isset($this->fields[$object])) {
            return $this->fields[$object];
        }

        $this->fields[$object] = $this->fieldMapping->getObjectFields($object);
        unset($this->fields[$object]['assigned_user_id']);

        return $this->fields[$object];
    }
}

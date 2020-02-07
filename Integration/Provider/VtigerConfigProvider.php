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

use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use Mautic\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
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
            'mautic.integration.feature.sync' => ConfigFormFeaturesInterface::FEATURE_SYNC,
            //ConfigFormFeaturesInterface::FEATURE_PUSH_ACTIVITY => 'mautic.integration.feature.push_activity',
        ];
    }

    /**
     * @param string $object
     *
     * @return array|MappedFieldInfoInterface[]
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getOptionalFieldsForMapping(string $object): array
    {
        $fields = $this->getFields($object);

        $optionalFields = [];
        foreach ($fields as $fieldName => $field) {
            if ($field->showAsRequired()) {
                continue;
            }

            $optionalFields[$fieldName] = $field;
        }

        return $optionalFields;
    }

    /**
     * @param string $object
     *
     * @return array|MappedFieldInfoInterface[]
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getRequiredFieldsForMapping(string $object): array
    {
        $fields = $this->getFields($object);

        $requiredFields = [];
        foreach ($fields as $fieldName => $field) {
            if (!$field->showAsRequired()) {
                continue;
            }

            $requiredFields[$fieldName] = $field;
        }

        return $requiredFields;
    }

    /**
     * @param string $object
     *
     * @return array|MappedFieldInfoInterface[]
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getAllFieldsForMapping(string $object): array
    {
        $requiredFields = $this->getRequiredFieldsForMapping($object);
        asort($requiredFields);

        $optionalFields = $this->getOptionalFieldsForMapping($object);
        asort($optionalFields);

        return array_merge($requiredFields, $optionalFields);
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
     * @return array|MappedFieldInfoInterface[]
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    private function getFields(string $object): array
    {
        $fields = $this->fieldMapping->getObjectFields($object);
        unset($fields['assigned_user_id']);

        return $fields;
    }
}

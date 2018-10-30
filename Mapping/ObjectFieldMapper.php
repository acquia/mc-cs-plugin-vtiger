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

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ObjectFieldMapper provides all necessary information  to supply mapping information.
 */
class ObjectFieldMapper
{
    /**
     * Map Mautic objects to Vtiger module objects.
     *
     * @var array
     */
    private $vtiger2mauticObjectMapping = [
        'Contacts' => MauticSyncDataExchange::OBJECT_CONTACT,
        'Leads'    => MauticSyncDataExchange::OBJECT_CONTACT,
        'Accounts' => MauticSyncDataExchange::OBJECT_COMPANY,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $repositories;

    /**
     * @var VtigerSettingProvider
     */
    private $settings;

    /**
     * @param ContainerInterface    $container
     * @param VtigerSettingProvider $settingProvider
     */
    public function __construct(
        ContainerInterface $container,
        VtigerSettingProvider $settingProvider
    ) {
        $this->container = $container;
        $this->settings  = $settingProvider;
    }

    /**
     * @param $objectName
     *
     * @return array
     *
     * @throws InvalidQueryArgumentException
     */
    public function getObjectFields($objectName): array
    {
        if (!ModelFactory::isObjectSupported($objectName)) {
            throw new InvalidQueryArgumentException('Unknown object '.$objectName);
        }

        $this->repositories[$objectName] = $this->container->get('mautic.vtiger_crm.repository.'.strtolower($objectName));

        try {
            $fields = $this->repositories[$objectName]->getMappableFields();
        } catch (PluginNotConfiguredException $e) {
            return [];
        }

        $salesFields = [];

        /** @var ModuleFieldInfo $fieldInfo */
        foreach ($fields as $fieldInfo) {
            $type                               = 'string';
            $salesFields[$fieldInfo->getName()] = [
                'type'        => $type,
                'label'       => $fieldInfo->getLabel(),
                'required'    => $fieldInfo->isMandatory(),
                'optionLabel' => $fieldInfo->getLabel(),
            ];
        }

        asort($salesFields);

        return $salesFields;
    }

    /**
     * @return MappingManualDAO
     * @throws ObjectNotSupportedException*@throws InvalidQueryArgumentException
     * @throws InvalidQueryArgumentException
     */
    public function getObjectsMappingManual(): MappingManualDAO
    {
        $mappingManual = new MappingManualDAO(VtigerCrmIntegration::NAME);

        foreach ($this->settings->getSyncObjects() as $vtigerObject) {
            $objectMapping = new ObjectMappingDAO(
                $this->getVtiger2MauticObjectNameMapping($vtigerObject),
                $vtigerObject
            );

            $availableFields = $this->getObjectFields($vtigerObject);
            foreach ($this->settings->getFieldMappings($vtigerObject) as $vtigerField => $fieldMapping) {
                if (!isset($availableFields[$vtigerField])) {
                    continue;
                }

                $objectMapping->addFieldMapping(
                    $fieldMapping['mappedField'],
                    $vtigerField,
                    $fieldMapping['syncDirection'],
                    $availableFields[$vtigerField]['required']
                );
            }

            if (in_array($vtigerObject, [ContactDataExchange::OBJECT_NAME, LeadDataExchange::OBJECT_NAME])) {
                $objectMapping->addFieldMapping('mautic_internal_dnc_email', 'emailoptout', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
            }

            $mappingManual->addObjectMapping($objectMapping);
        }

        return $mappingManual;
    }

    /**
     * @param $objectName
     *
     * @return string
     *
     * @throws ObjectNotSupportedException
     */
    public function getMautic2VtigerObjectNameMapping($objectName): string
    {
        if (false === ($key = array_search($objectName, $this->vtiger2mauticObjectMapping))) {
            throw new ObjectNotSupportedException('Mautic', $objectName);
        }

        return $key;
    }

    /**
     * @param $vtigerObjectName
     *
     * @return mixed
     *
     * @throws ObjectNotSupportedException
     */
    public function getVtiger2MauticObjectNameMapping($vtigerObjectName)
    {
        if (!isset($this->vtiger2mauticObjectMapping[$vtigerObjectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $vtigerObjectName);
        }

        return $this->vtiger2mauticObjectMapping[$vtigerObjectName];
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->vtiger2mauticObjectMapping;
    }
}

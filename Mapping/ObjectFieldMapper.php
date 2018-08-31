<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\MauticFullContactBundle\Exception\Base;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ObjectFieldMapper
{
    static $objectToSettings = [
        'Contacts' => 'leadFields',
    ];

    static $mauticToVtigerObjectMapping = [
        'Lead' => 'Contacts'
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
     * @var \Mautic\PluginBundle\Integration\AbstractIntegration
     */
    private $integrationEntity;

    /** @var array */
    private $fieldDirections;

    /** @var VtigerSettingProvider  */
    private $settings;


    /**
     * ObjectFieldMapper constructor.
     *
     * @param ContainerInterface    $container
     * @param VtigerSettingProvider $settingProvider
     */
    public function __construct(
        ContainerInterface $container,
        VtigerSettingProvider $settingProvider
    )
    {
        $this->container = $container;
        $this->settings = $settingProvider;
    }


    public function getObjectFields($objectName): array
    {
        if (!isset(BaseRepository::$moduleClassMapping[$objectName])) {
            throw new InvalidArgumentException('Unknown object ' . $objectName);
        }

        $this->repositories[$objectName] = $this->container->get('mautic.vtiger_crm.repository.' . strtolower($objectName));

        $fields = $this->repositories[$objectName]->describe()->getFields();

        /** @var ModuleFieldInfo $fieldInfo */
        foreach ($fields as $fieldInfo) {
            $type = 'string';
            $salesFields[$fieldInfo->getName()] = [
                'type' => $type,
                'label' => $fieldInfo->getLabel(),
                'required' => $fieldInfo->isMandatory(),
                'optionLabel' => $fieldInfo->getLabel(),
            ];
        }

        asort($salesFields);

        return $salesFields;
    }

    public function getMappedFields($objectName): array
    {
        if (!isset(BaseRepository::$moduleClassMapping[$objectName]) || !isset(self::$objectToSettings[$objectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $objectName);
        }

        return empty($this->settings->getSetting(self::$objectToSettings[$objectName]))
            ? []
            : $this->settings->getSetting(self::$objectToSettings[$objectName]);
    }

    /**
     * @param string $alias
     *
     * @return string
     * @throws BadMappingDirectionException
     */
    public function getFieldDirection(string $alias): string
    {
        return ObjectMappingDAO::SYNC_BIDIRECTIONALLY;
        if (isset($this->getMappedFieldsDirections()[$alias])) {
            return $this->getMappedFieldsDirections()[$alias];
        }
        throw new BadMappingDirectionException("There is no field direction for field '${alias}'.");
    }

    /**
     * todo change to use correct object settings that are not yet implemented
     *
     * @param $objectName
     *
     * @return array
     */
    private function getFieldDirectionSettings($objectName): array
    {

        return empty($this->integrationEntity->getFeatureSettings()['update_mautic']) ? [] : $this->integrationEntity->getFeatureSettings()['update_mautic'];
    }

    /**
     * Returns direction of what field to sinc where.
     * In format [magento_field_alias => direction].
     * @return array|mixed[]
     * @throws BadMappingDirectionException
     */
    public function getMappedFieldsDirections($objectName = 'Contacts'): array
    {
        if (isset($this->fieldDirections[$objectName])) {
            return $this->fieldDirections[$objectName];
        }

        foreach ($this->getFieldDirectionSettings($objectName) as $alias => $rawValue) {
            $rawValueInt = (int)$rawValue;
            if (1 === $rawValueInt) {
                $value = ObjectMappingDAO::SYNC_TO_MAUTIC;
            } elseif (0 === $rawValueInt) {
                $value = ObjectMappingDAO::SYNC_TO_INTEGRATION;
            } else {
                throw new BadMappingDirectionException(
                    "Value '${rawValue}' is not supported as a mapped field direction."
                );
            }

            $this->fieldDirections[$alias] = $value;
        }

        return $this->fieldDirections;
    }

    public function getObjectsMappingManual(): MappingManualDAO
    {
        $mappingManual = new MappingManualDAO(VtigerCrmIntegration::NAME);

        foreach ($this->getSyncableObjects() as $mauticObject) {
            $objectMapping = new ObjectMappingDAO(
                $mauticObject,
                $this->getObjectNameMapping($mauticObject)
            );

            foreach ($this->getMappedFields($this->getObjectNameMapping($mauticObject)) as $vtigerField => $mauticField) {
                $objectMapping->addFieldMapping(
                    $mauticField,
                    $vtigerField,
                    $this->getFieldDirection($vtigerField)
                );
            }

            $mappingManual->addObjectMapping($objectMapping);
        }

        return $mappingManual;
    }

    /**
     * @return array
     */
    public function getSyncableObjects(): array
    {
        return $this->settings->getSetting('objects');
    }

    /**
     * @param $objectName
     *
     * @return string
     * @throws ObjectNotSupportedException
     */
    public function getObjectNameMapping($objectName): string
    {
        if (!isset(self::$mauticToVtigerObjectMapping[$objectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $objectName);
        }

        return self::$mauticToVtigerObjectMapping[$objectName];
    }
}

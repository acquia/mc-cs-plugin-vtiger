<?php
declare(strict_types=1);
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ObjectFieldMapper provides all necessary information  to supply mapping information
 * @package MauticPlugin\MauticVtigerCrmBundle\Mapping
 */
class ObjectFieldMapper
{
    /**
     * Maps module to settings in integration configuration
     * @var array
     */
    static $objectToSettings = [
        'Contacts' => 'leadFields',
        'Leads'    => 'leadFields',
        'Accounts' => 'companyFields',
    ];

    /**
     * Map mautic objects to Vtiger module objects
     * @var array
     */
    static $vtiger2mauticObjectMapping = [
        'Contacts' => 'lead',
        'Leads'    => 'lead',
        'Accounts' => 'company',
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

    /**
     * @var array
     */
    private $fieldDirections;

    /**
     * @var VtigerSettingProvider
     */
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
        $this->settings  = $settingProvider;
    }

    /**
     * @param $objectName
     *
     * @return array
     * @throws InvalidQueryArgumentException
     */
    public function getObjectFields($objectName): array
    {
        if (!isset(BaseRepository::$moduleClassMapping[$objectName])) {
            throw new InvalidQueryArgumentException('Unknown object ' . $objectName);
        }

        $this->repositories[$objectName] = $this->container->get('mautic.vtiger_crm.repository.' . strtolower($objectName));

        $fields = $this->repositories[$objectName]->describe()->getFields();


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
     * @param $objectName
     *
     * @return array
     * @throws ObjectNotSupportedException
     */
    public function getMappedFields($objectName): array
    {
        if (!isset(self::$objectToSettings[$objectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $objectName);
        }

        return empty($this->settings->getSetting(self::$objectToSettings[$objectName]))
            ? []
            : $this->settings->getSetting(self::$objectToSettings[$objectName]);
    }


    public function getObjectSyncDirection(string $vtigerObject, string $mauticObject)
    {
        $vtigerSyncable = $this->getVtigerSyncable();
        $mauticSyncable = $this->getSyncableObjects();

        /** a little hack */
        if (in_array('Lead', $mauticSyncable) && in_array('Leads', $vtigerSyncable)) {
            $mauticSyncable[] = 'AbstractLead';
        }

        if ($v = in_array($vtigerObject, $vtigerSyncable) && $m = in_array($mauticObject, $mauticSyncable)) {
            return ObjectMappingDAO::SYNC_BIDIRECTIONALLY;
        }

        if (false !== $v) {
            return ObjectMappingDAO::SYNC_TO_MAUTIC;
        }

        if (false !== $v) {
            return ObjectMappingDAO::SYNC_TO_INTEGRATION;
        }

        return false;
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
    public function getMappedFieldsDirections($objectName): array
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

    /**
     * @return MappingManualDAO
     * @throws ObjectNotSupportedException
     */
    public function getObjectsMappingManual(): MappingManualDAO
    {
        $mappingManual = new MappingManualDAO(VtigerCrmIntegration::NAME);

        foreach ($this->getVtigerSyncable() as $vtigerObject) {
            $objectMapping = new ObjectMappingDAO(
                $this->getVtiger2MauticObjectNameMapping($vtigerObject),
                $vtigerObject
            );

            $direction = $this->getObjectSyncDirection($vtigerObject, $this->getVtiger2MauticObjectNameMapping($vtigerObject));

            foreach ($this->getMappedFields($vtigerObject) as $vtigerField => $mauticField) {
                $objectMapping->addFieldMapping(
                    $mauticField,
                    $vtigerField,
                    $direction
                );
            }

            $mappingManual->addObjectMapping($objectMapping);
        }

        return $mappingManual;
    }

    /**
     * @return array
     */
    public function getVtigerSyncable(): array
    {
        return $this->settings->getSetting('objects');
    }

    /**
     * @return array
     */
    public function getSyncableObjects(): array
    {
        return $this->settings->getSetting('objects_mautic');
    }

    /**
     * @param $objectName
     *
     * @return string
     * @throws ObjectNotSupportedException
     */
    public function getMautic2VtigerObjectNameMapping($objectName): string
    {
        if (false === ($key = array_search($objectName, self::$vtiger2mauticObjectMapping))) {
            throw new ObjectNotSupportedException('Mautic', $objectName);
        }

        return $key;
    }

    public function getVtiger2MauticObjectNameMapping($vtigerObjectName)
    {
        if (!isset(self::$vtiger2mauticObjectMapping[$vtigerObjectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $vtigerObjectName);
        }

        return self::$vtiger2mauticObjectMapping[$vtigerObjectName];
    }

    public function getVtigerModelNameFromModuleName($moduleName)
    {
        return BaseRepository::$moduleClassMapping[$moduleName];
    }

}

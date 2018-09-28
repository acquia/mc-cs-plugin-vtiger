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
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
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
     * Map mautic objects to Vtiger module objects
     * @var array
     */
    static $vtiger2mauticObjectMapping = [
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
     * @var \Mautic\PluginBundle\Integration\AbstractIntegration
     */
    private $integrationEntity;

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
     * @return MappingManualDAO
     * @throws ObjectNotSupportedException
     */
    public function getObjectsMappingManual(): MappingManualDAO
    {
        $mappingManual = new MappingManualDAO(VtigerCrmIntegration::NAME);

        foreach ($this->settings->getSyncObjects() as $vtigerObject) {
            $objectMapping = new ObjectMappingDAO(
                $this->getVtiger2MauticObjectNameMapping($vtigerObject),
                $vtigerObject
            );

            foreach ($this->settings->getFieldMappings($vtigerObject) as $vtigerField => $fieldMapping) {
                $objectMapping->addFieldMapping(
                    $fieldMapping['mappedField'],
                    $vtigerField,
                    $fieldMapping['syncDirection']
                );
            }

            $mappingManual->addObjectMapping($objectMapping);
        }

        return $mappingManual;
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

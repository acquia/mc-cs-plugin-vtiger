<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24.8.18
 * Time: 13:36
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MauticPlugin\IntegrationsBundle\Sync\Mapping\MappingHelper;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;

class DataExchange implements SyncDataExchangeInterface
{
    /**
     * @var ObjectFieldMapper
     */
    private $fieldMapper;

    /**
     * @var ContactDataExchange
     */
    private $contactDataExchange;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * DataExchange constructor.
     *
     * @param ObjectFieldMapper   $fieldMapper
     * @param MappingHelper       $mappingHelper
     * @param ContactDataExchange $contactDataExchange
     */
    public function __construct(ObjectFieldMapper $fieldMapper, MappingHelper $mappingHelper, ContactDataExchange $contactDataExchange) {
        $this->fieldMapper = $fieldMapper;
        $this->contactDataExchange = $contactDataExchange;
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * @return ObjectFieldMapper
     */
    public function getFieldMapper(): ObjectFieldMapper
    {
        return $this->fieldMapper;
    }

    /**
     * @param ObjectFieldMapper $fieldMapper
     *
     * @return DataExchange
     */
    public function setFieldMapper(ObjectFieldMapper $fieldMapper): DataExchange
    {
        $this->fieldMapper = $fieldMapper;

        return $this;
    }

    /**
     * Get Sync report from integration
     *
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     * @throws ObjectNotSupportedException
     */
    public function getSyncReport(RequestDAO $requestDAO)
    {
        // Build a report of objects that have been modified
        $syncReport = new ReportDAO(VtigerCrmIntegration::NAME);

        if ($requestDAO->getSyncIteration() > 1) {
            // Prevent loop
            return $syncReport;
        }

        $requestedObjects = $requestDAO->getObjects();

        foreach ($requestedObjects as $requestedObject) {
            $objectName = $requestedObject->getObject();

            $exchangeService = $this->getDataExchangeService($objectName);

            /** @var  ContactDataExchange $exchangeService */
            $syncReport = $exchangeService->getObjectSyncReport($requestedObject, $syncReport);
        }

        return $syncReport;
    }

    /**
     * @param OrderDAO $syncOrderDAO
     *
     * @throws ObjectNotSupportedException
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO)
    {
        $identifiedObjects = $syncOrderDAO->getIdentifiedObjects();

        foreach ($identifiedObjects as $objectName => $updateObjects) {
            $updateCount = count($updateObjects);

            if (0 === $updateCount) {
                continue;
            }

            $identifiedObjectIds = $syncOrderDAO->getIdentifiedObjectIds($objectName);

            /** @var ContactDataExchange $dataExchange */
            $dataExchange = $this->getDataExchangeService($objectName);

            $updatedObjectMappings = $dataExchange->update($identifiedObjectIds, $updateObjects);

            $this->updateObjectMappings($updatedObjectMappings);
        }


        $unidentifiedObjects = $syncOrderDAO->getUnidentifiedObjects();
        foreach ($unidentifiedObjects as $objectName => $createObjects) {
            $createCount = count($createObjects);

            if (0 === $createCount) {
                continue;
            }

            /** @var ContactDataExchange $dataExchange */
            $dataExchange = $this->getDataExchangeService($objectName);

            $objectMappings = $dataExchange->insert($createObjects);

            $this->saveObjectMappings($objectMappings);
        }
    }

    /**
     * @param $objectName
     *
     * @return SyncDataExchangeInterface
     * @throws ObjectNotSupportedException
     */
    private function getDataExchangeService($objectName) {
        switch ($objectName) {
            case 'Contacts':
                return $this->contactDataExchange;
            default:
                throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $objectName);
        }
    }


    /**
     * @param UpdatedObjectMappingDAO[] $mappings
     */
    public function updateObjectMappings(array $mappings)
    {
        foreach ($mappings as $mapping) {
            $this->mappingHelper->updateObjectMapping($mapping);
        }
    }

    /**
     * @param ObjectMapping[] $mappings
     */
    public function saveObjectMappings(array $mappings)
    {
        foreach ($mappings as $mapping) {
            $this->mappingHelper->saveObjectMapping($mapping);
        }
    }
}
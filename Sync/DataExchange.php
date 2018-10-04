<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MauticPlugin\IntegrationsBundle\Sync\Helper\MappingHelper;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;

/**
 * Class DataExchange.
 */
final class DataExchange implements SyncDataExchangeInterface
{
    /**
     * @var ObjectFieldMapper
     */
    private $objectFieldMapper;

    /**
     * @var ContactDataExchange
     */
    private $contactDataExchange;

    /**
     * @var LeadDataExchange
     */
    private $leadDataExchange;

    /**
     * @var CompanyDetailsDataExchange
     */
    private $companyDetailsDataExchange;

    /**
     * @var AccountDataExchange
     */
    private $accountDataExchange;
    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var EventSyncService
     */
    private $eventSyncService;

    /**
     * DataExchange constructor.
     *
     * @param ObjectFieldMapper          $fieldMapper
     * @param MappingHelper              $mappingHelper
     * @param ContactDataExchange        $contactDataExchange
     * @param LeadDataExchange           $leadDataExchange
     * @param CompanyDetailsDataExchange $companyDetailsDataExchange
     * @param AccountDataExchange        $accountDataExchange
     */
    public function __construct(
        ObjectFieldMapper $objectFieldMapper,
        MappingHelper $mappingHelper,
        ContactDataExchange $contactDataExchange,
        LeadDataExchange $leadDataExchange,
        CompanyDetailsDataExchange $companyDetailsDataExchange,
        AccountDataExchange $accountDataExchange,
        EventSyncService $eventSyncService
    ) {
        $this->objectFieldMapper          = $objectFieldMapper;
        $this->contactDataExchange        = $contactDataExchange;
        $this->leadDataExchange           = $leadDataExchange;
        $this->mappingHelper              = $mappingHelper;
        $this->companyDetailsDataExchange = $companyDetailsDataExchange;
        $this->accountDataExchange        = $accountDataExchange;
        $this->eventSyncService           = $eventSyncService;
    }

    /**
     * @return ObjectFieldMapper
     */
    public function getFieldMapper(): ObjectFieldMapper
    {
        return $this->objectFieldMapper;
    }

    /**
     * @param ObjectFieldMapper $fieldMapper
     *
     * @return DataExchange
     */
    public function setFieldMapper(ObjectFieldMapper $objectFieldMapper): self
    {
        $this->objectFieldMapper = $objectFieldMapper;

        return $this;
    }

    /**
     * Get Sync report from integration.
     *
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     *
     * @throws ObjectNotSupportedException
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
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

            $syncReport = $exchangeService->getObjectSyncReport($requestedObject, $syncReport);
        }

        //$this->eventSyncService->sync();

        return $syncReport;
    }

    /**
     * @param OrderDAO $syncOrderDAO
     *
     * @throws ObjectNotSupportedException
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO): void
    {
        $syncOrderDAO->getSyncDateTime();

        $identifiedObjects   = $syncOrderDAO->getIdentifiedObjects();
        $unidentifiedObjects = $syncOrderDAO->getUnidentifiedObjects();

        foreach ($identifiedObjects as $objectName => $updateObjects) {
            $updateCount = count($updateObjects);

            if (0 === $updateCount) {
                continue;
            }

            $identifiedObjectIds = $syncOrderDAO->getIdentifiedObjectIds($objectName);
            /** @var ObjectSyncDataExchangeInterface $dataExchange */
            $dataExchange = $this->getDataExchangeService($objectName);

            $updatedObjectMappings = $dataExchange->update($identifiedObjectIds, $identifiedObjects[$objectName]);

            foreach ($updatedObjectMappings as $updateObject) {
                $syncOrderDAO->updateLastSyncDate($updateObject);
            }
        }

        foreach ($unidentifiedObjects as $objectName => $createObjects) {
            $createCount = count($createObjects);

            if (0 === $createCount) {
                continue;
            }

            /** @var ObjectSyncDataExchangeInterface $dataExchange */
            $dataExchange = $this->getDataExchangeService($objectName);

            $objectMappings = $dataExchange->insert($createObjects);

            /** @var ObjectChangeDAO $objectMapping */
            foreach ($objectMappings as $objectMapping) {
                $syncOrderDAO->addObjectMapping(
                    $objectMapping,
                    $objectMapping->getObject(),
                    $objectMapping->getObjectId(),
                    $objectMapping->getChangeDateTime()
                );
            }
        }
    }

    // @todo add delete support

    /**
     * @param $objectName
     *
     * @return ObjectSyncDataExchangeInterface
     *
     * @throws ObjectNotSupportedException
     */
    private function getDataExchangeService($objectName): ObjectSyncDataExchangeInterface
    {
        switch ($objectName) {
            case 'Contacts':
                return $this->contactDataExchange;
            case 'Leads':
                return $this->leadDataExchange;
            case 'CompanyDetails':
                return $this->companyDetailsDataExchange;
            case 'Accounts':
                return $this->accountDataExchange;
            default:
                throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $objectName);
        }
    }
}

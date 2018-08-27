<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24.8.18
 * Time: 13:36
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
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

    public function __construct(ObjectFieldMapper $fieldMapper, ContactDataExchange $contactDataExchange) {
        $this->fieldMapper = $fieldMapper;
        $this->contactDataExchange = $contactDataExchange;
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
     * Sync to integration
     *
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
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

            return $exchangeService->getSyncReport($requestDAO);
        }
    }

    /**
     * Sync from integration
     *
     * @param OrderDAO $syncOrderDAO
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO)
    {
        var_dump('execute sync order'); die();
        // TODO: Implement executeSyncOrder() method.
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
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     * @throws ObjectNotSupportedException
     */
    private function buildReportFromFullObjects(RequestDAO $requestDAO)
    {
        $syncReport       = new ReportDAO(self::NAME);
        $requestedObjects = $requestDAO->getObjects();

        $limit = 200;
        $start = $limit * ($requestDAO->getSyncIteration() - 1);

        foreach ($requestedObjects as $objectDAO) {
            $mauticFields = $this->getFieldList($objectDAO->getObject());

            DebugLogger::log(
                self::NAME,
                sprintf(
                    "Searching for %s objects between %s and %s (%d,%d)",
                    $objectDAO->getObject(),
                    $objectDAO->getFromDateTime()->format('Y:m:d H:i:s'),
                    $objectDAO->getToDateTime()->format('Y:m:d H:i:s'),
                    $start,
                    $limit
                ),
                __CLASS__.':'.__FUNCTION__
            );

            switch ($objectDAO->getObject()) {
                case self::OBJECT_CONTACT:
                    $foundObjects = $this->contactObjectHelper->findObjectsBetweenDates(
                        $objectDAO->getFromDateTime(),
                        $objectDAO->getToDateTime(),
                        $start,
                        $limit
                    );
                    break;
                case self::OBJECT_COMPANY:
                    $foundObjects = $this->companyObjectHelper->findObjectsBetweenDates(
                        $objectDAO->getFromDateTime(),
                        $objectDAO->getToDateTime(),
                        $start,
                        $limit
                    );
                    break;
                default:
                    throw new ObjectNotSupportedException(self::NAME, $objectDAO->getObject());
            }

            $fields = $objectDAO->getFields();
            foreach ($foundObjects as $object) {
                $modifiedDateTime = new \DateTime(
                    !empty($object['date_modified']) ? $object['date_modified'] : $object['date_added'],
                    new \DateTimeZone('UTC')
                );
                $objectDAO        = new ReportObjectDAO($objectDAO->getObject(), $object['id'], $modifiedDateTime);
                $syncReport->addObject($objectDAO);

                foreach ($fields as $field) {
                    $fieldType       = $this->getNormalizedFieldType($mauticFields[$field]['type']);
                    $normalizedValue = new NormalizedValueDAO(
                        $fieldType,
                        $object[$field]
                    );

                    $objectDAO->addField(new ReportFieldDAO($field, $normalizedValue));
                }
            }
        }

        return $syncReport;
    }
}
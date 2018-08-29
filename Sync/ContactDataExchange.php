<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24.8.18
 * Time: 13:50
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;

class ContactDataExchange implements SyncDataExchangeInterface
{
    const OBJECT_NAME = 'Contacts';

    /** @var ContactRepository */
    private $contactRepository;

    /** @var ValueNormalizer */
    private $valueNormalizer;

    /** @var array */
    private $fieldTypes;

    public function __construct(ContactRepository $contactRepository) {
        $this->contactRepository = $contactRepository;
        $this->valueNormalizer = new VtigerValueNormalizer();
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
        // TODO: Implement getSyncReport() method.
    }


    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject,ReportDAO &$syncReport)
    {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->contactRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                // Normalize the value from the API to what Mautic needs
                $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getType(), $value);

                $reportFieldDAO  = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    public function executeSyncOrder(OrderDAO $syncOrderDAO)
    {
        // TODO: Implement executeSyncOrder() method.
        var_dump('execute sync order'); die();
    }

    private function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields) {
        $report = $this->contactRepository->query('SELECT id,modifiedtime,'. join(',', $mappedFields) .' FROM Contacts WHERE modifiedtime>' . $fromDate->getTimestamp());
        return $report;
    }
}
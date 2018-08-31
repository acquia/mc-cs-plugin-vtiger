<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24.8.18
 * Time: 13:50
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ContactDataExchange implements SyncDataExchangeInterface
{
    const OBJECT_NAME = 'Contacts';

    /** @var ContactRepository */
    private $contactRepository;

    /** @var ValueNormalizer */
    private $valueNormalizer;

    /** @var LeadModel */
    private $model;

    /** @var VtigerSettingProvider  */
    private $settings;

    public function __construct(
        ContactRepository $contactRepository,
        VtigerSettingProvider $settingProvider,
        LeadModel $leadModel)
    {
        $this->contactRepository = $contactRepository;
        $this->valueNormalizer = new VtigerValueNormalizer();
        $this->model = $leadModel;
        $this->settings = $settingProvider;
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


    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject, ReportDAO &$syncReport)
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

                $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    public function executeSyncOrder(OrderDAO $syncOrderDAO)
    {
        // TODO: Implement executeSyncOrder() method.
        var_dump('execute sync order');
        die();
    }

    private function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $report = $this->contactRepository->query('SELECT id,modifiedtime,assigned_user_id,' . join(',', $mappedFields) . ' FROM Contacts WHERE modifiedtime>' . $fromDate->getTimestamp());

        return $report;
    }

    /**
     * @param array             $ids
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     */
    public function update(array $ids, array $objects)
    {
        DebugLogger::log(
            self::OBJECT_NAME,
            sprintf(
                "Found %d leads to update with ids %s",
                count($objects),
                implode(", ", $ids)
            ),
            __CLASS__ . ':' . __FUNCTION__
        );

        $updatedMappedObjects = [];
        /** @var ObjectChangeDAO $changedObject */
        foreach ($objects as $integrationObjectId => $changedObject) {
            $fields = $changedObject->getFields();

            $objectData = ['id'=>$integrationObjectId];

            foreach ($fields as $field) {
                /** @var \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO $field */
                $objectData[$field->getName()] = $field->getValue()->getNormalizedValue();
            }

            $vtigerModel = new Contact($objectData);

            if ($this->settings->getSetting('updateOwner')) {
                $vtigerModel->setAssignedUserId($this->settings->getSetting('owner'));
            }

            try {
                $returnedModel = $this->contactRepository->update($vtigerModel);

                // Integration name and ID are stored in the change's mappedObject/mappedObjectId
                $updatedMappedObjects[] = new UpdatedObjectMappingDAO(
                    $changedObject,
                    $changedObject->getObjectId(),
                    $changedObject->getObject(),
                    $returnedModel->getModifiedTime()
                );

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Updated to %s ID %s",
                        self::OBJECT_NAME,
                        $integrationObjectId
                    ),
                    __CLASS__ . ':' . __FUNCTION__
                );
            } catch (InvalidArgumentException $e) {
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Update to %s ID %s failed: %s",
                        self::OBJECT_NAME,
                        $integrationObjectId,
                        $e->getMessage()
                    ),
                    __CLASS__ . ':' . __FUNCTION__
                );
            }
        }

        return $updatedMappedObjects;
    }


    /**
     * @param ObjectChangeDAO[] $objects
     *
     * @return ObjectMapping[]
     */
    public function insert(array $objects)
    {
        $modelName = BaseRepository::$moduleClassMapping[self::OBJECT_NAME];

        $objectMappings = [];
        foreach ($objects as $object) {
            $fields = $object->getFields();

            $objectData = [];

            foreach ($fields as $field) {
                /** @var \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO $field */
                $objectData[$field->getName()] = $field->getValue()->getNormalizedValue();
            }
            /** @var Contact $contact */
            $contact = new $modelName($objectData);
            if (!$this->settings->getSetting('owner')) {
                throw new InvalidConfigurationException('You need to configure owner for new objects');
            }
            $contact->setAssignedUserId($this->settings->getSetting('owner'));

            try {
                $response = $this->contactRepository->create($contact);

                // Integration name and ID are stored in the change's mappedObject/mappedObjectId
                $updatedMappedObjects[] = new UpdatedObjectMappingDAO(
                    $object,
                    $object->getObjectId(),
                    $response->getId(),
                    $response->getModifiedTime()
                );

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Created Contact ID %s from Lead %d",
                        $response->getId(),
                        $object->getMappedObjectId()
                    ),
                    __CLASS__.':'.__FUNCTION__
                );

                $objectMapping = new ObjectMapping();
                $objectMapping->setLastSyncDate($response->getModifiedTime())
                    ->setIntegration($object->getIntegration())
                    ->setIntegrationObjectName($object->getMappedObject())
                    ->setIntegrationObjectId($object->getMappedObjectId())
                    ->setInternalObjectName(MauticSyncDataExchange::OBJECT_CONTACT)
                    ->setInternalObjectId($object->getMappedObjectId());
                $objectMappings[] = $objectMapping;
            } catch (InvalidArgumentException $e) {
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Failed to create %s with error '%s'",
                        self::OBJECT_NAME,
                        $e->getMessage()
                    ),
                    __CLASS__.':'.__FUNCTION__
                );
            }
        }

        return $objectMappings;
    }
}
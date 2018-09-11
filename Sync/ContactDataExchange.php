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
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class ContactDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;

    const OBJECT_NAME = 'Contacts';

    /** @var ContactRepository */
    private $objectRepository;

    /** @var ValueNormalizer */
    private $valueNormalizer;

    /** @var LeadModel */
    private $mauticModel;

    /** @var VtigerSettingProvider  */
    private $settings;

    /** @var int  */
    const VTIGER_CONTACT_API_QUERY_LIMIT = 100;

    public function __construct(
        ContactRepository $contactRepository,
        VtigerSettingProvider $settingProvider,
        LeadModel $leadModel)
    {
        $this->objectRepository = $contactRepository;
        $this->valueNormalizer = new VtigerValueNormalizer();
        $this->mauticModel = $leadModel;
        $this->settings = $settingProvider;
    }

    /**
     * @param \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO|mixed
     * @throws \Exception
     */
    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject, ReportDAO &$syncReport)
    {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->objectRepository->describe()->getFields();

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


    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = []; $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->objectRepository->query('SELECT id,modifiedtime,assigned_user_id,' . join(',', $mappedFields)
                . ' FROM Contacts WHERE modifiedtime>' . $fromDate->getTimestamp()
                . ' LIMIT ' . ($iteration*self::VTIGER_CONTACT_API_QUERY_LIMIT) . ',' . self::VTIGER_CONTACT_API_QUERY_LIMIT);

            $iteration++;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $report;
    }

    /**
     * @param array             $ids
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     */
    public function updateX(array $ids, array $objects)
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

            if ($this->settings->getSetting('updateOwner') || !$vtigerModel->getAssignedUserId()) {
                $vtigerModel->setAssignedUserId($this->settings->getSetting('owner'));
            }

            try {
                $returnedModel = $this->objectRepository->update($vtigerModel);

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

        DebugLogger::log(
            self::OBJECT_NAME,
            sprintf(
                "Found %d leads to INSERT",
                count($objects)
            ),
            __CLASS__ . ':' . __FUNCTION__
        );

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
                $response = $this->objectRepository->create($contact);

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
                $objectMapping
                    ->setIntegration(VtigerCrmIntegration::NAME)
                    ->setIntegrationObjectName($object->getMappedObject())
                    ->setIntegrationObjectId($response->getId())
                    ->setInternalObjectName($object->getObject())
                    ->setInternalObjectId($object->getObjectId());

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

    /**
     * @param array $objects
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function delete(array $objects)
    {
        // TODO: Implement delete() method.
        throw new \Exception('Not implemented');
    }


}
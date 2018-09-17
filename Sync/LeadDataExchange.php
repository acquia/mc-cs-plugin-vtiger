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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class LeadDataExchange implements ObjectSyncDataExchangeInterface
{
    const OBJECT_NAME = 'Leads';

    /**
     * @var LeadRepository
     */
    private $leadsRepository;

    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @var LeadModel
     */
    private $model;

    /**
     * @var VtigerSettingProvider
     */
    private $settings;

    /**
     * LeadDataExchange constructor.
     *
     * @param LeadRepository        $leadsRepository
     * @param VtigerSettingProvider $settingProvider
     * @param LeadModel             $leadModel
     */
    public function __construct(
        LeadRepository $leadsRepository,
        VtigerSettingProvider $settingProvider,
        LeadModel $leadModel)
    {
        $this->leadsRepository = $leadsRepository;
        $this->valueNormalizer = new VtigerValueNormalizer();
        $this->model = $leadModel;
        $this->settings = $settingProvider;
    }

    /**
     * @param \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO
     * @throws \Exception
     */
    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject, ReportDAO &$syncReport)
    {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->leadsRepository->describe()->getFields();

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
     * @return array
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = []; $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->leadsRepository->query('SELECT id,modifiedtime,assigned_user_id,' . join(',', $mappedFields)
                . ' FROM ' . self::OBJECT_NAME . ' WHERE modifiedtime>' . $fromDate->getTimestamp()
                . ' LIMIT ' . ($iteration*100) . ',100');

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

            $objectName = BaseRepository::$moduleClassMapping[self::OBJECT_NAME];
            var_dump($objectName);
            die();
            $vtigerModel = new $objectName($objectData);

            if ($this->settings->getSetting('updateOwner')) {
                $vtigerModel->setAssignedUserId($this->settings->getSetting('owner'));
            }

            try {
                $returnedModel = $this->leadsRepository->update($vtigerModel);

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
            /** @var Lead $lead */
            $lead = new $modelName($objectData);
            if (!$this->settings->getSetting('owner')) {
                throw new InvalidConfigurationException('You need to configure owner for new objects');
            }

            $lead->setAssignedUserId($this->settings->getSetting('owner'));

            try {
                $response = $this->leadsRepository->create($lead);

                $object->setObjectId($response->getId());
                // Integration name and ID are stored in the change's mappedObject/mappedObjectId
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Created Contact ID %s from Lead %d",
                        $response->getId(),
                        $object->getMappedObjectId()
                    ),
                    __CLASS__.':'.__FUNCTION__
                );


                $objectMapping = new ObjectChangeDAO(
                    $object->getIntegration(),
                    $object->getObject(),
                    $object->getObjectId(),
                    $object->getMappedObject(),
                    $response->getId()
                );

                $objectMapping->setChangeDateTime($response->getModifiedTime());
//                $objectMapping->setLastSyncDate($response->getModifiedTime())
//                    ->setIntegration($object->getIntegration())
//                    ->setIntegrationObjectName($object->getMappedObject())
//                    ->setIntegrationObjectId($object->getMappedObjectId())
//                    ->setInternalObjectName($object->getObject())
//                    ->setInternalObjectId($object->getObjectId());
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
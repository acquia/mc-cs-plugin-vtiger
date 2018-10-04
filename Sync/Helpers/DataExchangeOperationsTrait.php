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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

trait DataExchangeOperationsTrait
{
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
            sprintf('Found %d objects to update to integration with ids %s', count($objects), implode(', ', $ids)),
            __CLASS__.':'.__FUNCTION__
        );

        $updatedMappedObjects = [];

        /** @var ObjectChangeDAO $changedObject */
        foreach ($objects as $integrationObjectId => $changedObject) {
            $fields = $changedObject->getFields();

            $objectData = ['id'=>$changedObject->getObjectId()];

            foreach ($fields as $field) {
                /* @var \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO $field */
                $objectData[$field->getName()] = $field->getValue()->getNormalizedValue();
            }

            $modelClass = BaseRepository::$moduleClassMapping[$changedObject->getObject()];

            $vtigerModel = new $modelClass($objectData);

            if ($this->settings->getSetting('updateOwner') || !$vtigerModel->getAssignedUserId()) {
                $vtigerModel->setAssignedUserId($this->settings->getSetting('owner'));
            }

            try {
                $this->objectValidator->validate($vtigerModel);

                /** @var BaseModel $returnedModel */
                $returnedModel = $this->objectRepository->update($vtigerModel);

                $newChange = new ObjectChangeDAO(
                    VtigerCrmIntegration::NAME,
                    $changedObject->getObject(),
                    $changedObject->getObjectId(),
                    $changedObject->getMappedObject(),
                    $changedObject->getMappedObjectId()
                );

                $updatedMappedObjects[] = $newChange;

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf('Updated to %s ID %s', self::OBJECT_NAME, $integrationObjectId),
                    __CLASS__.':'.__FUNCTION__
                );
            } catch (InvalidQueryArgumentException $e) {
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        'Update to %s ID %s failed: %s',
                        self::OBJECT_NAME,
                        $integrationObjectId,
                        $e->getMessage()
                    ),
                    __CLASS__.':'.__FUNCTION__
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
            sprintf('Found %d %s to INSERT', $modelName, count($objects)),
            __CLASS__.':'.__FUNCTION__
        );

        $objectMappings = [];
        foreach ($objects as $object) {
            $fields = $object->getFields();

            $objectData = [];

            foreach ($fields as $field) {
                /* @var \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO $field */
                $objectData[$field->getName()] = $field->getValue()->getNormalizedValue();
            }

            /** @var Contact $object */
            $objectModel = new $modelName($objectData);
            if (!$this->settings->getSetting('owner')) {
                throw new InvalidConfigurationException('You need to configure owner for new objects');
            }
            $objectModel->setAssignedUserId($this->settings->getSetting('owner'));

            /* Perform validation */
            $this->objectValidator->validate($objectModel);

            try {
                $response = $this->objectRepository->create($objectModel);

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        'Created %s ID %s from %s %d',
                        self::OBJECT_NAME,
                        $response->getId(),
                        $object->getMappedObject(),
                        $object->getMappedObjectId()
                    ),
                    __CLASS__.':'.__FUNCTION__
                );

                $objectMapping = new ObjectChangeDAO(
                    $object->getIntegration(),
                    $object->getObject(),
                    $response->getId(),
                    $object->getMappedObject(),
                    $object->getMappedObjectId()
                );

                $objectMapping->setChangeDateTime($response->getModifiedTime());

                $objectMappings[] = $objectMapping;
            } catch (InvalidQueryArgumentException $e) {
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf("Failed to create %s with error '%s'", self::OBJECT_NAME, $e->getMessage()),
                    __CLASS__.':'.__FUNCTION__
                );
            }
        }

        return $objectMappings;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function getDeleted(\DateTimeImmutable $fromDate)
    {
        var_dump($this->objectRepository->sync((new \DateTime('-1 year'))->getTimestamp()));
        die();

        $fullReport = [];
        $iteration  = 0;
        // We must iterate while there is still some result left

        do {
            $reportQuery = 'SELECT * FROM '.self::OBJECT_NAME
                .' LIMIT '.($iteration * self::VTIGER_API_QUERY_LIMIT).','.self::VTIGER_API_QUERY_LIMIT;

            echo $reportQuery;
            $report = $this->objectRepository->query($reportQuery);

            ++$iteration;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $fullReport;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = [];
        $iteration  = 0;
        // We must iterate while there is still some result left

        do {
            $reportQuery = 'SELECT id,modifiedtime,assigned_user_id,'.join(',', $mappedFields)
                .' FROM '.self::OBJECT_NAME.' WHERE modifiedtime >= \''.$fromDate->format('Y-m-d H:i:s').'\''
                .' LIMIT '.($iteration * self::VTIGER_API_QUERY_LIMIT).','.self::VTIGER_API_QUERY_LIMIT;

            $report = $this->objectRepository->query($reportQuery);

            ++$iteration;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $fullReport;
    }
}

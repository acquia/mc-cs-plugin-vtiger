<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Mautic\IntegrationsBundle\Sync\Logger\DebugLogger;
use Mautic\IntegrationsBundle\Sync\Notification\Handler\HandlerInterface;
use Mautic\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\Validation\InvalidObject;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\AccountValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ObjectValidatorInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

abstract class GeneralDataExchange implements ObjectSyncDataExchangeInterface
{
    /**
     * @var VtigerSettingProvider
     */
    protected $vtigerSettingProvider;

    /**
     * @var VtigerValueNormalizer
     */
    protected $valueNormalizer;

    /**
     * @var HandlerInterface
     */
    private $notificationHandler;

    /**
     * @param VtigerSettingProvider $vtigerSettingProvider
     * @param ValueNormalizerInterface $valueNormalizer
     * @param HandlerInterface $notificationHandler
     */
    public function __construct(
        VtigerSettingProvider $vtigerSettingProvider,
        ValueNormalizerInterface $valueNormalizer,
        HandlerInterface $notificationHandler
    ) {
        $this->vtigerSettingProvider = $vtigerSettingProvider;
        $this->valueNormalizer       = $valueNormalizer;
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * @param array       $ids
     * @param BaseModel[] $objects
     * @param string      $objectName
     *
     * @return array
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function updateInternal(array $ids, array $objects, string $objectName): array
    {
        DebugLogger::log($objectName, sprintf('Found %d objects to update to integration with ids %s', count($objects), implode(', ', $ids)), __CLASS__ . ':' . __FUNCTION__);

        $updatedMappedObjects = [];

        /** @var ObjectChangeDAO $changedObject */
        foreach ($objects as $integrationObjectId => $changedObject) {
            $fields = $changedObject->getFields();

            $fields['id'] = new FieldDAO('id', new NormalizedValueDAO('string', $changedObject->getObjectId(), $changedObject->getObjectId()));

            $objectModel = $this->getModel($fields);

            if ($this->vtigerSettingProvider->isOwnerUpdateEnabled() || !$objectModel->getAssignedUserId()) {
                $objectModel->setAssignedUserId($this->vtigerSettingProvider->getOwner());
            }

            /* Perform validation */
            try {
                $this->getValidator()->validate($objectModel);
            } catch (InvalidObject $e) {
                $this->logInvalidObject($changedObject, $objectName, $e);
                continue;
            }

            try {
                $this->getRepository()->update($objectModel);

                $newChange = new ObjectChangeDAO(
                    VtigerCrmIntegration::NAME,
                    $changedObject->getObject(),
                    $changedObject->getObjectId(),
                    $changedObject->getMappedObject(),
                    $changedObject->getMappedObjectId()
                );

                $updatedMappedObjects[] = $newChange;

                DebugLogger::log(VtigerCrmIntegration::NAME, sprintf('Updated to %s ID %s', $objectName, $integrationObjectId), __CLASS__ . ':' . __FUNCTION__);
            } catch (InvalidQueryArgumentException $e) {
                DebugLogger::log(VtigerCrmIntegration::NAME, sprintf('Update to %s ID %s failed: %s', $objectName, $integrationObjectId, $e->getMessage()), __CLASS__ . ':' . __FUNCTION__);
            }
        }

        return $updatedMappedObjects;
    }

    /**
     * @param BaseModel[] $objects
     * @param string      $objectName
     *
     * @return array|[]
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function insertInternal(array $objects, string $objectName): array
    {
        DebugLogger::log($objectName, sprintf('Found %d %s to INSERT', $objectName, count($objects)), __CLASS__ . ':' . __FUNCTION__);

        $objectMappings = [];
        /** @var ObjectChangeDAO $object */
        foreach ($objects as $object) {
            $fields = $object->getFields();

            /* Perform validation */
            try {
                $objectModel = $this->getModel($fields);

                if (!$this->vtigerSettingProvider->getOwner()) {
                    throw new VtigerPluginException('You need to configure owner for new objects');
                }
                $objectModel->setAssignedUserId($this->vtigerSettingProvider->getOwner());

                $this->getValidator()->validate($objectModel);
            } catch (InvalidObject $e) {
                $this->logInvalidObject($object, $objectName, $e);
                continue;
            }

            try {
                $response = $this->getRepository()->create($objectModel);

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf('Created %s ID %s from %s %d', $objectName, $response->getId(), $object->getMappedObject(), $object->getMappedObjectId()),
                    __CLASS__ . ':' . __FUNCTION__
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
                DebugLogger::log(VtigerCrmIntegration::NAME, sprintf("Failed to create %s with error '%s'", $objectName, $e->getMessage()), __CLASS__ . ':' . __FUNCTION__);
            }
        }

        return $objectMappings;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     * @param string             $objectName
     *
     * @return array|mixed
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields, string $objectName): array
    {
        $fullReport = [];
        $iteration  = 0;
        // We must iterate while there is still some result left
        do {
            $reportQuery = 'SELECT id,modifiedtime,assigned_user_id,' . join(',', $mappedFields)
                . ' FROM ' . $objectName . ' WHERE modifiedtime > \'' . $fromDate->format('Y-m-d H:i:s') . '\''
                . ' LIMIT ' . $iteration * $this->getVtigerApiQueryLimit() . ',' . $this->getVtigerApiQueryLimit();

            $report = $this->getRepository()->query($reportQuery);

            ++$iteration;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $fullReport;
    }

    /**
     * @param ObjectChangeDAO $object
     * @param string          $objectName
     * @param InvalidObject   $exception
     */
    private function logInvalidObject(ObjectChangeDAO $object, string $objectName, InvalidObject $exception): void
    {
        $this->notificationHandler->writeEntry(
            new NotificationDAO($object, $exception->getMessage()),
            VtigerCrmIntegration::NAME,
            $objectName
        );

        DebugLogger::log(
            VtigerCrmIntegration::NAME,
            sprintf(
                "Invalid object %s (%s) with ID '%s' with message '%s'",
                $objectName,
                $object->getMappedObject(),
                $object->getMappedObjectId(),
                $exception->getMessage()
            ),
            __CLASS__ . ':' . __FUNCTION__
        );
    }

    /**
     * @param array $objectData
     *
     * @return BaseModel|Contact|Account|Lead
     */
    abstract protected function getModel(array $objectData);

    /**
     * @return ObjectValidatorInterface|LeadValidator|ContactValidator|AccountValidator
     */
    abstract protected function getValidator();

    /**
     * @return BaseRepository|LeadRepository|ContactRepository|AccountRepository
     */
    abstract protected function getRepository();

    /**
     * @return int
     */
    abstract protected function getVtigerApiQueryLimit(): int;
}

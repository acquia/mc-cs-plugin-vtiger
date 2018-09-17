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
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use Recurr\Exception;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class ContactDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;

    const OBJECT_NAME = 'Contacts';

    /** @var ContactRepository */
    private $objectRepository;

    /** @var ValueNormalizerInterface */
    private $valueNormalizer;

    /** @var LeadModel */
    private $mauticModel;

    /** @var VtigerSettingProvider  */
    private $settings;

    /** @var int  */
    const VTIGER_API_QUERY_LIMIT = 100;

    public function __construct(
        ContactRepository $contactRepository,
        VtigerSettingProvider $settingProvider,
        LeadModel $leadModel,
        ValueNormalizerInterface $valueNormalizer
        )
    {
        $this->objectRepository = $contactRepository;
        $this->valueNormalizer = $valueNormalizer;
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

        $mappedFields = array_merge($mappedFields,[
            'isconvertedfromlead', 'leadsource'
        ]);

        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        var_dump($updated);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                // Normalize the value from the API to what Mautic needs
                $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getType(), $value);

                $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            var_dump($objectDAO); die();

            $syncReport->addObject($objectDAO);
        }


        return $syncReport;
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
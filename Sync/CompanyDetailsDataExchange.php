<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 24.8.18
 * Time: 13:50
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeReportTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\CompanyDetailsRepository;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class CompanyDetailsDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;
    use DataExchangeReportTrait;

    const OBJECT_NAME = 'CompanyDetails';

    /** @var CompanyDetailsRepository */
    private $objectRepository;

    /** @var ValueNormalizerInterface */
    private $valueNormalizer;

    /** @var LeadModel */
    private $model;

    /** @var VtigerSettingProvider  */
    private $settings;

    public function __construct(
        CompanyDetailsRepository $companyDetailsRepository,
        VtigerSettingProvider $settingProvider,
        CompanyModel $companyModel,
        ValueNormalizerInterface $valueNormalizer
    )
    {
        $this->objectRepository = $companyDetailsRepository;
        $this->valueNormalizer = $valueNormalizer;
        $this->model = $companyModel;
        $this->settings = $settingProvider;
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
            /** @var Contact $model */
            $model = new $modelName($objectData);
            if (!$this->settings->getSyncSetting('owner')) {
                throw new InvalidConfigurationException('You need to configure owner for new objects');
            }
            $model->setAssignedUserId($this->settings->getSyncSetting('owner'));

            try {
                $response = $this->objectRepository->create($model);

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
                        "Created %s ID %s from %s %d",
                        self::OBJECT_NAME,
                        $response->getId(),
                        $object->getObject(),
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
            } catch (InvalidQueryArgumentException $e) {
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

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = []; $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->objectRepository->query('SELECT * FROM ' . self::OBJECT_NAME
                . ' LIMIT ' . ($iteration*100) . ',100');

            $iteration++;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $report;
    }
}
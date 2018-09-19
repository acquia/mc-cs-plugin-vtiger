<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
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
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class LeadDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;


    const OBJECT_NAME = 'Leads';

    /**
     * @var LeadRepository
     */
    private $objectRepository;

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

    /** @var int  */
    const VTIGER_API_QUERY_LIMIT = 100;

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
        LeadModel $leadModel,
        ValueNormalizerInterface $valueNormalizer,
        LeadValidator $objectValidator
    )
    {
        $this->objectRepository = $leadsRepository;
        $this->objectValidator = $objectValidator;
        $this->valueNormalizer  = $valueNormalizer;
        $this->model            = $leadModel;
        $this->settings         = $settingProvider;
    }

    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject, ReportDAO &$syncReport): ReportDAO
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
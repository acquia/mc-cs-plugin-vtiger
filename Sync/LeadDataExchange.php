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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

/**
 * Class LeadDataExchange
 * @package MauticPlugin\MauticVtigerCrmBundle\Sync
 */
final class LeadDataExchange implements ObjectSyncDataExchangeInterface
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

    /** @var int */
    const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * LeadDataExchange constructor.
     *
     * @param LeadRepository           $leadsRepository
     * @param VtigerSettingProvider    $settingProvider
     * @param LeadModel                $leadModel
     * @param ValueNormalizerInterface $valueNormalizer
     * @param LeadValidator            $objectValidator
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
        $this->objectValidator  = $objectValidator;
        $this->valueNormalizer  = $valueNormalizer;
        $this->model            = $leadModel;
        $this->settings         = $settingProvider;
    }

    /**
     * @param RequestObjectDAO $requestedObject
     * @param ReportDAO        $syncReport
     *
     * @return ReportDAO
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getObjectSyncReport(
        RequestObjectDAO $requestedObject,
        ReportDAO &$syncReport
    ): ReportDAO
    {

        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->objectRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(
                self::OBJECT_NAME, $object->getId(),
                new \DateTimeImmutable($object->getModifiedTime()->format('r'))
            );

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

    /**
     * @param array $objects
     *
     * @return array|mixed
     */
    public function insert(array $objects)
    {
        return [];  // We will not insert leads as we are not able to tell them
    }
}

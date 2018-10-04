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

use DateTimeImmutable;
use Exception;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as MauticPluginObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

/**
 * Class LeadDataExchange.
 */
final class LeadDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;

    const OBJECT_NAME = 'Leads';

    /** @var int */
    const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var VtigerSettingProvider
     */
    private $vtigerSettingProvider;

    /**
     * LeadDataExchange constructor.
     *
     * @param LeadRepository        $leadsRepository
     * @param VtigerSettingProvider $settingProvider
     * @param LeadModel             $leadModel
     */
    public function __construct(
        LeadRepository $leadRepository,
        VtigerSettingProvider $vtigerSettingProvider,
        LeadModel $leadModel,
        ValueNormalizerInterface $valueNormalizer,
        LeadValidator $leadValidator
    ) {
        $this->leadRepository                = $leadRepository;
        $this->objectValidator               = $leadValidator;
        $this->valueNormalizer               = $valueNormalizer;
        $this->leadModel                     = $leadModel;
        $this->vtigerSettingProvider         = $vtigerSettingProvider;
    }

    /**
     * @param MauticPluginObjectDAO $requestedObject
     * @param ReportDAO             $syncReport
     *
     * @return ReportDAO
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getObjectSyncReport(MauticPluginObjectDAO $mauticPluginObjectDAO, ReportDAO $reportDAO): ReportDAO
    {
        $fromDateTime = $mauticPluginObjectDAO->getFromDateTime();
        $mappedFields = $mauticPluginObjectDAO->getFields();
        $objectFields = $this->leadRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new DateTimeImmutable(
                $object->getModifiedTime()->format(
                'r'
            )
            ));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                // Normalize the value from the API to what Mautic needs
                $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getType(), $value);

                $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            $reportDAO->addObject($objectDAO);
        }

        return $reportDAO;
    }

    /**
     * @param array $objects
     *
     * @return mixed|void
     *
     * @throws \Exception
     */
    public function delete(array $objects)
    {
        // TODO: Implement delete() method.
        throw new Exception('Not implemented');
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

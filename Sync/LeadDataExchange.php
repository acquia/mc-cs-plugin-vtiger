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

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Mautic\IntegrationsBundle\Sync\Logger\DebugLogger;
use Mautic\IntegrationsBundle\Sync\Notification\Handler\ContactNotificationHandler;
use Mautic\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\LeadValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;

class LeadDataExchange extends GeneralDataExchange
{
    /**
     * @var string
     */
    public const OBJECT_NAME = 'Leads';

    /**
     * @var int
     */
    private const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    /**
     * @var LeadValidator
     */
    private $leadValidator;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @param VtigerSettingProvider $vtigerSettingProvider
     * @param ValueNormalizerInterface $valueNormalizer
     * @param LeadRepository $leadRepository
     * @param LeadValidator $leadValidator
     * @param ModelFactory $modelFactory
     * @param ContactNotificationHandler $notificationHandler
     */
    public function __construct(
        VtigerSettingProvider $vtigerSettingProvider,
        ValueNormalizerInterface $valueNormalizer,
        LeadRepository $leadRepository,
        LeadValidator $leadValidator,
        ModelFactory $modelFactory,
        ContactNotificationHandler $notificationHandler
    ) {
        parent::__construct($vtigerSettingProvider, $valueNormalizer, $notificationHandler);
        $this->leadRepository = $leadRepository;
        $this->leadValidator  = $leadValidator;
        $this->modelFactory   = $modelFactory;
    }

    /**
     * @param \Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function getObjectSyncReport(
        \Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject,
        ReportDAO $syncReport
    ): ReportDAO {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->leadRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields, self::OBJECT_NAME);

        /** @var Lead $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                try {
                    // Normalize the value from the API to what Mautic needs
                    $normalizedValue = $this->valueNormalizer->normalizeForMauticTyped($objectFields[$field], $value);
                    $reportFieldDAO  = new FieldDAO($field, $normalizedValue);

                    $objectDAO->addField($reportFieldDAO);
                } catch (InvalidQueryArgumentException $e) {
                    DebugLogger::log(
                        VtigerCrmIntegration::NAME,
                        sprintf('%s for %s %s', $e->getMessage(), self::OBJECT_NAME, $object->getId())
                    );
                    printf("%s for %s %s\n", $e->getIncomingMessage(), self::OBJECT_NAME, $object->getId());
                } catch (InvalidObjectValueException $e) {
                    DebugLogger::log(VtigerCrmIntegration::NAME, $e->getMessage());
                    continue(2);
                }
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    /**
     * @param array             $ids
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function update(array $ids, array $objects): array
    {
        return $this->updateInternal($ids, $objects, self::OBJECT_NAME);
    }

    /**
     * @param ObjectChangeDAO[] $objects
     *
     * @return array|ObjectMapping[]
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function insert(array $objects): array
    {
        if (!$this->vtigerSettingProvider->shouldBeMauticContactPushedAsLead()) {
            return [];
        }

        return $this->insertInternal($objects, self::OBJECT_NAME);
    }

    /**
     * @param array $objectData
     *
     * @return Lead
     * @throws InvalidQueryArgumentException
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getModel(array $objectData): Lead
    {
        $objectFields     = $this->leadRepository->describe()->getFields();
        $normalizedFields = [];

        /**
         * @var string   $key
         * @var FieldDAO $fieldDAO
         */
        foreach ($objectData as $key => $fieldDAO) {
            $normalizedFields[$key] = $this->valueNormalizer->normalizeForVtiger($objectFields[$fieldDAO->getName()], $fieldDAO);
        }

        return $this->modelFactory->createLead($normalizedFields);
    }

    /**
     * @return LeadValidator
     */
    protected function getValidator(): LeadValidator
    {
        return $this->leadValidator;
    }

    /**
     * @return LeadRepository
     */
    protected function getRepository(): LeadRepository
    {
        return $this->leadRepository;
    }

    /**
     * @return int
     */
    protected function getVtigerApiQueryLimit(): int
    {
        return self::VTIGER_API_QUERY_LIMIT;
    }
}

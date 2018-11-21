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

use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\Notification\Handler\CompanyNotificationHandler;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\AccountValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;

/**
 * This synchronizes data between vTiger Organization named on API as Account and in Mautic named as Company
 */
class AccountDataExchange extends GeneralDataExchange
{
    /**
     * @var string
     */
    public const OBJECT_NAME = 'Accounts';

    /**
     * @var int
     */
    private const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var AccountValidator
     */
    private $accountValidator;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @param VtigerSettingProvider $vtigerSettingProvider
     * @param ValueNormalizerInterface $valueNormalizer
     * @param AccountRepository $accountRepository
     * @param AccountValidator $accountValidator
     * @param ModelFactory $modelFactory
     * @param CompanyNotificationHandler $notificationHandler
     */
    public function __construct(
        VtigerSettingProvider $vtigerSettingProvider,
        ValueNormalizerInterface $valueNormalizer,
        AccountRepository $accountRepository,
        AccountValidator $accountValidator,
        ModelFactory $modelFactory,
        CompanyNotificationHandler $notificationHandler
    ){
        parent::__construct($vtigerSettingProvider, $valueNormalizer, $notificationHandler);
        $this->accountRepository = $accountRepository;
        $this->accountValidator  = $accountValidator;
        $this->modelFactory      = $modelFactory;
    }

    /**
     * @param \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO
     *
     * @throws \Exception
     */
    public function getObjectSyncReport(
        \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject,
        ReportDAO $syncReport
    ): ReportDAO {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->accountRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields, self::OBJECT_NAME);

        /** @var BaseModel $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                try {
                    if (!isset($objectFields[$field])) {
                        // If the present value is not described it should be processed as string
                        $normalizedValue = $this->valueNormalizer->normalizeForMautic(NormalizedValueDAO::STRING_TYPE, $value);
                    } else {
                        // Normalize the value from the API to what Mautic needs
                        $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getTypeName(), $value);
                    }

                    $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                    $objectDAO->addField($reportFieldDAO);
                }
                catch (InvalidQueryArgumentException $e) {
                    DebugLogger::log(VtigerCrmIntegration::NAME,
                        sprintf('%s for %s %s', $e->getMessage(), self::OBJECT_NAME, $object->getId())
                    );
                    printf("%s for %s %s\n", $e->getIncomingMessage(), self::OBJECT_NAME, $object->getId());
                }
                catch (InvalidObjectValueException $e) {
                    DebugLogger::log(VtigerCrmIntegration::NAME, $e->getMessage());
                    continue(2);
                }
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     * @param string             $objectName
     *
     * @return array|mixed
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields, string $objectName): array
    {
        $fullReport = [];
        $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->accountRepository->query('SELECT * FROM '.$objectName
                .' LIMIT '.($iteration * $this->getVtigerApiQueryLimit()).','.$this->getVtigerApiQueryLimit());

            ++$iteration;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $fullReport;
    }

    /**
     * @param array $ids
     * @param UpdatedObjectMappingDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function update(array $ids, array $objects): array
    {
        return $this->updateInternal($ids, $objects, self::OBJECT_NAME);
    }

    /**
     * @param Account[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function insert(array $objects): array
    {
        return $this->insertInternal($objects, self::OBJECT_NAME);
    }

    /**
     * @param array $objectData
     *
     * @return Account
     * @throws VtigerPluginException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getModel(array $objectData): Account
    {
        $objectFields = $this->accountRepository->describe()->getFields();
        $normalizedFields = [];

        /**
         * @var string   $key
         * @var FieldDAO $fieldDAO
         */
        foreach ($objectData as $key => $fieldDAO) {
            $normalizedFields[$key] = $this->valueNormalizer->normalizeForVtiger($objectFields[$fieldDAO->getName()], $fieldDAO);
        }

        return $this->modelFactory->createAccount($normalizedFields);
    }

    /**
     * @return AccountValidator
     */
    protected function getValidator(): AccountValidator
    {
        return $this->accountValidator;
    }

    /**
     * @return AccountRepository
     */
    protected function getRepository(): AccountRepository
    {
        return $this->accountRepository;
    }

    /**
     * @return int
     */
    protected function getVtigerApiQueryLimit(): int
    {
        return self::VTIGER_API_QUERY_LIMIT;
    }
}

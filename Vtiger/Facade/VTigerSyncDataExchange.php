<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Facade;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Operation\Sync\SyncOperationInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync\SyncServiceInterface;

/**
 * Class VTigerSyncDataExchange
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Facade
 */
final class VTigerSyncDataExchange implements IntegrationSyncDataExchangeInterface
{
    const ACCOUNT = 'account';
    const CONTACT = 'contact';

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    /**
     * VTigerSyncDataExchange constructor.
     *
     * @param AccountRepository    $accountRepository
     * @param SyncServiceInterface $syncService
     */
    public function __construct(AccountRepository $accountRepository, SyncServiceInterface $syncService)
    {
        $this->accountRepository = $accountRepository;
        $this->syncService = $syncService;
    }

    /**
     * @return string
     */
    public function getIntegration()
    {
        return 'vTiger';
    }

    /**
     * @param IntegrationMappingManual $integrationMapping
     * @param int|null                 $fromTimestamp
     *
     * @return ReportDAO
     */
    public function getSyncReport(IntegrationMappingManual $integrationMapping, int $fromTimestamp = null): ReportDAO
    {
        $objectsMapping = $integrationMapping->getObjects();
        $syncLog = $this->generalRepository->getSyncLog($fromTimestamp);
        $syncReport = new ReportDAO();
        foreach($objectsMapping as $objectMapping) {
            $object = $objectMapping->getObject();
            $updatedIds = $syncLog->getUpdatedIds($object);
            $repository = $this->getSyncOperationRepository($object);
            $fields = $objectMapping->getFields();
            /** @var BaseModel[] $currentObjects */
            $currentObjects = $repository->findBy(['id' => $updatedIds], $fields);
            foreach($currentObjects as $currentObject) {
                $objectChange = new ObjectChangeDAO();
                foreach($fields as $field) {
                    $fieldValue = $currentObject->{'get'. ucwords($field)}();
                    $objectChange->addField(new FieldDAO($field, $fieldValue));
                }
                $objectChange->setChangeTimestamp($syncLog->getObjectChangeTimestamp($object, $currentObject->getId()));
                $syncReport->addObject($objectChange);
            }
        }
        return $syncReport;
    }

    public function executeSyncOrder(SyncOrderDAO $syncOrderDAO)
    {
        $objects = $syncOrderDAO->getObjects();

        foreach($objects as $object) {
            $this->syncService->syncObject($object);
        }
    }

    /**
     * @param string $object
     *
     * @return SyncOperationInterface
     *
     * @throws \InvalidArgumentException
     */
    private function getSyncOperationRepository(string $object): SyncOperationInterface
    {
        switch($object) {
            case self::ACCOUNT:
                return $this->accountRepository;
            case self::CONTACT:
                return $this->contactRepository;
            default:
                throw new \InvalidArgumentException();
        }
    }
}

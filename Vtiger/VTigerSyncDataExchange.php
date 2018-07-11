<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;

/**
 * Class VTigerSyncDataExchange
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger
 */
final class VTigerSyncDataExchange implements IntegrationSyncDataExchangeInterface
{
    const ACCOUNT = 'account';

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * VTigerSyncDataExchange constructor.
     *
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
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
     */
    public function getSyncReport(IntegrationMappingManual $integrationMapping, $fromTimestamp = null)
    {

    }

    public function executeSyncOrder(SyncOrderDAO $syncOrderDAO)
    {
        $objectsChanges = $syncOrderDAO->getObjectsChanges();

        $updateList = [];
        foreach($objectsChanges as $objectChange) {
            $object = $objectChange->getObject();
            if(!array_key_exists($object, $updateList)) {
                $updateList[$object] = [];
            }
            $objectId = $objectChange->getObjectId();
            $updateList[$object][$objectId] = $objectChange;
        }
        foreach($updateList as $object => $objectsChanges) {
            switch($object) {
                case self::ACCOUNT:
                    $this->executeObjectSyncOrder($this->accountRepository, $objectsChanges);
                case self::CONTACT:
                    $this->executeAccountSyncOrder($objectsChanges);
            }
        }
    }

    /**
     * @param array $objectsChanges
     */
    private function executeObjectSyncOrder($repository, array $objectsChanges)
    {
        $objectsIds = array_keys($objectsChanges);
        $objects = $repository->findBy(['id' => $objectsIds]);
        foreach($objects as $object) {
            $objectChange = $objectsChanges[$object->getId()];
            $objectChangeFields = $objectChange->getFields();
            foreach($objectChangeFields as $objectChangeField) {
                $object->{'set' . ucwords($objectChangeField->getName())}($objectChangeField->getValue());
            }
            $repository->update($object);
        }
    }
}

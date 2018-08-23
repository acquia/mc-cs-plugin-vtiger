<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Facade\Sync;

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
    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    /**
     * VTigerSyncDataExchange constructor.
     *
     * @param SyncServiceInterface $syncService
     */
    public function __construct(SyncServiceInterface $syncService)
    {
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
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        return $this->syncService->getReport($requestDAO);
    }

    /**
     * @param OrderDAO $orderDAO
     */
    public function executeSyncOrder(OrderDAO $orderDAO)
    {
        $objects = $orderDAO->getObjects();
        foreach($objects as $object) {
            $this->syncService->syncObject($object);
        }
    }
}

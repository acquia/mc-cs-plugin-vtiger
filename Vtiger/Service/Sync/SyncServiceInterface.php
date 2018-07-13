<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync;

/**
 * Interface SyncServiceInterface
 */
interface SyncServiceInterface
{
    /**
     * @param IntegrationMappingManual $integrationMappingManual
     * @param int|null                 $fromTimestamp
     *
     * @return ReportDAO
     */
    public function getSyncReport(IntegrationMappingManual $integrationMappingManual, int $fromTimestamp = null) : ReportDAO;

    /**
     * @param ObjectDAO $objectDAO
     */
    public function syncObject(ObjectDAO $objectDAO);
}

<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync;

/**
 * Interface SyncServiceInterface
 */
interface SyncServiceInterface
{
    /**
     * @param string $object
     * @param int[]  $ids
     * @param int[]  $columns
     *
     * @return ObjectDAO[]
     */
    public function getSyncObjects(string $object, array $ids, array $columns): array;

    /**
     * @param ObjectDAO $objectDAO
     */
    public function syncObject(ObjectDAO $objectDAO);

    public function getRepositoryByObject();
}

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

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO;

interface ObjectSyncDataExchangeInterface
{
    /**
     * @param ObjectDAO $requestedObject
     * @param ReportDAO $syncReport
     *
     * @return ReportDAO
     */
    public function getObjectSyncReport(ObjectDAO $requestedObject, ReportDAO $syncReport): ReportDAO;

    /**
     * @param array $ids
     * @param array $objects
     *
     * @return mixed
     */
    public function update(array $ids, array $objects);

    /**
     * @param array $objects
     *
     * @return array|ObjectChangeDAO[]
     */
    public function insert(array $objects): array;
}

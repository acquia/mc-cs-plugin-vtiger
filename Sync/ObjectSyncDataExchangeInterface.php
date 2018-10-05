<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     5.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO;

interface ObjectSyncDataExchangeInterface
{
    /**
     * @param ObjectDAO $objectDAO
     * @param ReportDAO $reportDAO
     *
     * @return mixed
     */
    public function getObjectSyncReport(ObjectDAO $objectDAO, ReportDAO $reportDAO);

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
     * @return mixed
     */
    public function insert(array $objects);

    /**
     * @param array $objects
     *
     * @return mixed
     */
    public function delete(array $objects);
}

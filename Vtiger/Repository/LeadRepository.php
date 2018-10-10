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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class LeadRepository
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
class LeadRepository extends BaseRepository
{
    use RepositoryHelper;

    /**
     * @param Lead $module
     *
     * @return Lead
     */
    public function create(Lead $module): Lead
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Lead
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Lead
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }
}

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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class LeadRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Lead $lead): Lead
    {
        return $this->createUnified($lead);
    }

    public function retrieve(string $id): Lead
    {
        return $this->findOneBy(['id'=>$id]);
    }

    public function delete(ModuleInterface $module): void
    {
        // TODO: Implement delete() method.
    }
}

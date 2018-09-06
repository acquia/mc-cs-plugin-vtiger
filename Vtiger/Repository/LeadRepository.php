<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class LeadRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Lead $module): Lead
    {
        return $this->createUnified($module);
    }

    public function retrieve(string $id): Lead
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }

    public function delete(ModuleInterface $module)
    {
        // TODO: Implement delete() method.
    }
}
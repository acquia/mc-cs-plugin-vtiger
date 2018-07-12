<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class AccountRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Account $module): Account
    {
        return $this->createUnified($module);
    }

    public function retrieve(string $id): Account
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }
}
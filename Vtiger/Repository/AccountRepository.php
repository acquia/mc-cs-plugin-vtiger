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

    public function getByContactId(string $contactId) {
        return $this->findBy(['contact_id' => $contactId]);
    }

    public function findBy($where = [], $columns = '*')
    {
        if (!count($where)) {
            $columnsString = is_array($columns) ? join('|', $columns) : $columns;
            $cacheKey = 'vtigercrm_acccounts_' . sha1($columnsString);
            $cacheItem = $this->cacheProvider->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        //  We will cache only queries for complete list of accounts
        $result = $this->findByInternal($where, $columns);
        $cacheItem->set($result);

        $this->cacheProvider->save($cacheItem);
        return $result;
    }
}
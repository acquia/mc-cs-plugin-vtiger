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

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class AccountRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Account $account): Account
    {
        return $this->createUnified($account);
    }

    public function retrieve(string $id): Account
    {
        return $this->findOneBy(['id'=>$id]);
    }

    public function getByContactId(string $contactId)
    {
        return $this->findBy(['contact_id' => $contactId]);
    }

    public function findBy($where = [], $columns = '*')
    {
        if (!count($where)) {
            $columnsString = is_array($columns) ? join('|', $columns) : $columns;
            $cacheKey      = 'vtigercrm_acccounts_'.sha1($columnsString);
            $cacheItem     = $this->cacheProvider->getItem($cacheKey);
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

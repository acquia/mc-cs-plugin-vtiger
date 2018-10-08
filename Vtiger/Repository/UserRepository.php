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

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class UserRepository
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
class UserRepository extends BaseRepository
{
    use RepositoryHelper;

    /**
     * @param User $module
     *
     * @return User
     */
    public function create(User $module): User
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return User
     */
    public function retrieve(string $id): User
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array|User[]|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function findBy($where = [], $columns = '*')
    {
        if (!count($where)) {
            $columnsString = is_array($columns) ? join('|', $columns) : $columns;
            $cacheKey = 'vtigercrm_users_' . sha1($columnsString);
            $cacheItem = $this->cacheProvider->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
            //  We will cache only queries for complete list of accounts
            $result = $this->findByInternal($where, $columns);
            $cacheItem->set($result);

            $this->cacheProvider->save($cacheItem);
        } else {
            $result = $this->findByInternal($where, $columns);
        }

        return $result;
    }
}

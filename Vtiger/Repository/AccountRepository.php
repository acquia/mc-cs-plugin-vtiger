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

/**
 * Class AccountRepository.
 */
class AccountRepository extends BaseRepository
{
    use RepositoryHelper;

    /**
     * @param Account $module
     *
     * @return Account
     */
    public function create(Account $module): Account
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Account
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Account
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }

    /**
     * @param string $contactId
     *
     * @return array
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getByContactId(string $contactId): array
    {
        return $this->findBy(['contact_id' => $contactId]);
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array|Account[]|mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
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

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return 'Accounts';
    }
}

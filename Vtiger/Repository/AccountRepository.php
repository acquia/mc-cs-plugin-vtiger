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

use MauticPlugin\MauticVtigerCrmBundle\Enum\CacheEnum;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\CachedItemNotFoundException;
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
        return $this->findOneBy(['id' =>$id]);
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
     * @return array|Account[]
     */
    public function findBy($where = [], $columns = '*'): array
    {
        if (count($where)) {
            return $this->findByInternal($where, $columns);
        }

        $columnsString = is_array($columns) ? join('|', $columns) : $columns;
        $key           = 'vtigercrm_acccounts_'.sha1($columnsString);
        try {
            return $this->fieldCache->getAccountQuery($key);
        } catch (CachedItemNotFoundException $e) {
        }

        $result = $this->findByInternal($where, $columns);
        $this->fieldCache->setAccountQuery($key, $result);

        return $result;
    }

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return CacheEnum::ACCOUNT;
    }
}

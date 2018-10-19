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

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\CachedItemNotFoundException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class UserRepository.
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
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): User
    {
        return $this->findOneBy(['id' =>$id]);
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array|User[]|mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function findBy($where = [], $columns = '*')
    {
        if (count($where)) {
            return $this->findByInternal($where, $columns);
        }

        $columnsString = is_array($columns) ? join('|', $columns) : $columns;
        $key      = 'vtigercrm_users_'.sha1($columnsString);

        try {
            return $this->fieldCache->getModuleInfo($key);
        } catch (CachedItemNotFoundException $e) {
        }

        //  We will cache only queries for complete list of accounts
        $result = $this->findByInternal($where, $columns);
        $this->fieldCache->setUserQuery($key, $result);

        return $result;
    }

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return 'Users';
    }
}

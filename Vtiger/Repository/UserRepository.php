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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction\FieldDirectionInterface;

/**
 * Class UserRepository.
 */
class UserRepository extends BaseRepository
{
    /**
     * @param User $module
     *
     * @return User
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
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
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function retrieve(string $id): User
    {
        return $this->findOneBy(['id' =>$id]);
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array|User[]
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function findBy($where = [], $columns = '*'): array
    {
        if (count($where)) {
            return $this->findByInternal($where, $columns);
        }

        $columnsString = is_array($columns) ? join('|', $columns) : $columns;
        $key      = 'vtigercrm_users_'.sha1($columnsString);

        try {
            return $this->fieldCache->getUserQuery($key);
        } catch (CachedItemNotFoundException $e) {
        }

        $result = $this->findByInternal($where, $columns);
        $this->fieldCache->setUserQuery($key, $result);

        return $result;
    }

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return CacheEnum::USER;
    }

    /**
     * @param array $objectData
     *
     * @return User
     */
    protected function getModel(array $objectData): User
    {
        return $this->modelFactory->createUser($objectData);
    }

    /**
     * @return FieldDirectionInterface
     *
     * @throws \Exception
     */
    protected function getFieldDirection(): FieldDirectionInterface
    {
        throw new \Exception('User has no Fields');
    }
}

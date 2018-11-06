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

use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\CachedItemNotFoundException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;

/**
 * Class BaseRepository.
 */
abstract class BaseRepository
{
    /** @var Connection */
    protected $connection;

    /**
     * @var FieldCache
     */
    protected $fieldCache;

    /**
     * @var ModelFactory
     */
    protected $modelFactory;

    /**
     * @param Connection   $connection
     * @param FieldCache   $fieldCache
     * @param ModelFactory $modelFactory
     */
    public function __construct(Connection $connection, FieldCache $fieldCache, ModelFactory $modelFactory)
    {
        $this->connection   = $connection;
        $this->fieldCache   = $fieldCache;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @return ModuleInfo
     *
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function describe(): ModuleInfo
    {
        $key = $this->getModuleFromRepositoryName();
        try {
            return $this->fieldCache->getModuleInfo($key);
        } catch (CachedItemNotFoundException $e) {
        }

        $moduleInfo = new ModuleInfo(
            $this->connection->get('describe', ['elementType' => $key])
        );
        $this->fieldCache->setModuleInfo($key, $moduleInfo);

        return $moduleInfo;
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function findBy($where = [], $columns = '*'): array
    {
        return $this->findByInternal($where, $columns);
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return mixed|null
     *
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function findOneBy($where = [], $columns = '*')
    {
        $findResult = $this->findBy($where, $columns);

        if (!count($findResult)) {
            return null;
        }

        if (count($findResult) > 1) {
            throw new InvalidQueryArgumentException('Invalid query. Query returned more than one result.');
        }

        return array_shift($findResult);
    }

    /**
     * @param BaseModel $module
     *
     * @return BaseModel
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function update(BaseModel $module): BaseModel
    {
        DebugLogger::log(VtigerCrmIntegration::NAME, 'Updating '.$this->getModuleFromRepositoryName().' '.$module->getId());
        $response = $this->connection->post('update', ['element' => json_encode($module->dehydrate())]);

        return $this->getModel((array) $response);
    }

    /**
     * @param $query
     *
     * @return array
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function query($query): array
    {
        $result = $this->connection->get('query', ['query' => $query]);

        $return = [];

        foreach ($result as $key => $moduleObject) {
            $return[] = $this->getModel((array) $moduleObject);
        }

        return $return;
    }

    /**
     * @return array|ModuleFieldInfo[]
     *
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getMappableFields(): array
    {
        return $this->getEditableFields();
    }

    /**
     * @return array|ModuleFieldInfo[]
     *
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    protected function getEditableFields(): array
    {
        /** @var ModuleInfo $moduleFields */
        $moduleFields = $this->describe()->getFields();

        $fields = [];
        foreach ($moduleFields as $fieldInfo) {
            if ($fieldInfo->isEditable()) {
                $fields[$fieldInfo->getName()] = $fieldInfo;
            }
        }

        return $fields;
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    protected function findByInternal($where = [], $columns = '*'): array
    {
        $moduleName = $this->getModuleFromRepositoryName();

        $columns = is_array($columns) ? join(', ', $columns) : $columns;

        $query = 'select '.$columns.' from '.$moduleName;
        if (count($where)) {
            $whereEscaped = [];
            foreach ($where as $key => $value) {
                $whereEscaped[$key] = sprintf("%s='%s'",
                                              $key,
                                              htmlentities($value)
                );
            }
            $query .= ' where '.join(' and ', $whereEscaped);
        }

        $query .= ';';

        $result = $this->connection->get('query', ['query' => $query]);
        $return = [];

        foreach ($result as $key => $moduleObject) {
            $return[] = $this->getModel((array) $moduleObject);
        }

        return $return;
    }

    /**
     * @param BaseModel $module
     *
     * @return BaseModel|Account|Contact|Lead|Event|User
     * @throws InvalidQueryArgumentException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    protected function createUnified(BaseModel $module): BaseModel
    {
        $response = $this->connection->post('create', ['element' => json_encode($module->dehydrate()), 'elementType' => $this->getModuleFromRepositoryName()]);

        return $this->getModel((array) $response);
    }

    /**
     * @return string
     */
    abstract public function getModuleFromRepositoryName(): string;

    /**
     * @param array $objectData
     *
     * @return BaseModel|Contact|Account|Lead
     */
    abstract protected function getModel(array $objectData);
}

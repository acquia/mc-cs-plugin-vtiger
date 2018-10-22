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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper;

use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\EventFactory;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;

trait RepositoryHelper
{
    public function findBy($where = [], $columns = '*'): array
    {
        return $this->findByInternal($where, $columns);
    }

    /**
     * @todo this is useless you cannot use operators, needa complete rewrite
     *
     * @param array  $where
     * @param string $columns
     *
     * @return array
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
            $return[] = ModelFactory::getModel($moduleName, (array) $moduleObject);
        }

        return $return;
    }

    /**
     * @param array  $where
     * @param string $columns
     *
     * @return mixed|null
     *
     * @throws InvalidQueryArgumentException
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
     * @return BaseModel|Account|Contact|Event|EventFactory|Lead|User
     */
    private function createUnified(BaseModel $module): BaseModel
    {
        $response = $this->connection->post('create', ['element' => json_encode($module->dehydrate()), 'elementType' => $this->getModuleFromRepositoryName()]);

        return ModelFactory::getModel($this->getModuleFromRepositoryName(), (array) $response);
    }

    /**
     * @param BaseModel $module
     *
     * @return BaseModel
     */
    public function update(BaseModel $module): BaseModel
    {
        DebugLogger::log(VtigerCrmIntegration::NAME, 'Updating '.$this->getModuleFromRepositoryName().' '.$module->getId());
        $response = $this->connection->post('update', ['element' => json_encode($module->dehydrate())]);

        return ModelFactory::getModel($this->getModuleFromRepositoryName(), (array) $response);
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function query($query): array
    {
        $moduleName = $this->getModuleFromRepositoryName();

        $result = $this->connection->get('query', ['query' => $query]);

        $return = [];

        foreach ($result as $key => $moduleObject) {
            $return[] = ModelFactory::getModel($moduleName, (array) $moduleObject);
        }

        return $return;
    }

    /**
     * @return array
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMappableFields(): array
    {
        return $this->getEditableFields();
    }

    /**
     * @return array
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEditableFields(): array
    {
        /** @var ModuleInfo $moduleFields */
        $moduleFields = $this->describe()->getFields();

        $fields = [];
        /** @var ModuleFieldInfo $fieldInfo */
        foreach ($moduleFields as $fieldInfo) {
            if ($fieldInfo->isEditable()) {
                $fields[] = $fieldInfo;
            }
        }

        return $fields;
    }
}

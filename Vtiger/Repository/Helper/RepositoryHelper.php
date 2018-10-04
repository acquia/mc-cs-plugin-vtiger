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

use MauticPlugin\MauticCacheBundle\Cache\CacheProvider;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\SyncReport;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

/**
 * Trait RepositoryHelper.
 */
trait RepositoryHelper
{
    /**
     * @var array [ModuleInfo]
     */
    private $modulesInfo = [];

    public function findBy($where = [], $columns = '*')
    {
        return $this->findByInternal($where, $columns);
    }

    /**
     * todo same problem as above.
     *
     * @param array  $where
     * @param string $columns
     *
     * @return BaseModel|null
     */
    public function findOneBy(array $where = [], string $columns = '*'): ?BaseModel
    {
        $findResult = $this->findBy($where, $columns);

        if (!count($findResult)) {
            return null;
        }

        if (count($findResult) > 1) {
            throw new InvalidRequestException('Invalid query. Query returned more than one result.');
        }

        return array_shift($findResult);
    }

    /**
     * @return mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function describe()
    {
        $cacheKey = BaseRepository::CACHE_NAMESPACE.'_'.$this->getModuleFromRepositoryName();

        /** @var CacheProvider $cache */
        $cache = $this->cacheProvider;

        $cachedItem = $cache->getItem($cacheKey);
        if ($cachedItem->isHit()) {
            return $cachedItem->get();
        }

        $cachedItem->tag(['vtigercrm', 'vtigercrm_repository']);
        $cachedItem->expiresAfter(60 * 60 * 24 * 7);  // Expire after a week

        if (!isset($this->modulesInfo[$this->getModuleFromRepositoryName()])) {
            $this->modulesInfo[$this->getModuleFromRepositoryName()] = new ModuleInfo(
                $this->connection->get('describe', ['elementType' => $this->getModuleFromRepositoryName()])
            );
        }

        $cachedItem->set($this->modulesInfo[$this->getModuleFromRepositoryName()]);
        $cache->save($cachedItem);

        return $cachedItem->get();
    }

    /**
     * @param BaseModel $module
     *
     * @return BaseModel
     */
    public function update(BaseModel $baseModel): BaseModel
    {
        $response = $this->connection->post('update', ['element' => json_encode($baseModel->dehydrate())]);

        $className = self::$moduleClassMapping[$this->getModuleFromRepositoryName()];

        return new $className((array) $response);
    }

    /**
     * todo complete refactoring, object needs to be specified at one place only, not multiple.
     *
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        $className = get_class($this);

        $parts = explode('\\', $className);

        return rtrim(str_replace('Repository', '', array_pop($parts)), 's').'s';
    }

    /**
     * @param $query
     *
     * @return array
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function query($query): array
    {
        $moduleName = $this->getModuleFromRepositoryName();
        $className  = self::$moduleClassMapping[$moduleName];

        $result = $this->connection->get('query', ['query' => $query]);

        $return = [];

        foreach ($result as $key=>$moduleObject) {
            $return[] = new $className((array) $moduleObject);
        }

        return $return;
    }

    /**
     * @param $id string Vtiger ID
     *
     * @return mixed
     */
    public function delete(string $id)
    {
        return $this->connection->post('delete', ['id' =>  (string) $id]);
    }

    /**
     * @see
     * ```sync(modifiedTime: Timestamp, elementType: String, syncType: String):SyncResult```
     */
    public function sync(int $modifiedTime, $syncType = BaseRepository::SYNC_APPLICATION): void
    {
        $moduleName = $this->getModuleFromRepositoryName();

        $query = [
            'modifiedtime' => (new \DateTime())->getTimestamp(),
            'elementType'  => rtrim($moduleName, 's').'s',
            //'syncType' => 'user'
        ];

        var_dump($query);
        /** @var Connection $this->connection */
        $response = $this->connection->query('sync', $query);

        var_dump($response);
        $report = new SyncReport($response, $moduleName);

        var_dump($report);
        var_dump($response);
        die();
    }

    /**
     * @todo this is useless you cannot use operators, needa complete rewrite
     *
     * @param array  $where
     * @param string $columns
     *
     * @return array|Account[]
     */
    protected function findByInternal(array $where = [], string $columns = '*'): array
    {
        $moduleName = $this->getModuleFromRepositoryName();
        $className  = self::$moduleClassMapping[$moduleName];

        $columns = is_array($columns) ? join(', ', $columns) : $columns;

        $query = 'select '.$columns.' from '.$moduleName;
        if (count($where)) {
            foreach ($where as $key => $value) {
                $whereEscaped[$key] = sprintf("%s='%s'", $key, htmlentities($value));
            }
            $query .= ' where '.join(' and ', $whereEscaped);
        }

        $query .= ';';

        $result = $this->connection->get('query', ['query' => $query]);
        $return = [];

        foreach ($result as $key=>$moduleObject) {
            $return[] = new $className((array) $moduleObject);
        }

        return $return;
    }

    /**
     * @param BaseModel $baseModel
     *
     * @return BaseModel
     */
    private function createUnified(BaseModel $baseModel): BaseModel
    {
        $response = $this->connection->post(
            'create',
            [
                'element'     => json_encode($baseModel->dehydrate()),
                'elementType' => $this->getModuleFromRepositoryName(),
            ]
        );

        $className = self::$moduleClassMapping[$this->getModuleFromRepositoryName()];

        return new $className((array) $response);
    }
}

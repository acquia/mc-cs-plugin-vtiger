<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper;

use MauticPlugin\MauticCacheBundle\Cache\CacheProvider;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\SyncReport;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

/**
 * Trait RepositoryHelper
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper
 */
trait RepositoryHelper
{
    /** @var array[ModuleInfo] */
    private $modulesInfo;

    public function findBy($where = [], $columns = '*') {
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
    protected function findByInternal($where = [], $columns = '*')
    {
        $moduleName = $this->getModuleFromRepositoryName();
        $className = self::$moduleClassMapping[$moduleName];

        $columns = is_array($columns) ? join(', ', $columns) : $columns;

        $query = "select " . $columns . " from " . $moduleName;
        if (count($where)) {
            foreach ($where as $key => $value) {
                $whereEscaped[$key] = sprintf("%s='%s'",
                    $key,
                    htmlentities($value)
                );
            }
            $query .= " where " . join(' and ', $whereEscaped);
        }

        $query .= ";";

        $result = $this->connection->get('query', ['query' => $query]);
        $return = [];

        foreach ($result as $key=>$moduleObject) {
            $return[] = new $className((array) $moduleObject);
        }

        return $return;
    }

    /**
     * todo same problem as above
     *
     * @param array  $where
     * @param string $columns
     *
     * @return BaseModel|null|Account|Contact|Event|Lead|User
     */
    public function findOneBy($where = [], $columns = '*')
    {
        $findResult = $this->findBy($where, $columns);

        if (!count($findResult)) {
            return null;
        }

        if (count($findResult)>1) {
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
        $cacheKey = BaseRepository::CACHE_NAMESPACE . "_" . $this->getModuleFromRepositoryName();

        /** @var CacheProvider $cache */
        $cache = $this->cacheProvider;

        $cachedItem = $cache->getItem($cacheKey);
        if($cachedItem->isHit()) {
            return $cachedItem->get();
        }

        $cachedItem->tag(['vtigercrm','vtigercrm_repository']);
        $cachedItem->expiresAfter(60*60*24*7);  // Expire after a week

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
    private function createUnified($module): BaseModel
    {
        $response = $this->connection->post('create', ['element' => json_encode($module->dehydrate()), 'elementType' => $this->getModuleFromRepositoryName()]);

        $className = self::$moduleClassMapping[$this->getModuleFromRepositoryName()];
        $createdModule = new $className((array)$response);

        return $createdModule;
    }

    /**
     * @param BaseModel $module
     *
     * @return BaseModel
     */
    public function update(BaseModel $module): BaseModel
    {
        $response = $this->connection->post('update', ['element' => json_encode($module->dehydrate())]);

        $className = self::$moduleClassMapping[$this->getModuleFromRepositoryName()];
        $createdModule = new $className((array)$response);

        return $createdModule;
    }


    /**
     * todo complete refactoring, object needs to be specified at one place only, not multiple
     * @return string
     */
    public function getModuleFromRepositoryName() {
        $className = get_class($this);

        $parts = explode('\\', $className);
        $modelName = rtrim(str_replace('Repository', '', array_pop($parts)),'s') . "s";
        return $modelName;
    }

    /**
     * @param $query
     *
     * @return array
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function query($query) {
        $moduleName = $this->getModuleFromRepositoryName();
        $className = self::$moduleClassMapping[$moduleName];

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
    public function delete(string $id) {
        $response = $this->connection->post('delete', ['id' =>  (string) $id]);
        return $response;
    }

    /**
     * @see
     * ```sync(modifiedTime: Timestamp, elementType: String, syncType: String):SyncResult```
     */
    public function sync(int $modifiedTime, $syncType = BaseRepository::SYNC_APPLICATION) {
        throw new \Exception('Remote API support for sync is not working, thus not implemented. If it starts working we should use it at least for deleted.');
        $moduleName = $this->getModuleFromRepositoryName();

        $query = [
            'modifiedtime' => (new \DateTime())->getTimestamp(),
            'elementType' => rtrim($moduleName,'s').'s',
            //'syncType' => 'user'
        ];

        /** @var Connection $this->connection */
        $response = $this->connection->query('sync', $query);

        $report = new SyncReport($response, $moduleName);
    }
}

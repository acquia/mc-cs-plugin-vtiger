<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 11.7.18
 * Time: 10:41
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\SyncReport;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

/**
 * Trait RepositoryHelper
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper
 */
trait RepositoryHelper
{
    /**
     * @param array  $where
     * @param string $columns
     *
     * @return array
     */
    public function findBy($where = [], $columns = '*')
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
     * @param array  $where
     * @param string $columns
     *
     * @return BaseModel|null
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
     * @return ModuleInfo
     */
    public function describe()
    {
        $info = $this->connection->get('describe', ['elementType' => $this->getModuleFromRepositoryName()]);

        return new ModuleInfo($info);
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
     * @return string
     */
    private function getModuleFromRepositoryName() {
        $className = get_class($this);

        if (!preg_match("/.*\\\\([A-Z]{1}[a-z]+)Repository/", $className, $matches)) {
            throw new \InvalidArgumentException('Repositories must conform to certain naming conventions. Failed to parse module name out of ' . $className);
        }

        return $matches[1] . 's';
    }

    /**
     * @param $query
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function query($query) {
        $response =  $this->connection->get('query', ['query' => $query]);
        return $response;
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
        $moduleName = $this->getModuleFromRepositoryName();

        /** @var Connection $this->connection */
        $response = $this->connection->get('sync', [
            'modifiedTime' => intval($modifiedTime),
            'elementType' => $moduleName,
            'syncType'  => $syncType
        ]);

        $report = new SyncReport($response, $moduleName);

        var_dump($report);
        var_dump($response);
        die();
    }
}
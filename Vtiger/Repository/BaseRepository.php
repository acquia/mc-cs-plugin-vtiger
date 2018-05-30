<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;


use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerInvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Module\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;

abstract class BaseRepository implements RepositoryInterface
{
    /** @var Connection */
    protected $connection;

    /** @var string */
    protected $moduleName;

    /**
     * BaseRepository constructor.
     *
     * @param Connection $connection
     * @param $moduleName
     */
    public function __construct(Connection $connection, $moduleName)
    {
        $this->connection = $connection;
        $this->moduleName = $moduleName;
    }

    /**
     * @param string $columns
     * @param array $where
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerSessionException
     */
    public function findBy($where = [], $columns = '*')
    {
        $columns = is_array($columns) ? join(', ', $columns) : $columns;

        $query = "select " . $columns . " from " . $this->moduleName;
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

        return $this->connection->get('query', ['query' => $query]);
    }

    /**
     * @param string $columns
     * @param array $where
     * @return \Psr\Http\Message\ResponseInterface|bool
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerSessionException
     */
    public function findOneBy($where = [], $columns = '*')
    {
        $findResult = $this->find($where, $columns);

        if (!count($findResult)) {
            return false;
        }

        if (count($findResult)>1) {
            throw new VtigerInvalidRequestException('Invalid query. Query returned more than one result.');
        }

        return array_shift($findResult);
    }

    /**
     * @todo add caching
     *
     * @return ModuleInfo
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerSessionException
     */
    public function describe()
    {
        $info = $this->connection->get('describe', ['elementType' => $this->moduleName]);

        return new ModuleInfo($info);
    }
}
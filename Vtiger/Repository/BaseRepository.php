<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;


use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;

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
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
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
     * @param array  $where
     * @param string $columns
     *
     * @return bool|mixed
     * @throws InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
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
     * @todo add caching
     * @return ModuleInfo
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function describe()
    {
        $info = $this->connection->get('describe', ['elementType' => $this->moduleName]);

        return new ModuleInfo($info);
    }

    public function create($module): ModuleInterface
    {
        $response = $this->connection->post('create', ['element' => json_encode($module->dehydrate()), 'elementType' => $module->getModuleName()]);
        $className = get_class($module);
        $contact = new $className((array)$response);

        return $contact;
    }
}
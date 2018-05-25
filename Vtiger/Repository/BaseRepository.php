<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;


use MauticPlugin\MauticVtigerCrmBundle\Module\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;

abstract class BaseRepository implements RepositoryInterface
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $moduleName;

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
}
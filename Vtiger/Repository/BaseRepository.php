<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;


use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;

/**
 * Class BaseRepository
 * 
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
abstract class BaseRepository
{
    /** @var Connection */
    protected $connection;

    /**
     * BaseRepository constructor.
     *
     * @param Connection $connection
     * @param $moduleName
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return BaseModel
     */
    abstract public function getEmptyModel(): BaseModel;
}
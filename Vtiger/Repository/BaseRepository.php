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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;

/**
 * Class BaseRepository
 * 
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
abstract class BaseRepository
{
    const SYNC_USER = 'user';   //  user: fetches all the updates done on records assigned to you.
    const SYNC_APPLICATION = 'application'; //  application: fetches all the updates done on records assigned to any user.

    public static $moduleClassMapping = [
        'Contacts' => Contact::class,
        'Accounts' => Account::class,
        'Events'   => Event::class,
    ];

    /** @var Connection */
    protected $connection;

    /**
     * BaseRepository constructor.
     *
     * @param Connection $connection
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
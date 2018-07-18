<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 16.7.18
 * Time: 12:10
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;


use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;

final class EventSyncService
{
    /** @var Connection $connection */
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }


}
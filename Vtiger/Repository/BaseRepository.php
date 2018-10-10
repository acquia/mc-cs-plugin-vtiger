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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticCacheBundle\Cache\CacheProvider;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\CompanyDetails;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;

/**
 * Class BaseRepository
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
abstract class BaseRepository
{
    const SYNC_USER = 'user';   //  user: fetches all the updates done on records assigned to you.
    const SYNC_APPLICATION = 'application'; //  application: fetches all the updates done on records assigned to any user.
    const CACHE_NAMESPACE = 'vtigercrm_repo';

    public static $moduleClassMapping = [
        'Contacts' => Contact::class,
        'Accounts' => Account::class,
        'Events' => Event::class,
        'Leads' => Lead::class,
        'CompanyDetails' => CompanyDetails::class,
        'Users' => User::class
    ];

    /** @var Connection */
    protected $connection;

    /** @var CacheProvider */
    protected $cacheProvider;

    /**
     * BaseRepository constructor.
     *
     * @param Connection    $connection
     * @param CacheProvider $cache
     */
    public function __construct(Connection $connection, CacheProvider $cache)
    {
        $this->connection = $connection;
        $this->cacheProvider = $cache;
    }
}

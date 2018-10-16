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
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\CachedItemNotFoundException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\CompanyDetails;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache;

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
     * @var FieldCache
     */
    private $fieldCache;

    /**
     * @todo get rid of $cacheProvider here - write classes for each cache like $fieldCache
     *
     * @param Connection    $connection
     * @param CacheProvider $cacheProvider
     * @param FieldCache    $fieldCache
     */
    public function __construct(Connection $connection, CacheProvider $cacheProvider, FieldCache $fieldCache)
    {
        $this->connection    = $connection;
        $this->cacheProvider = $cacheProvider;
        $this->fieldCache    = $fieldCache;
    }

    /**
     * @return ModuleInfo
     *
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function describe(): ModuleInfo
    {
        $key = $this->getModuleFromRepositoryName();
        try {
            return $this->fieldCache->getModuleInfo($key);
        } catch (CachedItemNotFoundException $e) {
        }

        $moduleInfo = new ModuleInfo(
            $this->connection->get('describe', ['elementType' => $key])
        );
        $this->fieldCache->setModuleInfo($moduleInfo, $key);

        return $moduleInfo;
    }

    /**
     * @todo complete refactoring, object needs to be specified at one place only, not multiple
     * @todo - make this method private / protected
     *
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        $className = get_class($this);

        $parts = explode('\\', $className);

        return rtrim(str_replace('Repository', '', array_pop($parts)), 's').'s';
    }
}

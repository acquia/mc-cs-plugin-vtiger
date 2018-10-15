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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache;

use MauticPlugin\MauticCacheBundle\Cache\CacheProvider;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\CachedItemNotFoundException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInfo;
use Psr\Cache\InvalidArgumentException;

class FieldCache
{
    /** @var CacheProvider */
    protected $cacheProvider;

    /**
     * @param CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param string $key
     *
     * @return ModuleInfo
     *
     * @throws CachedItemNotFoundException
     */
    public function getModuleInfo(string $key): ModuleInfo
    {
        try {
            $cachedItem = $this->cacheProvider->getItem($key);
        } catch (InvalidArgumentException $e) {
            throw new CachedItemNotFoundException($e->getMessage());
        }
        if (!$cachedItem->isHit()) {
            throw new CachedItemNotFoundException('Item was not found');
        }

        return $cachedItem->get();
    }

    /**
     * @param ModuleInfo $moduleInfo
     * @param string     $key
     */
    public function setModuleInfo(ModuleInfo $moduleInfo, string $key): void
    {
        try {
            $cachedItem = $this->cacheProvider->getItem($key);
        } catch (InvalidArgumentException $e) {
            return;
        }

        $cachedItem->tag(['vtigercrm','vtigercrm_repository']);
        $cachedItem->expiresAfter(60*60*24*7);  // Expire after a week

        $cachedItem->set($moduleInfo);
        $this->cacheProvider->save($cachedItem);
    }
}

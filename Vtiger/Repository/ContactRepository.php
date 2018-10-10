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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class ContactRepository
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
class ContactRepository extends BaseRepository
{
    use RepositoryHelper;

    private $excludedFields = [
        'leadsource', 'contact_id', 'donotcall', 'emailoptout', 'assigned_user_id', 'modifiedby', 'imagename', 'isconvertedfromlead',
    ];

    /**
     * ContactRepository constructor.
     *
     * @param Connection    $connection
     * @param CacheProvider $cache
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct(Connection $connection, CacheProvider $cache) {
        parent::__construct($connection, $cache);
        foreach ($this->getEditableFields() as $field) {
            $this->mappableFields[] = $field->getName();
        }
    }

    /**
     * @param Contact $module
     *
     * @return Contact
     */
    public function create(Contact $module): Contact
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Contact
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Contact
    {
        $record = $this->findOneBy(['id' => $id]);

        return $record;
    }

    /**
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMappableFields(): array
    {
        $mappable = $this->getEditableFields();

        /**
         * @var int $key
         * @var ModuleFieldInfo $field
         */
        foreach ($mappable as $key=>$field) {
            if (in_array($field->getName(), $this->excludedFields)) {
                unset($mappable[$key]);
            }
        }

        return $mappable;
    }
}

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

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class EventRepository
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
class EventRepository extends BaseRepository
{
    use RepositoryHelper;

    /**
     * @param Event $module
     *
     * @return Event
     */
    public function create(Event $module): Event
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Event
     */
    public function retrieve(string $id): Event
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }

    /**
     * @param $contactId
     *
     * @return array|Event[]
     */
    public function findByContactId($contactId): array {
        $this->findBy(['contact_id'=>(string) $contactId]);
    }

    /**
     * @param array $contactIds
     *
     * @return array|Event[]
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AuthenticationException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function findByContactIds(array $contactIds): array {
        $moduleName = $this->getModuleFromRepositoryName();
        $className = self::$moduleClassMapping[$moduleName];

        $query = "select * from " . $moduleName;
        $query .= sprintf(" where contact_id in ('%s')", join("','", $contactIds));

        $return = [];

        $offset = 0; $limit = 100;

        do {
            $queryLimiter = sprintf("LIMIT %d,%d", $offset, $limit);
            $result = $this->connection->get('query', ['query' => $query . " " . $queryLimiter]);
            foreach ($result as $key=>$moduleObject) {
                $return[] = new $className((array) $moduleObject);
            }
            $offset += $limit;
        } while (count($result));

        return $return;
    }
}

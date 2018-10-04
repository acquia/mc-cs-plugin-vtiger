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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class EventRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Event $event): Event
    {
        return $this->createUnified($event);
    }

    public function retrieve(string $id): Event
    {
        return $this->findOneBy(['id'=>$id]);
    }

    public function findByContactId($contactId): void
    {
        $this->findBy(['contact_id'=>(string) $contactId]);
    }

    public function findByContactIds(array $contactIds): array
    {
        $moduleName = $this->getModuleFromRepositoryName();
        $className  = self::$moduleClassMapping[$moduleName];

        $query = 'select * from '.$moduleName;
        $query .= sprintf(" where contact_id in ('%s')", join("','", $contactIds));

        $query .= ';';

        $result = $this->connection->get('query', ['query' => $query]);
        $return = [];

        foreach ($result as $key=>$moduleObject) {
            $return[] = new $className((array) $moduleObject);
        }

        return $return;
    }
}

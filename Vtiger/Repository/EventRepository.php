<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

class EventRepository extends BaseRepository
{
    use RepositoryHelper;

    public function create(Event $module): Event
    {
        return $this->createUnified($module);
    }

    public function retrieve(string $id): Event
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }

    public function findByContactId($contactId) {
        $this->findBy(['contact_id'=>(string) $contactId]);
    }

    public function findByContactIds(array $contactIds): array {
        $moduleName = $this->getModuleFromRepositoryName();
        $className = self::$moduleClassMapping[$moduleName];

        $query = "select * from " . $moduleName;
        $query .= sprintf(" where contact_id in ('%s')", join("','", $contactIds));

        $query .= ";";

        $result = $this->connection->get('query', ['query' => $query]);
        $return = [];

        foreach ($result as $key=>$moduleObject) {
            $return[] = new $className((array) $moduleObject);
        }

        return $return;
    }
}
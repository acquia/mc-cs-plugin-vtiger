<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;

class EventFactory extends BaseModel
{
    public static function createEmptyPrefilled() {
        $event = new Event();
        $event->set('recurringtype','');
        $event->set('duration_hours', 0);
        $event->set('duration_minutes', 0);
        $event->set('eventstatus', 'Held');
//        $event->set('sendnotification', 0);
        $event->set('activitytype', 'Mautic Event');
//        $event->set('createdtime', $created = (new \DateTime())->getTimestamp());
//        $event->set('modifiedtime', $created = (new \DateTime())->getTimestamp());
        $event->set('visibility', 'Public');
        $event->set('notime', 0);
        $event->set('reminder_time', 0);
        return $event;
    }
}
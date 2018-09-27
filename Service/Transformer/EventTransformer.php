<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     26.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Service\Transformer;


use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;

class EventTransformer
{
    public function transformForMautic(Event $event) {

    }

    public function transformForVtiger(array $event) {
//        if ($)
//        $result['eventName'] = $event['']
    }

    //public function transformToUnited
}

//  0 =>
//    array (size=6)
//      'event' => string 'lead.create' (length=11)
//      'eventId' => string 'lead.create55' (length=13)
//      'icon' => string 'fa-user-secret' (length=14)
//      'eventType' => string 'Contact created' (length=15)
//      'eventPriority' => int -5
//      'timestamp' =>
//        object(DateTime)[1532]
//          public 'date' => string '2018-09-26 12:36:20.000000' (length=26)
//          public 'timezone_type' => int 3
//          public 'timezone' => string 'UTC' (length=3)

///home/jan/mautic/plugins/mautic-vtiger-plugin/Sync/EventSyncService.php:55:
//array (size=1)
//  0 =>
//    object(MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event)[2393]
//      protected 'data' =>
//        array (size=27)
//          'subject' => string 'Test evetnt' (length=11)
//          'assigned_user_id' => string '19x1' (length=4)
//          'date_start' => string '2018-09-26' (length=10)
//          'time_start' => string '09:49:00' (length=8)
//          'due_date' => string '2018-09-26' (length=10)
//          'time_end' => string '09:54:00' (length=8)
//          'recurringtype' => string '' (length=0)
//          'duration_hours' => string '0' (length=1)
//          'duration_minutes' => string '5' (length=1)
//          'parent_id' => string '' (length=0)
//          'eventstatus' => string 'Planned' (length=7)
//          'sendnotification' => string '0' (length=1)
//          'activitytype' => string 'Call' (length=4)
//          'location' => string '' (length=0)
//          'createdtime' => string '2018-09-26 09:50:03' (length=19)
//          'modifiedtime' => string '2018-09-26 09:50:03' (length=19)
//          'taskpriority' => string '' (length=0)
//          'notime' => string '0' (length=1)
//          'visibility' => string 'Public' (length=6)
//          'modifiedby' => string '19x1' (length=4)
//          'description' => string '' (length=0)
//          'reminder_time' => string '0' (length=1)
//          'contact_id' => string '12x6' (length=4)
//          'source' => string 'CRM' (length=3)
//          'starred' => string '0' (length=1)
//          'tags' => string '' (length=0)
//          'id' => string '18x8' (length=4)
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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

/**
 * Class EventFactory.
 */
class EventFactory extends BaseModel
{
    /**
     * @return Event
     */
    public static function createEmptyPrefilled(): Event
    {
        $event = new Event();
        $event->set('recurringtype', '');
        $event->set('duration_hours', 0);
        $event->set('duration_minutes', 0);
        $event->set('eventstatus', 'Held');
        $event->set('activitytype', 'Mautic Event');
        /*      $event->set('createdtime', $created = (new \DateTime())->getTimestamp());
                $event->set('modifiedtime', $created = (new \DateTime())->getTimestamp());
                $event->set('sendnotification', 0);*/
        $event->set('visibility', 'Public');
        $event->set('notime', 0);
        $event->set('reminder_time', 0);

        return $event;
    }
}

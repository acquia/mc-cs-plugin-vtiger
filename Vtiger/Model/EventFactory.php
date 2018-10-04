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

class EventFactory extends BaseModel
{
    public static function createFromUnified(array $unified, ?string $contactId = null)
    {
        $event = self::createEmptyPrefilled();
        if (null !== $contactId) {
            $event->set('contact_id', $contactId);
        }

        return $event;
    }

    public static function createEmptyPrefilled()
    {
        $event = new Event();
        $event->set('recurringtype', '');
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

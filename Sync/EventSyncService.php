<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository;

/**
 * Class AccountDataExchange
 * @package MauticPlugin\MauticVtigerCrmBundle\Sync
 */
final class EventSyncService
{
    /**
     * @var LeadEventSupplier
     */
    private $leadEventSupplier;
    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var VtigerSettingProvider
     */
    private $settingProvider;

    /**
     * EventSyncService constructor.
     *
     * @param LeadEventSupplier $leadEventSupplier
     * @param EventRepository   $eventRepository
     */
    public function __construct(LeadEventSupplier $leadEventSupplier, EventRepository $eventRepository, VtigerSettingProvider $settingProvider)
    {
        $this->leadEventSupplier = $leadEventSupplier;
        $this->eventRepository   = $eventRepository;
        $this->settingProvider   = $settingProvider;
    }

    public function sync(ObjectMapping $objectMapping)
    {

        $eventsToSynchronize = $this->getSyncReport($objectMapping->getInternalObjectId());
        var_dump($eventsToSynchronize);
        die();
    }

    private function getSyncReport($leadId)
    {
        $mauticEvents = $this->leadEventSupplier->getByLeadId($leadId);
        $vtigerEvents = $this->eventRepository->findBy([]);

        $eventTypes = array_flip($this->leadEventSupplier->getTypes());

        $result = ['up' => [], 'down' => []];

        $vtigerCheck = [];
        /** @var Event $vtigerEvent */
        foreach ($vtigerEvents as $vtigerEvent) {
            if (!isset($eventTypes[$vtigerEvent->getSubject()])) {
                continue;
            }

            $vtigerCheck[$vtigerEvent->getDateTimeStart()->getTimestamp()][] = [
                'timestamp' => $vtigerEvent->getDateTimeStart()->getTimestamp(),
                'message'   => $eventTypes[$vtigerEvent->getSubject()],
                'priority'  => $vtigerEvent->getTaskPriority(),
            ];
        }

        $mauticCheck = [];
        foreach ($mauticEvents['events'] as $mauticEvent) {
            $eventTimestamp  = $mauticEvent['timestamp']->getTimestamp();
            $checkEvent      = [
                'timestamp' => $eventTimestamp,
                'message'   => $mauticEvent['event'],
                'priority'  => $mauticEvent['eventPriority'],
            ];
            $mauticCheck[][] = $checkEvent;
            if (isset($vtigerCheck[$eventTimestamp])) {
                foreach ($vtigerCheck[$eventTimestamp] as $recordKey => $record) {
                    if ($record === $checkEvent) {
                        // This exists, we remove it from the check
                        unset($vtigerCheck[$eventTimestamp][$recordKey]);
                        continue(2);
                    }
                }
            }
            $result['up'][] = $mauticEvent;
        }

        return $result;
    }

    public function getNewVtigerEvents(Contact $contact)
    {

    }


}
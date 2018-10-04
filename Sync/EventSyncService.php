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

use Mautic\CampaignBundle\Executioner\Scheduler\Mode\DateTime;
use Mautic\CampaignBundle\Executioner\Scheduler\Mode\DateTime\DateTimeInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\EventFactory;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository;

/**
 * Class AccountDataExchange.
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
    private $vtigerSettingProvider;

    /**
     * EventSyncService constructor.
     *
     * @param LeadEventSupplier $leadEventSupplier
     * @param EventRepository   $eventRepository
     */
    public function __construct(
        LeadEventSupplier $leadEventSupplier,
        EventRepository $eventRepository,
        VtigerSettingProvider $vtigerSettingProvider
    ) {
        $this->leadEventSupplier       = $leadEventSupplier;
        $this->eventRepository         = $eventRepository;
        $this->vtigerSettingProvider   = $vtigerSettingProvider;
    }

    public function sync(DateTimeInterface $dateFrom, DateTimeInterface $dateTo): void
    {
        $mapping = $this->leadEventSupplier->getLeadsMapping();

        $eventsToSynchronize = $this->getSyncReport(
            $mapping,
            $this->vtigerSettingProvider->getSetting('activityEvents'),
            $dateFrom,
            $dateTo
        );

        foreach ($eventsToSynchronize['up'] as $eventUnifiedData) {
            var_dump($eventUnifiedData);
            die();
            $eventTime = new DateTime();
            $eventTime->setTimestamp($eventUnifiedData['timestamp']);
            /** @var Event $event */
            $event = EventFactory::createFromUnified($eventUnifiedData, $eventUnifiedData);
            $event->setDateTimeStart($eventTime);
            $event->setDateTimeEnd($eventTime);
            $event->setSubject($eventUnifiedData['message']);
            $event->setTaskPriority((string) $eventUnifiedData['priority']);
            $event->setAssignedUserId($this->vtigerSettingProvider->getSetting('owner'));

            $this->eventRepository->create($event);
        }
    }

    public function getNewVtigerEvents(Contact $contact): void
    {
    }

    private function getSyncReport($mappings, array $events = [], $dateFrom = null, $dateTo = null)
    {
        $mauticEvents = $this->leadEventSupplier->getLeadEvents(array_keys($mappings), $events, $dateFrom, $dateTo);

        $vtigerEvents = $this->eventRepository->findByContactIds($mappings);

        $eventTypes = array_flip($this->leadEventSupplier->getTypes());

        $result = [
            'up'   => [],
            'down' => [],
        ];

        $vtigerCheck = [];
        /** @var Event $vtigerEvent */
        foreach ($vtigerEvents as $vtigerEvent) {
            if (!isset($eventTypes[$vtigerEvent->getSubject()])) {
                continue;
            }

            $vtigerCheck[$vtigerEvent->getContactId()][$vtigerEvent->getDateTimeStart()->getTimestamp()][] = [
                'timestamp' => $vtigerEvent->getDateTimeStart()->getTimestamp(),
                'message'   => $vtigerEvent->getSubject(),
                'event'     => $eventTypes[$vtigerEvent->getSubject()],
                'priority'  => $vtigerEvent->getTaskPriority(),
            ];
        }

        $eventTypesFlipped = array_flip($eventTypes);

        $mauticCheck = [];
        foreach ($mauticEvents['events'] as $mauticEvent) {
            $eventTimestamp  = $mauticEvent['timestamp']->getTimestamp();
            $checkEvent      = [
                'timestamp' => $eventTimestamp,
                'message'   => $eventTypesFlipped[$mauticEvent['event']],
                'event'     => $mauticEvent['event'],
                'priority'  => $mauticEvent['eventPriority'],
            ];
            $mauticCheck[][] = $checkEvent;
            if (isset($vtigerCheck[$eventTimestamp])) {
                foreach ($vtigerCheck[$eventTimestamp] as $recordKey => $record) {
                    if ($record === $checkEvent) {
                        // This exists, we remove it from the check
                        unset($vtigerCheck[$eventTimestamp][$recordKey]);

                        continue 2;
                    }
                }
            }
            $result['up'][] = $checkEvent;
        }

        return $result;
    }
}

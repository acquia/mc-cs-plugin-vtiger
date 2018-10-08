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

use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\EventFactory;
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


    /**
     * @param \DateTimeInterface|null $dateFrom
     * @param \DateTimeInterface|null $dateTo
     */
    public function sync(?\DateTimeInterface $dateFrom, ?\DateTimeInterface $dateTo)
    {
        $mapping = $this->leadEventSupplier->getLeadsMapping();

        $this->settingProvider->exceptConfigured();

        $eventsToSynchronize = $this->getSyncReport($mapping, $this->settingProvider->getSyncSetting('activityEvents'), $dateFrom, $dateTo);


        DebugLogger::log(VtigerCrmIntegration::NAME, sprintf('Uploading %d Events', count($eventsToSynchronize['up'])));

        foreach ($eventsToSynchronize['up'] as $event) {
            $this->eventRepository->create($event);
        }

        DebugLogger::log(VtigerCrmIntegration::NAME, 'Events have been uploaded');
    }

    /**
     * TODO refactor to compare two arrays at once, less iteration!
     *
     * @param       $mappings
     * @param array $events
     * @param null  $dateFrom
     * @param null  $dateTo
     *
     * @return array
     * @throws \Exception
     */
    private function getSyncReport($mappings, array $events = [], $dateFrom = null, $dateTo = null) {
        $mauticEvents = $this->leadEventSupplier->getLeadEvents(array_keys($mappings), $events, $dateFrom, $dateTo);

        $vtigerEvents = $this->eventRepository->findByContactIds($mappings);

        $eventTypesFlipped = array_flip($this->leadEventSupplier->getTypes());
        $eventTypes = $this->leadEventSupplier->getTypes();

        $result = ['up' => [], 'down' => []];

        $vtigerCheck = [];
        /** @var Event $vtigerEvent */
        foreach ($vtigerEvents as $vtigerEvent) {
            if (!isset($eventTypesFlipped[$vtigerEvent->getSubject()])) {
                continue;
            }

            $vtigerCheck[$vtigerEvent->getContactId()][$vtigerEvent->getDateTimeStart()->getTimestamp()][] = [
                'timestamp' => $vtigerEvent->getDateTimeStart()->getTimestamp(),
                'message' => $vtigerEvent->getSubject(),
                'event'   => $eventTypesFlipped[$vtigerEvent->getSubject()],
                'priority'  => $vtigerEvent->getTaskPriority(),

            ];
        }

        foreach ($mauticEvents as $mauticLeadId=>$leadEventsArray) {
            $vtigerId = $mappings[$mauticLeadId] ?? false;
            if (!$vtigerId) {   // Do not upload to not mapped contacts
                continue;
            }
            foreach ($leadEventsArray as $eventTimeStamp=>$leadEvents) {
                foreach ($leadEvents as $event) {
                    $eventCheck = [
                        'timestamp' => $eventTimeStamp,
                        'message'   => $eventTypes[$event['event']],
                        'event'     => $event['event'],
                        'priority'  => $event['priority']
                    ];

                    if (isset($vtigerCheck[$vtigerId][$eventTimeStamp]) && in_array($eventCheck, $vtigerCheck[$vtigerId][$eventTimeStamp])) {
                        continue;
                    }

                    $eventTime = new \DateTime();
                    $eventTime->setTimestamp($eventTimeStamp);
                    /** @var Event $event */
                    $event = EventFactory::createEmptyPrefilled();
                    $event->setContactId($vtigerId);
                    $event->setDateTimeStart($eventTime);
                    $event->setDateTimeEnd($eventTime);
                    $event->setSubject($eventCheck['message']);
                    $event->setTaskPriority((string)$eventCheck['priority']);
                    $event->setAssignedUserId($this->settingProvider->getSetting('owner'));
                    $result['up'][] = $event;
                }
            }

        }

        return $result;
    }
}
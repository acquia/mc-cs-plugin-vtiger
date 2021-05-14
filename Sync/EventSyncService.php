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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\EventFactory;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\EventRepository;

/**
 * Class AccountDataExchange.
 */
class EventSyncService
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
     * @param LeadEventSupplier     $leadEventSupplier
     * @param EventRepository       $eventRepository
     * @param VtigerSettingProvider $settingProvider
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
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function sync(?\DateTimeInterface $dateFrom, ?\DateTimeInterface $dateTo): void
    {
        if (!$this->settingProvider->isActivitySyncEnabled()) {
            return;
        }

        $mapping = $this->leadEventSupplier->getLeadsMapping();

        if (!count($mapping)) {
            DebugLogger::log(VtigerCrmIntegration::NAME, 'No mapped contacts to synchronize activities for.');

            return;
        }

        $this->settingProvider->exceptConfigured();

        $eventsToSynchronize = $this->getSyncReport($mapping, $this->settingProvider->getActivityEvents(), $dateFrom, $dateTo);

        DebugLogger::log(VtigerCrmIntegration::NAME, sprintf('Uploading %d Events', count($eventsToSynchronize['up'])));

        $iter = 0;
        foreach ($eventsToSynchronize['up'] as $event) {
            DebugLogger::log(
                VtigerCrmIntegration::NAME,
                sprintf(
                    'Creating %s [%d%%] %d of %d ',
                    'event',
                    round(100 * (++$iter / count($eventsToSynchronize['up']))),
                    $iter,
                    count($eventsToSynchronize['up'])
                )
            );
            $this->eventRepository->create($event);
        }

        DebugLogger::log(VtigerCrmIntegration::NAME, 'Events have been uploaded');
    }

    /**
     * @param       $mappings
     * @param array $events
     * @param null  $dateFrom
     * @param null  $dateTo
     *
     * @return array
     * @throws \Exception
     */
    private function getSyncReport($mappings, array $events = [], $dateFrom = null, $dateTo = null)
    {
        $mauticEvents = $this->leadEventSupplier->getLeadEvents(array_keys($mappings), $events, $dateFrom, $dateTo);

        $vtigerEvents = $this->eventRepository->findByContactIds($mappings);

        $eventTypesFlipped = array_flip($this->leadEventSupplier->getTypes());
        $eventTypes        = $this->leadEventSupplier->getTypes();

        $result = ['up' => [], 'down' => []];

        $vtigerCheck = [];
        /** @var Event $vtigerEvent */
        foreach ($vtigerEvents as $vtigerEvent) {
            if (!isset($eventTypesFlipped[$vtigerEvent->getSubject()])) {
                continue;
            }

            $vtigerCheck[$vtigerEvent->getContactId()][$vtigerEvent->getDateTimeStart()->getTimestamp()][] = [
                'timestamp' => $vtigerEvent->getDateTimeStart()->getTimestamp(),
                'message'   => $vtigerEvent->getSubject(),
                'event'     => $eventTypesFlipped[$vtigerEvent->getSubject()],
                'priority'  => $vtigerEvent->getTaskPriority(),
            ];
        }

        $found = 0;
        foreach ($mauticEvents as $mauticLeadId => $leadEventsArray) {
            $vtigerId = $mappings[$mauticLeadId] ?? false;
            if (!$vtigerId) {   // Do not upload to not mapped contacts
                continue;
            }
            foreach ($leadEventsArray as $eventTimeStamp => $leadEvents) {
                foreach ($leadEvents as $event) {
                    $eventCheck = [
                        'timestamp' => $eventTimeStamp,
                        'message'   => $eventTypes[$event['event']],
                        'event'     => $event['event'],
                        'priority'  => $event['priority'],
                    ];

                    if (isset($vtigerCheck[$vtigerId][$eventTimeStamp]) && in_array($eventCheck, $vtigerCheck[$vtigerId][$eventTimeStamp])) {
                        ++$found;
                        continue;
                    }

                    $eventTime = new \DateTime();
                    $eventTime->setTimestamp($eventTimeStamp);
                    /** @var Event $event */
                    $event = EventFactory::createEmptyPrefilled();
                    $event->setContactId((string)$vtigerId);
                    $event->setDateTimeStart($eventTime);
                    $event->setDateTimeEnd($eventTime);
                    $event->setSubject($eventCheck['message']);
                    $event->setTaskPriority($eventCheck['priority']);
                    $event->setAssignedUserId($this->settingProvider->getOwner());
                    $result['up'][] = $event;
                }
            }
        }

        return $result;
    }
}

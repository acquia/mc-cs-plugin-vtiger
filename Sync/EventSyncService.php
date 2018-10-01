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
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Service\LeadEventSupplier;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
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

    public function sync()
    {
        $mappedIds = $this->leadEventSupplier->getMappedLeadIds();

        $eventsToSynchronize = $this->getSyncReport($mappedIds);

        foreach ($eventsToSynchronize['up'] as $eventUnifiedData) {
            $eventTime = new \DateTime();
            $eventTime->setTimestamp($eventUnifiedData['timestamp']);
            /** @var Event $event */
            $event = EventFactory::createFromUnified($eventUnifiedData, $objectMapping->getIntegrationObjectId());
            $event->setDateTimeStart($eventTime);
            $event->setDateTimeEnd($eventTime);
            $event->setSubject($eventUnifiedData['message']);
            $event->setTaskPriority((string) $eventUnifiedData['priority']);
            $event->setAssignedUserId($this->settingProvider->getSetting('owner'));

            $this->eventRepository->create($event);
        }
    }

    private function getSyncReport(array $leadIds, array $events = [], $dateFrom = null, $dateTo = null) {
        $dateFrom = new \DateTime('-3 days');
        $dateTo = new \DateTime();
        $events = $this->leadEventSupplier->getLeadEvents($leadIds, $events, $dateFrom, $dateTo);
        var_dump($events);
        die();
    }

    private function getLeadSyncReport($leadId)
    {
        $mauticEvents = $this->leadEventSupplier->getLeadEvents($leadId);
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
                'message' => $vtigerEvent->getSubject(),
                'event'   => $eventTypes[$vtigerEvent->getSubject()],
                'priority'  => $vtigerEvent->getTaskPriority(),

            ];
        }

        $eventTypesFlipped = array_flip($eventTypes);

        $mauticCheck = [];
        foreach ($mauticEvents['events'] as $mauticEvent) {
            $eventTimestamp  = $mauticEvent['timestamp']->getTimestamp();
            $checkEvent      = [
                'timestamp' => $eventTimestamp,
                'message' => $eventTypesFlipped[$mauticEvent['event']],
                'event'   => $mauticEvent['event'],
                'priority'  => $mauticEvent['eventPriority']
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
            $result['up'][] = $checkEvent;
        }

        return $result;
    }

    public function getNewVtigerEvents(Contact $contact)
    {

    }


}
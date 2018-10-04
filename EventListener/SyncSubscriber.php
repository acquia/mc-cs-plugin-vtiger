<?php

/*
 * @copyright   2018 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\EventListener;

use MauticPlugin\IntegrationsBundle\Event\SyncEvent;
use MauticPlugin\IntegrationsBundle\SyncEvents;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SyncSubscriber implements EventSubscriberInterface
{
    /**
     * @var EventSyncService
     */
    private $eventSyncService;

    public function __construct(EventSyncService $eventSyncService, )
    {
        $this->eventSyncService = $eventSyncService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SyncEvents::INTEGRATION_POST_EXECUTE => ['onPostExecuteOrder', 0],
        ];
    }

    /**
     * @param SyncEvent $event
     */
    public function onPostExecuteOrder(SyncEvent $event) {
        if (!$event->isIntegration(VtigerCrmIntegration::NAME)) {
            return;
        }

        $this->eventSyncService->sync()
    }
}

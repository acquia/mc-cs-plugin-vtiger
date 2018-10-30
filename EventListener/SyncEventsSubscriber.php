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

namespace MauticPlugin\MauticVtigerCrmBundle\EventListener;

use MauticPlugin\IntegrationsBundle\Event\SyncEvent;
use MauticPlugin\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SyncSubscriber.
 */
class SyncEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @var EventSyncService
     */
    private $eventSyncService;

    /**
     * @param EventSyncService $eventSyncService
     */
    public function __construct(EventSyncService $eventSyncService)
    {
        $this->eventSyncService = $eventSyncService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IntegrationEvents::INTEGRATION_POST_EXECUTE => ['onPostExecuteOrder', 0],
        ];
    }

    /**
     * @param SyncEvent $event
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function onPostExecuteOrder(SyncEvent $event): void
    {
        if (!$event->isIntegration(VtigerCrmIntegration::NAME)) {
            return;
        }

        $this->eventSyncService->sync($event->getFromDateTime(), $event->getToDateTime());
    }
}

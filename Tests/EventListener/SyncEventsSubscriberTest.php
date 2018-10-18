<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\EventListener;

use MauticPlugin\IntegrationsBundle\Event\SyncEvent;
use MauticPlugin\IntegrationsBundle\SyncEvents;
use MauticPlugin\MauticVtigerCrmBundle\EventListener\SyncEventsSubscriber;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService;

class SyncEventsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SyncEventsSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $eventSyncService = $this->createMock(EventSyncService::class);
        $this->subscriber = new SyncEventsSubscriber($eventSyncService);
    }

    public function testOnPostExecuteOrder()
    {
        $event = $this->createMock(SyncEvent::class);
        $event->expects($this->once())
            ->method('isIntegration')
            ->with(VtigerCrmIntegration::NAME)
            ->willReturn(false);
        $this->assertNull($this->subscriber->onPostExecuteOrder($event));

        $fromDateTime = new \DateTimeImmutable();
        $toDateTime = new \DateTimeImmutable();

        $event = $this->createMock(SyncEvent::class);
        $event->expects($this->once())
            ->method('isIntegration')
            ->with(VtigerCrmIntegration::NAME)
            ->willReturn(true);
        $event->expects($this->once())
            ->method('getFromDateTime')
            ->willReturn($fromDateTime);
        $event->expects($this->once())
            ->method('getToDateTime')
            ->willReturn($toDateTime);
        $eventSyncService = $this->createMock(EventSyncService::class);
        $eventSyncService->expects($this->once())
            ->method('sync')
            ->with($fromDateTime, $toDateTime);
        $subscriber = new SyncEventsSubscriber($eventSyncService);
        $this->assertNull($subscriber->onPostExecuteOrder($event));
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [SyncEvents::INTEGRATION_POST_EXECUTE => ['onPostExecuteOrder', 0]],
            $this->subscriber->getSubscribedEvents()
        );
    }
}

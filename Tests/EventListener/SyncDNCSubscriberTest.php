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

use MauticPlugin\IntegrationsBundle\SyncEvents;
use MauticPlugin\MauticVtigerCrmBundle\EventListener\SyncDNCSubscriber;
use MauticPlugin\MauticVtigerCrmBundle\Sync\EventSyncService;

class SyncDNCSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SyncDNCSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $eventSyncService = $this->createMock(EventSyncService::class);
        $this->subscriber = new SyncDNCSubscriber($eventSyncService);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [SyncEvents::INTEGRATION_POST_EXECUTE => ['onPostExecuteOrder', 0]],
            $this->subscriber->getSubscribedEvents()
        );
    }
}

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

use MauticPlugin\IntegrationsBundle\Event\FormLoadEvent;
use MauticPlugin\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\MauticVtigerCrmBundle\EventListener\ConfigFormLoadSubscriber;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache;

class ConfigFormLoadSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigFormLoadSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $fieldCache = $this->createMock(FieldCache::class);
        $this->subscriber = new ConfigFormLoadSubscriber($fieldCache);
    }

    public function testOnConfigFormLoad()
    {
        $event = $this->createMock(FormLoadEvent::class);
        $event->expects($this->once())
            ->method('getIntegration')
            ->willReturn('nonsense');
        $this->assertNull($this->subscriber->onConfigFormLoad($event));

        $fieldCache = $this->createMock(FieldCache::class);
        $fieldCache->expects($this->once())
            ->method('ClearCacheForConfigForm');
        $subscriber = new ConfigFormLoadSubscriber($fieldCache);
        $event = $this->createMock(FormLoadEvent::class);
        $event->expects($this->once())
            ->method('getIntegration')
            ->willReturn(VtigerCrmIntegration::NAME);
        $this->assertNull($subscriber->onConfigFormLoad($event));
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [IntegrationEvents::INTEGRATION_CONFIG_FORM_LOAD => ['onConfigFormLoad', 0]],
            $this->subscriber->getSubscribedEvents()
        );
    }
}

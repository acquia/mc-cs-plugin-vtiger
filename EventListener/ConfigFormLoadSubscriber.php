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

use MauticPlugin\IntegrationsBundle\Event\FormLoadEvent;
use MauticPlugin\IntegrationsBundle\SyncEvents;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigFormLoadSubscriber implements EventSubscriberInterface
{
    /**
     * @var FieldCache
     */
    private $fieldCache;

    public function __construct(FieldCache $fieldCache)
    {
        $this->fieldCache = $fieldCache;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * The array keys are event names and the value can be:
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     * For instance:
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            SyncEvents::INTEGRATION_CONFIG_FORM_LOAD => ['onConfigFormLoad', 0],
        ];
    }

    public function onConfigFormLoad(FormLoadEvent $event): void {
        if ($event->getIntegrationName() !== VtigerCrmIntegration::NAME) {
            return;
        }

        $this->fieldCache->configFormWasLoaded();
    }
}
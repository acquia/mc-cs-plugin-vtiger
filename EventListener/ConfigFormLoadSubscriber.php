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

use Mautic\IntegrationsBundle\Event\FormLoadEvent;
use Mautic\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Cache\FieldCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigFormLoadSubscriber implements EventSubscriberInterface
{
    /**
     * @var FieldCache
     */
    private $fieldCache;

    /**
     * @param FieldCache $fieldCache
     */
    public function __construct(FieldCache $fieldCache)
    {
        $this->fieldCache = $fieldCache;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IntegrationEvents::INTEGRATION_CONFIG_FORM_LOAD => ['onConfigFormLoad', 0],
        ];
    }

    /**
     * @param FormLoadEvent $event
     */
    public function onConfigFormLoad(FormLoadEvent $event): void
    {
        if (VtigerCrmIntegration::NAME !== $event->getIntegration()) {
            return;
        }

        $this->fieldCache->ClearCacheForConfigForm();
    }
}

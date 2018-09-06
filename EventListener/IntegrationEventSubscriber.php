<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\EventListener;

use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\IntegrationsBundle\Event\SyncEvent;
use MauticPlugin\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\DTO\Contact;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class IntegrationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var DataExchange
     */
    private $dataExchange;

    /**
     * @var IntegrationHelper
     */
    private $integrationObject;

    /**
     * IntegrationEventSubscriber constructor.
     *
     * @param DataExchange      $dataExchange
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(DataExchange $dataExchange, IntegrationHelper $integrationHelper)
    {
        $this->integrationObject = $integrationHelper->getIntegrationObject(VtigerCrmIntegration::NAME);
        $this->dataExchange = $dataExchange;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IntegrationEvents::ON_SYNC_TRIGGERED => ['onSync', 0],
        ];
    }


    public function onSync(SyncEvent $syncEvent): void {
        if (!$syncEvent->shouldIntegrationSync(VtigerCrmIntegration::NAME)) {
            return;
        }

        $mappingManual = $this->dataExchange->getFieldMapper()->getObjectsMappingManual();

        $syncEvent->setSyncServices($this->dataExchange, $mappingManual);
    }
}

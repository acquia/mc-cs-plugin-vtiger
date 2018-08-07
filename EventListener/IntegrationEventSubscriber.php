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
use MauticPlugin\MauticIntegrationsBundle\DAO\Mapping\MappingManualDAO;
use MauticPlugin\MauticIntegrationsBundle\DAO\Mapping\ObjectMappingDAO;
use MauticPlugin\MauticIntegrationsBundle\Event\SyncEvent;
use MauticPlugin\MauticIntegrationsBundle\Facade\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\MauticIntegrationsBundle\IntegrationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class IntegrationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SyncDataExchange
     */
    private $syncDataExchange;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SyncDataExchange $syncDataExchange
     * @param Config           $syncDataExchange
     */
    public function __construct(SyncDataExchange $syncDataExchange,  $config)
    {
        $this->config           = $config;
        $this->syncDataExchange = $syncDataExchange;
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

    /**
     * @param SyncEvent $event
     */
    public function onSync(SyncEvent $syncEvent): void
    {
        if (!$syncEvent->shouldIntegrationSync(MagentoIntegration::NAME)) {
            return;
        }

        $customerObjectMapping = new ObjectMappingDAO(MauticSyncDataExchange::CONTACT_OBJECT, Customer::NAME);

        foreach ($this->config->getMappedFields() as $magentoField => $mauticField) {
            $customerObjectMapping->addFieldMapping(
                $mauticField,
                $magentoField,
                $this->config->getFieldDirection($magentoField)
            );
        }

        $mappingManual = new MappingManualDAO(MagentoIntegration::NAME);
        $mappingManual->addObjectMapping($customerObjectMapping);
        $syncEvent->setSyncServices($this->syncDataExchange, $mappingManual);
    }
}

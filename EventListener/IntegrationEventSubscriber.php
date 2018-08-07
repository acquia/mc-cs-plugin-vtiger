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
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\SyncDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\DTO\Contact;
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

    public function __construct(SyncDataExchange $syncDataExchange, IntegrationHelper $integrationEntity)
    {
        $this->config           = $integrationEntity;
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
        if (!$syncEvent->shouldIntegrationSync($this->config->getName())) {
            echo "no sync";
            return;
        }

        $customerObjectMapping = new ObjectMappingDAO(MauticSyncDataExchange::CONTACT_OBJECT, Contact::NAME);

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

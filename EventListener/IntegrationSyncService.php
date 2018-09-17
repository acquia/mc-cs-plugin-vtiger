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

use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\IntegrationsBundle\Event\SyncEvent;
use MauticPlugin\IntegrationsBundle\Integration\BasicIntegration;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\SyncInterface;
use MauticPlugin\IntegrationsBundle\IntegrationEvents;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\DTO\Contact;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class IntegrationSyncService implements EventSubscriberInterface, SyncInterface
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
     * @var Integration
     */
    private $integration;


    /**
     * @return string
     */
    public function getName() {
        return VtigerCrmIntegration::NAME;
    }

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
//            IntegrationEvents::ON_SYNC_TRIGGERED => ['onSync', 0],
        ];
    }


    public function onSync(SyncEvent $syncEvent): void {
        if (!$syncEvent->shouldIntegrationSync(VtigerCrmIntegration::NAME)) {
            return;
        }
        throw new \Exception('Not implemented');

//        $mappingManual =

        //var_dump($mappingManual->getObjectMapping('lead','Contacts')); die();

        $syncEvent->setSyncServices($this->dataExchange, $mappingManual);
    }

    /**
     * @return MappingManualDAO
     */
    public function getMappingManual(): MappingManualDAO
    {
        return $this->dataExchange->getFieldMapper()->getObjectsMappingManual();
    }

    /**
     * @return SyncDataExchangeInterface
     */
    public function getSyncDataExchange(): SyncDataExchangeInterface
    {
        return $this->dataExchange;
    }

    /**
     * @return Integration
     */
    public function getIntegration(): Integration
    {
        return $this->integration;
    }

    /**
     * @param Integration $integration
     */
    public function setIntegration(Integration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Check if Integration entity has been set to prevent PHP fatal error with using getIntegrationEntity
     *
     * @return bool
     */
    public function hasIntegration(): bool
    {
        return !empty($this->integration);
    }
}

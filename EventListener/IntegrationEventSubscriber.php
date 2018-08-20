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
     * @var IntegrationHelper
     */
    private $integrationObject;


    /**
     * @var bool|\Mautic\PluginBundle\Integration\AbstractIntegration
     */
    private $integrationEntity;

    public function __construct(SyncDataExchange $syncDataExchange, IntegrationHelper $integrationHelper)
    {
        $this->integrationObject = $integrationHelper->getIntegrationObject('VtigerCrm');
        $this->syncDataExchange = $syncDataExchange;
        $this->integrationEntity = $this->integrationObject->getIntegrationEntity();
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
     * Returns mapped fields that the user configured for this integration.
     * In format [magento_field_alias => mautic_field_alias].
     *
     * @return array
     */
    public function getMappedFields(): array
    {
        return empty($this->integrationEntity->getFeatureSettings()['leadFields']) ? [] : $this->integrationEntity->getFeatureSettings()['leadFields'];
    }

    public function onSync(SyncEvent $syncEvent): void
    {
        //var_dump($this->integrationHelper->getIntegrationObject('VtigerCrm')); die();
        if (!$syncEvent->shouldIntegrationSync('VtigerCrm')) {
            echo "no sync";

            return;
        }

        $customerObjectMapping = new ObjectMappingDAO(MauticSyncDataExchange::CONTACT_OBJECT, Contact::NAME);

        var_dump($this->getMappedFields());

        foreach ($this->getMappedFields() as $magentoField => $mauticField) {
            $customerObjectMapping->addFieldMapping(
                $mauticField,
                $magentoField,
                $this->getFieldDirection($magentoField)
            );
        }

        $mappingManual = new MappingManualDAO('VtigerCrm');
        $mappingManual->addObjectMapping($customerObjectMapping);

        $syncEvent->setSyncServices($this->syncDataExchange, $mappingManual);
    }


    /**
     * @param string $alias
     *
     * @return string
     *
     * @throws UnexpectedValueExceptionf
     */
    public function getFieldDirection(string $alias): string
    {
        if (isset($this->getMappedFieldsDirections()[$alias])) {
            return $this->getMappedFieldsDirections()[$alias];
        }

        throw new UnexpectedValueException("There is no field direction for field '${alias}'.");
    }

    /**
     * Returns direction of what field to sinc where.
     * In format [magento_field_alias => direction].
     *
     * @return array
     *
     * @throws UnexpectedValueException
     */
    public function getMappedFieldsDirections(): array
    {
        if (!$this->fieldDirections) {
            foreach ($this->getRawFieldDirections() as $alias => $rawValue) {
                $rawValueInt = (int) $rawValue;
                if (1 === $rawValueInt) {
                    $value = ObjectMappingDAO::SYNC_TO_MAUTIC;
                } elseif (0 === $rawValueInt) {
                    $value = ObjectMappingDAO::SYNC_TO_INTEGRATION;
                } else {
                    throw new UnexpectedValueException(
                        "Value '${rawValue}' is not supported as a mapped field direction."
                    );
                }

                $this->fieldDirections[$alias] = $value;
            }
        }

        return $this->fieldDirections;
    }

    /**
     * Returns mapped field directions in format [magento_field_alias => 0/1].
     *
     * @return array
     */
    private function getRawFieldDirections(): array
    {
        return empty($this->integrationEntity->getFeatureSettings()['update_mautic']) ? [] : $this->integrationEntity->getFeatureSettings()['update_mautic'];
    }

    /**
     * @var string[]
     */
    private $fieldDirections = [];
}

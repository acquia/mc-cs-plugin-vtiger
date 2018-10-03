<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.com
 * @created     ${DATE}
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration\Provider;

use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MauticPlugin\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VtigerSettingProvider
 * @package MauticPlugin\MauticVtigerCrmBundle\Integration
 */
class VtigerSettingProvider
{
    /** @var IntegrationsHelper */
    private $integrationHelper;

    /** @var Integration */
    private $integrationEntity;

    /** @var UserRepository */
    private $container;

    public function __construct(IntegrationsHelper $helper, ContainerInterface $container)
    {
        $this->integrationHelper = $helper;
        $this->container         = $container;
    }

    /**
     * @return Integration
     */
    public function getIntegrationEntity(): ?Integration
    {
        if (is_null($this->integrationEntity)) {
            try {
                $integrationObject       = $this->integrationHelper->getIntegration(VtigerCrmIntegration::NAME);
                $this->integrationEntity = $integrationObject->getIntegrationConfiguration();
            } catch (IntegrationNotFoundException $exception) {
                return null;
            }
        }
        return $this->integrationEntity;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if ($this->getIntegrationEntity() === null) {
            return [];
        }
        return $this->integrationEntity->getApiKeys();
    }


    /**
     * @return array
     */
    public function getFormOwners(): array
    {
        $owners = $this->container->get('mautic.vtiger_crm.repository.users')->findBy();
        $ownersArray = [];
        foreach ($owners as $owner) {
            $ownersArray[$owner->getId()] = (string)$owner;
        }

        return $ownersArray;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        if ($this->getIntegrationEntity() === null) {
            return [];
        }
        return $this->integrationEntity->getFeatureSettings();
    }

    /**
     * @param $settingName
     *
     * @return array|string
     */
    public function getSetting($settingName)
    {
        $settings = $this->getSettings();

        if (!array_key_exists($settingName, $settings)) {
            // todo debug only @debug
            throw new \InvalidArgumentException(
                sprintf('Setting "%s" does not exists, supported: %s',
                    $settingName, join(', ', array_keys($settings))
                ));
        }

        return $settings[$settingName];
    }
}
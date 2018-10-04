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

use InvalidArgumentException;
use Mautic\PluginBundle\Entity\Integration;
use MauticPlugin\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MauticPlugin\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VtigerSettingProvider.
 */
class VtigerSettingProvider
{
    /**
     * @var IntegrationsHelper
     */
    private $integrationsHelper;

    /**
     * @var Integration
     */
    private $integration;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(IntegrationsHelper $integrationsHelper, ContainerInterface $container)
    {
        $this->integrationsHelper     = $integrationsHelper;
        $this->userRepository         = $container;
    }

    /**
     * @return Integration
     */
    public function getIntegrationEntity(): ?Integration
    {
        if (null === $this->integration) {
            try {
                $integrationObject       = $this->integrationsHelper->getIntegration(VtigerCrmIntegration::NAME);
                $this->integration       = $integrationObject->getIntegrationConfiguration();
            } catch (IntegrationNotFoundException $exception) {
                return null;
            }
        }

        return $this->integration;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if (null === $this->getIntegrationEntity()) {
            return [];
        }

        return $this->integration->getApiKeys();
    }

    /**
     * @return array
     */
    public function getFormOwners(): array
    {
        $owners      = $this->userRepository->get('mautic.vtiger_crm.repository.users')->findBy();
        $ownersArray = [];
        foreach ($owners as $owner) {
            $ownersArray[$owner->getId()] = (string) $owner;
        }

        return $ownersArray;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        if (null === $this->getIntegrationEntity()) {
            return [];
        }

        return $this->integration->getFeatureSettings();
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
            throw new InvalidArgumentException(
                sprintf('Setting "%s" does not exists, supported: %s',
                    $settingName, join(', ', array_keys($settings))
                ));
        }

        return $settings[$settingName];
    }
}

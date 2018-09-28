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
use MauticPlugin\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MauticPlugin\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository;

/**
 * Class VtigerSettingProvider
 * @package MauticPlugin\MauticVtigerCrmBundle\Integration
 */
class VtigerSettingProvider
{
    /** @var IntegrationsHelper */
    private $integrationsHelper;

    /** @var Integration */
    private $integrationEntity;

    /** @var UserRepository */
    private $userRepository;

    /**
     * VtigerSettingProvider constructor.
     *
     * @param IntegrationsHelper $helper
     * @param UserRepository     $userRepository
     */
    public function __construct(IntegrationsHelper $helper, UserRepository $userRepository)
    {
        $this->integrationsHelper = $helper;
        $this->userRepository     = $userRepository;
    }

    /**
     * @return Integration
     */
    public function getIntegrationEntity(): ?Integration
    {
        if (is_null($this->integrationEntity)) {
            try {
                $integrationObject       = $this->integrationsHelper->getIntegration(VtigerCrmIntegration::NAME);
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
    public function getSettings(): array
    {
        if ($this->getIntegrationEntity() === null) {
            return [];
        }

        return $this->integrationEntity->getFeatureSettings();
    }

    /**
     * Gets a setting from the ConfigSyncFeaturesType form
     *
     * @param string $settingName
     *
     * @return mixed
     */
    public function getSyncSetting(string $settingName)
    {
        $settings = $this->getSettings()['sync']['integration'] ?? [];

        if (!array_key_exists($settingName, $settings)) {
            // todo debug only @debug
            throw new \InvalidArgumentException(
                sprintf(
                    'Setting "%s" does not exists, supported: %s',
                    $settingName,
                    join(', ', array_keys($settings))
                )
            );
        }

        return $settings[$settingName];
    }

    /**
     * @return array
     */
    public function getSyncObjects()
    {
        return isset($this->getSettings()['sync']['objects']) ? $this->getSettings()['sync']['objects'] : [];
    }

    /**
     * @param string $object
     *
     * @return array
     */
    public function getFieldMappings(string $object): array
    {
        return isset($this->getSettings()['sync']['fieldMappings'][$object]) ? $this->getSettings()['sync']['fieldMappings'][$object] : [];
    }
}
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

namespace MauticPlugin\MauticVtigerCrmBundle\Integration\Provider;

use Mautic\PluginBundle\Entity\Integration;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticVtigerCrmBundle\Enum\SettingsKeyEnum;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;

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
    private $integrationEntity;

    /**
     * @param IntegrationsHelper $helper
     */
    public function __construct(IntegrationsHelper $helper)
    {
        $this->integrationsHelper = $helper;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if (null === $this->getIntegrationEntity()) {
            return [];
        }

        return $this->integrationEntity->getApiKeys();
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        $credentialsCfg = $this->getCredentials();

        return !((!isset($credentialsCfg['accessKey']) || !isset($credentialsCfg['username']) || !isset($credentialsCfg['url'])));
    }

    /**
     * @throws PluginNotConfiguredException
     */
    public function exceptConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new PluginNotConfiguredException(VtigerCrmIntegration::NAME.' is not configured');
        }
    }

    /**
     * @return array
     */
    public function getSyncObjects(): array
    {
        return $this->getSettings()['sync']['objects'] ?? [];
    }

    /**
     * @param string $object
     *
     * @return array
     */
    public function getFieldMappings(string $object): array
    {
        return $this->getSettings()['sync']['fieldMappings'][$object] ?? [];
    }

    /**
     * @return bool
     *
     * @throws PluginNotConfiguredException
     */
    public function isActivitySyncEnabled(): bool
    {
        $this->exceptConfigured();

        return false; // Activities are commented out in the form.
        return in_array(SettingsKeyEnum::PUSH_ACTIVITY_IS_ENABLED, $this->getIntegrationEntity()->getSupportedFeatures(), true);
    }

    /**
     * @return array
     */
    public function getActivityEvents(): array
    {
        return []; // Activities are commented out in the form.
        return $this->getSyncSetting(SettingsKeyEnum::ACTIVITY_EVENTS);
    }

    /**
     * @return bool
     */
    public function isOwnerUpdateEnabled(): bool
    {
        return (bool) $this->getSyncSetting(SettingsKeyEnum::OWNER_UPDATE_IS_ENABLED);
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return (string) $this->getSyncSetting(SettingsKeyEnum::OWNER);
    }

    /**
     * @return bool
     */
    public function shouldBeMauticContactPushedAsLead(): bool
    {
        return $this->getSyncSetting(SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS) === SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS_LEAD;
    }

    /**
     * @return bool
     */
    public function shouldBeMauticContactPushedAsContact(): bool
    {
        return $this->getSyncSetting(SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS) === SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS_CONTACT;
    }

    /**
     * Gets a setting from the ConfigSyncFeaturesType form.
     *
     * @param string $settingName
     *
     * @return mixed
     */
    private function getSyncSetting(string $settingName)
    {
        $settings = $this->getSettings()['sync']['integration'] ?? [];

        if (!array_key_exists($settingName, $settings)) {
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
    private function getSettings(): array
    {
        if (null === $this->getIntegrationEntity()) {
            return [];
        }

        return $this->integrationEntity->getFeatureSettings();
    }

    /**
     * @return Integration|null
     */
    private function getIntegrationEntity(): ?Integration
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
}

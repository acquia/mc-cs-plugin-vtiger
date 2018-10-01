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

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VtigerSettingProvider
 * @package MauticPlugin\MauticVtigerCrmBundle\Integration
 */
class VtigerSettingProvider
{
    /** @var IntegrationHelper */
    private $integrationHelper;

    /** @var Integration */
    private $integrationEntity;

    /** @var ContainerInterface */
    private $container;

    /**
     * VtigerSettingProvider constructor.
     *
     * @param IntegrationHelper  $helper
     * @param ContainerInterface $container
     */
    public function __construct(IntegrationHelper $helper, ContainerInterface $container)
    {
        $this->integrationHelper = $helper;
        $this->container = $container;
    }

    /**
     * @return null|Integration
     */
    public function getIntegrationEntity(): ?Integration
    {
        if (is_null($this->integrationEntity)) {
            if (!$this->getIntegrationObject()) {
                return null;
            }
            $this->integrationEntity = $this->getIntegrationObject()
                ->getIntegration();
        }

        return $this->integrationEntity;
    }

    private function getIntegrationObject() {
        if (is_null($this->integrationEntity)) {
            if (!$this->integrationHelper
                ->getIntegrationObject(VtigerCrmIntegration::NAME)) {
                return null;
            }
            $this->integrationObject = $this->integrationHelper
                ->getIntegrationObject(VtigerCrmIntegration::NAME);

        }

        return $this->integrationObject;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if ($this->getIntegrationEntity() === null) {
            return [];
        }

        $credentialsCfg = $this->getIntegrationObject()->getDecryptedApiKeys(
            $this->getIntegrationObject()->getIntegrationSettings()
        );

        return $credentialsCfg;
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
        return $this->integrationHelper->getIntegrationObject(VtigerCrmIntegration::NAME)
            ->getIntegrationSettings()
            ->getFeatureSettings();
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
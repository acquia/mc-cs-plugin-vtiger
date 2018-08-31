<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 31.8.18
 * Time: 12:45
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;


use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VtigerSettingProvider
{
    /** @var IntegrationHelper  */
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
    public function __construct(IntegrationHelper $helper, ContainerInterface $container) {
        $this->integrationHelper = $helper;
        $this->container = $container;
    }

    /**
     * @return bool|Integration
     */
    public function getIntegrationEntity() {
        if (is_null($this->integrationEntity)) {
            $this->integrationEntity = $this->integrationHelper
                ->getIntegrationObject(VtigerCrmIntegration::NAME)
                ->getIntegrationEntity();
        }

        return $this->integrationEntity;
    }

    /**
     * @return array
     * @throws VtigerPluginException
     */
    public function getCredentials() {
        if ($this->getIntegrationEntity() === null) {
            throw new VtigerPluginException('Plugin is not configured');
        }

        $credentialsCfg = $this->integrationHelper->getIntegrationObject(VtigerCrmIntegration::NAME)->getDecryptedApiKeys(
            $this->integrationHelper->getIntegrationObject(VtigerCrmIntegration::NAME)->getIntegrationSettings()
        );

        return $credentialsCfg;
    }

    /**
     * @return array
     */
    public function getFormOwners() {
        $owners = $this->container->get('mautic.vtiger_crm.repository.accounts')->findBy();
        $ownersArray = [];
        foreach ($owners as $owner) {
            $ownersArray[$owner->getId()] = (string) $owner;
        }
        return $ownersArray;
    }

    /**
     * @return array
     */
    public function getSettings() {
        return $this->integrationHelper->getIntegrationObject(VtigerCrmIntegration::NAME)
            ->getIntegrationSettings()
            ->getFeatureSettings();
    }

    public function getSetting($settingName) {
        $settings = $this->getSettings();
        if (!array_key_exists($settingName, $settings)) {
            throw new \InvalidArgumentException(
                sprintf('Setting "%s" does not exists, supported: %s',
                    $settingName, join(', ', array_keys($settings))
                    ));
        }
        return $settings[$settingName];
    }
}
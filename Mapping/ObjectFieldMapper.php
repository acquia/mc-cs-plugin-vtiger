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

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use Mautic\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Mautic\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

/**
 * Class ObjectFieldMapper provides all necessary information  to supply mapping information.
 */
class ObjectFieldMapper
{
    /**
     * Map Mautic objects to Vtiger module objects.
     *
     * @var array
     */
    private $vtiger2mauticObjectMapping = [
        'Contacts' => MauticSyncDataExchange::OBJECT_CONTACT,
        'Leads'    => MauticSyncDataExchange::OBJECT_CONTACT,
        'Accounts' => MauticSyncDataExchange::OBJECT_COMPANY,
    ];

    /**
     * @var VtigerSettingProvider
     */
    private $settingProvider;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var LeadRepository
     */
    private $leadRepository;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @param VtigerSettingProvider $settingProvider
     * @param ContactRepository     $contactRepository
     * @param LeadRepository        $leadRepository
     * @param AccountRepository     $accountRepository
     */
    public function __construct(
        VtigerSettingProvider $settingProvider,
        ContactRepository $contactRepository,
        LeadRepository $leadRepository,
        AccountRepository $accountRepository
    ) {
        $this->settingProvider   = $settingProvider;
        $this->contactRepository = $contactRepository;
        $this->leadRepository    = $leadRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param string $objectName
     *
     * @return array|MappedFieldInfoInterface[]
     *
     * @throws InvalidQueryArgumentException
     * @throws PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getObjectFields(string $objectName): array
    {
        switch ($objectName) {
            case 'Contacts':
                $fields = $this->contactRepository->getMappableFields();
                break;
            case 'Leads':
                $fields = $this->leadRepository->getMappableFields();
                break;
            case 'Accounts':
                $fields = $this->accountRepository->getMappableFields();
                break;
            default:
                throw new InvalidQueryArgumentException('Unknown object '.$objectName);
        }

        return $fields;
    }

    /**
     * @return MappingManualDAO
     * @throws InvalidQueryArgumentException
     * @throws ObjectNotSupportedException
     * @throws PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function getObjectsMappingManual(): MappingManualDAO
    {
        $mappingManual = new MappingManualDAO(VtigerCrmIntegration::NAME);

        foreach ($this->settingProvider->getSyncObjects() as $vtigerObject) {
            $objectMapping = new ObjectMappingDAO(
                $this->getVtiger2MauticObjectNameMapping($vtigerObject),
                $vtigerObject
            );

            try {
                $availableFields = $this->getObjectFields($vtigerObject);
            } catch (PluginNotConfiguredException $exception) {
                continue;
            }

            foreach ($this->settingProvider->getFieldMappings($vtigerObject) as $vtigerField => $fieldMapping) {
                if (!isset($availableFields[$vtigerField])) {
                    continue;
                }

                $objectMapping->addFieldMapping(
                    $fieldMapping['mappedField'],
                    $vtigerField,
                    $fieldMapping['syncDirection'],
                    $availableFields[$vtigerField]->showAsRequired()
                );
            }

            if (in_array($vtigerObject, [ContactDataExchange::OBJECT_NAME, LeadDataExchange::OBJECT_NAME], true)) {
                $objectMapping->addFieldMapping('mautic_internal_dnc_email', 'emailoptout', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
            }

            $mappingManual->addObjectMapping($objectMapping);
        }

        return $mappingManual;
    }

    /**
     * @param $objectName
     *
     * @return string
     *
     * @throws ObjectNotSupportedException
     */
    public function getMautic2VtigerObjectNameMapping($objectName): string
    {
        if (false === ($key = array_search($objectName, $this->vtiger2mauticObjectMapping))) {
            throw new ObjectNotSupportedException('Mautic', $objectName);
        }

        return $key;
    }

    /**
     * @param $vtigerObjectName
     *
     * @return mixed
     *
     * @throws ObjectNotSupportedException
     */
    public function getVtiger2MauticObjectNameMapping($vtigerObjectName)
    {
        if (!isset($this->vtiger2mauticObjectMapping[$vtigerObjectName])) {
            throw new ObjectNotSupportedException(VtigerCrmIntegration::NAME, $vtigerObjectName);
        }

        return $this->vtiger2mauticObjectMapping[$vtigerObjectName];
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->vtiger2mauticObjectMapping;
    }
}

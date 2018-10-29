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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Enum\CacheEnum;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

/**
 * Class LeadRepository.
 */
class LeadRepository extends BaseRepository
{
    private $excludedFields = [
        'leadsource', 'contact_id', 'donotcall', 'emailoptout', 'assigned_user_id', 'modifiedby', 'imagename', 'isconvertedfromlead',
    ];


    /**
     * @param Lead $module
     *
     * @return Lead
     */
    public function create(Lead $module): Lead
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Lead
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Lead
    {
        return $this->findOneBy(['id' =>$id]);
    }

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return CacheEnum::LEAD;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappableFields(): array
    {
        $mappable = $this->getEditableFields();

        /**
         * @var int
         * @var ModuleFieldInfo $field
         */
        foreach ($mappable as $key=>$field) {
            if (in_array($field->getName(), $this->excludedFields)) {
                unset($mappable[$key]);
            }
        }

        return $mappable;
    }

    /**
     * @param array $objectData
     *
     * @return Lead
     */
    protected function getModel(array $objectData): Lead
    {
        return $this->modelFactory->createLead($objectData);
    }
}

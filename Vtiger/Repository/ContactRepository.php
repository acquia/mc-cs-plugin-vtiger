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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

/**
 * Class ContactRepository.
 */
class ContactRepository extends BaseRepository
{
    private $excludedFields = [
        'leadsource', 'contact_id', 'donotcall', 'emailoptout', 'assigned_user_id', 'modifiedby', 'imagename', 'isconvertedfromlead',
    ];

    /**
     * @param Contact $module
     *
     * @return Contact
     */
    public function create(Contact $module): Contact
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Contact
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Contact
    {
        return $this->findOneBy(['id' => $id]);
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
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return CacheEnum::CONTACT;
    }

    /**
     * @param array $objectData
     *
     * @return Contact
     */
    protected function getModel(array $objectData): Contact
    {
        return $this->modelFactory->createContact($objectData);
    }
}

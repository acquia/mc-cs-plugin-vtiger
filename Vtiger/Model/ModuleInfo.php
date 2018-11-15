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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction\FieldDirectionInterface;

/**
 * +"label": "Contacts"
 * +"name": "Contacts"
 * +"createable": true
 * +"updateable": true
 * +"deleteable": true
 * +"retrieveable": true
 * +"fields": array:62 [▶]
 * +"idPrefix": "12"
 * +"isEntity": true
 * +"allowDuplicates": false
 * +"labelFields": "firstname,lastname"
 */
class ModuleInfo
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $createable;

    /**
     * @var bool
     */
    private $updateable;

    /**
     * @var bool
     */
    private $deleteable;

    /**
     * @var bool
     */
    private $retrieveable;

    /**
     * @var array|ModuleFieldInfo[]
     */
    private $fields;

    /**
     * @var string
     */
    private $idPrefix;

    /**
     * @var bool
     */
    private $allowDuplicates;

    /**
     * @var string
     */
    private $labelFields;

    /**
     * @param \stdClass               $data
     * @param FieldDirectionInterface $fieldDirection
     */
    public function __construct(\stdClass $data, FieldDirectionInterface $fieldDirection)
    {
        $this->label        = $data->label;
        $this->name         = $data->name;
        $this->createable   = $data->createable;
        $this->updateable   = $data->updateable;
        $this->deleteable   = $data->deleteable;
        $this->retrieveable = $data->retrieveable;
        foreach ($data->fields as $key => $fieldInfo) {
            $this->fields[$fieldInfo->name] = new ModuleFieldInfo($fieldInfo, $fieldDirection);
        }
        $this->idPrefix        = $data->idPrefix;
        $this->allowDuplicates = $data->allowDuplicates ?? true;
        $this->labelFields     = $data->labelFields;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isCreateable(): bool
    {
        return $this->createable;
    }

    /**
     * @return bool
     */
    public function isUpdateable(): bool
    {
        return $this->updateable;
    }

    /**
     * @return bool
     */
    public function isDeleteable(): bool
    {
        return $this->deleteable;
    }

    /**
     * @return bool
     */
    public function isRetrieveable(): bool
    {
        return $this->retrieveable;
    }

    /**
     * @return array|ModuleFieldInfo[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getIdPrefix(): string
    {
        return $this->idPrefix;
    }

    /**
     * @return bool
     */
    public function isAllowDuplicates(): bool
    {
        return $this->allowDuplicates;
    }

    /**
     * @return string
     */
    public function getLabelFields(): string
    {
        return $this->labelFields;
    }

    /**
     * @param string $fieldName
     *
     * @return ModuleFieldInfo
     * @throws InvalidObjectException
     */
    public function getField(string $fieldName): ModuleFieldInfo {
        if (!isset($this->fields[$fieldName])) {
            throw new InvalidObjectException('Unknown field info requested: ' . $fieldName);
        }
        return $this->fields[$fieldName];
    }
}

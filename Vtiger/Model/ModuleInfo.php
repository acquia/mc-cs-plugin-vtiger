<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

/**
 * Class ModuleInfo
 *
 * @see
 *
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
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model
 */
class ModuleInfo
{
    /** @var string */
    private $label;
    /** @var string */
    private $name;
    /** @var bool */
    private $createable;
    /** @var bool */
    private $updateable;
    /** @var bool */
    private $deleteable;
    /** @var bool */
    private $retrieveable;
    /** @var array */
    private $fields;
    /** @var string */
    private $idPrefix;
    /** @var bool */
    private $allowDuplicates;
    /** @var string */
    private $labelFields;

    /**
     * ModuleInfo constructor.
     *
     * @param \stdClass $data
     */
    public function __construct(\stdClass $data)
    {
        $this->label = $data->label;
        $this->name = $data->name;
        $this->createable = $data->createable;
        $this->updateable = $data->updateable;
        $this->deleteable = $data->deleteable;
        $this->retrieveable = $data->retrieveable;
        foreach ($data->fields as $key=>$fieldInfo) {
            $this->fields[$key] = new ModuleFieldInfo($fieldInfo);
        }
        $this->idPrefix = $data->idPrefix;
        $this->allowDuplicates = $data->allowDuplicates;
        $this->labelFields = $data->labelFields;
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
     * @return array
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


}
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

abstract class BaseModel
{
    /** @var array */
    protected $data = [];           //  This contains the real data of the object for manipulation

    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->hydrate($data);
        }
    }

    public function hydrate(array $attributes)
    {
        foreach ($attributes as $attribute=>$value) {
            $this->data[$attribute] = $value;
        }
    }

    public function dehydrate($fields = [])
    {
        if (0 === count($fields)) {
            return $this->data;
        }

        $response = [];

        foreach ($fields as $fieldName) {
            $response[$fieldName] = isset($this->data[$fieldName]) ? $this->data[$fieldName] : null;
        }

        return $response;
    }

    public function __get($name)
    {
        if (!isset($this->data[$name]) && !in_array($name, [])) {
            var_dump($this->data);
            throw new \InvalidArgumentException('Unknown property '.$name);
        }

        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedTime()
    {
        return $this->createdtime ? new \DateTime($this->createdtime) : null;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return isset($this->data['id']) ? $this->data['id'] : null;
    }

    /**
     * @param $id string|null
     *
     * @return BaseModel
     */
    public function setId($id): self
    {
        $this->data['id'] = $id;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifiedTime(): ?\DateTime
    {
        return $this->modifiedtime ? new \DateTime($this->modifiedtime) : null;
    }

    public function set($identified, $value)
    {
        $this->data[$identified] = $value;
    }
}

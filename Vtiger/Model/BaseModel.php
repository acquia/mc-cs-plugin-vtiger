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

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * @param array $attributes
     */
    public function hydrate(array $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            $this->data[$attribute] = $value;
        }
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function dehydrate($fields = []): array
    {
        if (0 === count($fields)) {
            return $this->data;
        }

        $response = [];

        foreach ($fields as $fieldName) {
            $response[$fieldName] = $this->data[$fieldName] ?? null;
        }

        return $response;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifiedTime(): ?\DateTime
    {
        return $this->data['modifiedtime'] ? new \DateTime($this->data['modifiedtime']) : null;
    }


    /**
     * @param $identified
     * @param $value
     *
     * @return $this
     */
    public function set($identified, $value): self
    {
        $this->data[$identified] = $value;

        return $this;
    }
}

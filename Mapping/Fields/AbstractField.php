<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MagentoBundle\Mapping\Fields;

use MauticPlugin\MagentoBundle\Exception\DisabledFieldException;

abstract class AbstractField implements FieldInterface
{
    /**
     * @var string
     */
    private $type;

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
    private $required;

    /**
     * @var bool
     */
    private $customField;

    /**
     * @var array|OptionValue[]
     */
    private $optionValues = [];

    /**
     * @param array $data
     *
     * @throws DisabledFieldException
     */
    public function __construct(array $data)
    {
        $this->label       = (string) ($data['frontend_label'] ?? '');
        if (!$this->label) {
            throw new DisabledFieldException('Field has no label - suppose this is a szstem field like password etc.');
        }

        $this->type        = (string) ($data['frontend_input'] ?? '');
        $this->name        = (string) ($data['attribute_code'] ?? '');
        $this->required    = (bool) ($data['required'] ?? false);
        $this->customField = (bool) ($data['user_defined'] ?? false);

        if (!empty($data['options'])) {
            foreach ($data['options'] as $option) {
                $this->optionValues[] = new OptionValue($option);
            }
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isAddress(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCustomField(): bool
    {
        return $this->customField;
    }

    /**
     * @return array|OptionValue[]
     */
    public function getOptionValues(): array
    {
        return $this->optionValues;
    }
}

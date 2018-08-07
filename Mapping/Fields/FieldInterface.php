<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MagentoBundle\Mapping\Fields;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @return bool
     */
    public function isAddress(): bool;

    /**
     * Custom Field in Magento means that this Field is under a "custom_attributes: Item in response.
     *
     * @return bool
     */
    public function isCustomField(): bool;

    /**
     * @return array|OptionValue[]
     */
    public function getOptionValues(): array;
}

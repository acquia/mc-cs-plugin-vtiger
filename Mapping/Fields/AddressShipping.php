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

final class AddressShipping extends AbstractField
{
    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'Shipping address: '.parent::getLabel();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return parent::getName().'___address___shipping';
    }

    /**
     * @return bool
     */
    public function isAddress(): bool
    {
        return true;
    }
}

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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

interface FieldDirectionInterface
{
    /**
     * @param ModuleFieldInfo $moduleFieldInfo
     *
     * @return bool
     */
    public function isFieldReadable(ModuleFieldInfo $moduleFieldInfo): bool;

    /**
     * @param ModuleFieldInfo $moduleFieldInfo
     *
     * @return bool
     */
    public function isFieldWritable(ModuleFieldInfo $moduleFieldInfo): bool;
}

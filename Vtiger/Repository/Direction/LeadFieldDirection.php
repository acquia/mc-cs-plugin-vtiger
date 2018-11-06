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

class LeadFieldDirection implements FieldDirectionInterface
{
    private $readOnlyFields = [
        'leadsource',
        'contact_id',
        'donotcall',
        'emailoptout',
        'assigned_user_id',
        'modifiedby',
        'imagename',
        'isconvertedfromlead',
    ];

    /**
     * @inheritdoc
     */
    public function isFieldReadable(ModuleFieldInfo $moduleFieldInfo): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isFieldWritable(ModuleFieldInfo $moduleFieldInfo): bool
    {
        return !in_array($moduleFieldInfo->getName(), $this->readOnlyFields, true);
    }
}

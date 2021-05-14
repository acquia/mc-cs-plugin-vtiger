<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     29.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type;

/**
 * Class MultipicklistType
 *
 * @see
 *
 *
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type
 */
class TypeFactory
{
    /**
     * @param $data
     *
     * @return CommonType|DateType|MultipicklistType|PicklistType|ReferenceType
     */
    public static function create($data)
    {
        switch ($data->name) {
            case 'multipicklist':
                return new MultipicklistType($data);
            case 'picklist':
                return new PicklistType($data);
            case 'date':
                return new DateType($data);
            case 'reference':
                return new ReferenceType($data);
            default:
                return new CommonType($data);
        }
    }
}

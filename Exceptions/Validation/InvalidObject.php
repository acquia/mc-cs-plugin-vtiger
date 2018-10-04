<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     20.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Exceptions\Validation;

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

class InvalidObject extends InvalidObjectException
{
    public function __construct(array $violations, ModuleFieldInfo $moduleFieldInfo, $fieldValue)
    {
        foreach ($violations as $violation) {
            $violationsMessages[] = $violation->getMessage;
        }

        $message = sprintf("Validation of %s failed. Field value: '%s'. %s",
            $moduleFieldInfo->getName(),
            $fieldValue,
            join('. ', $violationsMessages)
            );

        parent::__construct($message);
    }
}

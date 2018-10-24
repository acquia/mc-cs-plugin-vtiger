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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator;

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\Validation\InvalidObject;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;

interface ObjectValidatorInterface
{
    /**
     * @param BaseModel $object
     *
     * @throws InvalidObject
     */
    public function validate(BaseModel $object): void;
}

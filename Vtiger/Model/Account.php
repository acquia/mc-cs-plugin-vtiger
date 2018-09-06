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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\NoFieldException;

class Account extends BaseModel
{
    /**
     * @return string
     *
     * @throws NoFieldException
     */
    public function getAccountName(): string
    {
        if (!isset($this->data['accountname'])) {
            throw new NoFieldException('Field accountname is missing');
        }

        return $this->data['accountname'];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAccountName();
    }
}

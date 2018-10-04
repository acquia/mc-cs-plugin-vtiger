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

class User extends BaseModel
{
    public function __toString(): string
    {
        return sprintf('%s, %s (%s)', $this->data['last_name'], $this->data['first_name'], $this->data['user_name']);
    }
}

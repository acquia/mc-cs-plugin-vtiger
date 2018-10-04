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

class Account extends BaseModel
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getAccountName();
    }

    /**
     * @return string|null
     */
    public function getAssignedUserId(): ?string
    {
        return !isset($this->data['assigned_user_id']) ? null : $this->data['assigned_user_id'];
    }

    /**
     * @param null|string $userId
     *
     * @return Contact
     */
    public function setAssignedUserId(?string $userId): self
    {
        $this->data['assigned_user_id'] = $userId;

        return $this;
    }
}

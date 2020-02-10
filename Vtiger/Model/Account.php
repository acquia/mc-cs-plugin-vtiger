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

class Account extends BaseModel
{
    /**
     * @return string|null
     */
    public function getAssignedUserId(): ?string
    {
        return !isset($this->data['assigned_user_id']) ? null : $this->data['assigned_user_id'];
    }

    /** @noinspection PhpDocSignatureInspection */

    /**
     * @param null|string $userId
     *
     * @return Contact
     */
    public function setAssignedUserId(?string $userId): Account
    {
        $this->data['assigned_user_id'] = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAccountName();
    }

    /**
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->data['accountname'] ?? null;
    }
}

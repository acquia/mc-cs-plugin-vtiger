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

/**
 * Class Contact
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model
 */
class Contact extends BaseModel
{
    /**
     * @return string|null
     */
    public function getAssignedUserId(): ?string {
        return !isset($this->data['assigned_user_id']) ? null : $this->data['assigned_user_id'];
    }

    /**
     * @param null|string $userId
     *
     * @return Contact
     */
    public function setAssignedUserId(?string $userId): Contact {
        $this->data['assigned_user_id'] = $userId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConvertedFromLead(): bool {
        return (bool) $this->data['isconvertedfromlead'];
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->data['email'];
    }

    /**
     * @return bool
     */
    public function getEmailOptout(): bool {
        return (bool) $this->data['emailoptout'];
    }

    /**
     * @param bool $value
     *
     * @return Contact
     */
    public function setEmailOptout(bool $value): self {
        $this->data['emailoptout'] = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDoNotCall(): bool {
        return (bool) $this->data['donotcall'];
    }

    /**
     * @param bool $value
     *
     * @return Contact
     */
    public function setDoNotCall(bool $value): self {
        $this->data['donotcall'] = $value;
        return $this;
    }
}

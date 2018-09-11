<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

class Lead extends BaseModel
{
    /**
     * @return string|null
     */
    public function getAssignedUserId() {
        return !isset($this->data['assigned_user_id']) ? null : $this->data['assigned_user_id'];
    }

    /**
     * @param null|string $userId
     *
     * @return Lead
     */
    public function setAssignedUserId(?string $userId): Lead {
        $this->data['assigned_user_id'] = $userId;
        return $this;
    }
}
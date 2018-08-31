<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

class Contact extends BaseModel
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
     * @return Contact
     */
    public function setAssignedUserId(?string $userId): Contact {
        $this->data['assigned_user_id'] = $userId;
        return $this;
    }
}
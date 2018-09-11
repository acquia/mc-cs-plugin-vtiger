<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

class User extends BaseModel
{
    public function __toString(): string
    {
        return sprintf('%s, %s (%s)', $this->data['last_name'], $this->data['first_name'], $this->data['user_name']);
    }

}
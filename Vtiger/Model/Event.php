<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

class Event extends BaseModel
{

    /**
     * @return null|string
     */
    public function getSubject(): ?string {
        return isset($this->data['subject']) ? $this->data['subject'] : null;
    }

    /**
     * @return null|string
     */
    public function getTaskPriority(): ?string {
        return isset($this->data['taskpriority']) ? $this->data['taskpriority'] : null;
    }

    /**
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public function getDateTimeStart(): \DateTimeImmutable {
        return new \DateTimeImmutable($this->data['date_start'] . " " . $this->data['time_start']);
    }
}
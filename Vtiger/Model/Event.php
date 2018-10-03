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
    public function getTaskPriority(): ?int {
        return isset($this->data['taskpriority']) ? (int) $this->data['taskpriority'] : null;
    }

    /**
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public function getDateTimeStart(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['date_start'] . " " . $this->data['time_start']);
    }

    /**
     * @return \DateTimeImmutable
     * @throws \Exception
     */
    public function getDateTimeEnd(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['due_date'] . " " . $this->data['time_end']);
    }

    public function setDateTimeStart(\DateTimeInterface $dateTime): Event {
        $day = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('date_start', $day);
        $this->set('time_start', $time);
        return $this;
    }

    public function setDateTimeEnd(\DateTimeInterface $dateTime): Event {
        $day = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('due_date', $day);
        $this->set('time_end', $time);
        return $this;
    }

    public function setSubject(?string $subject): Event {
        $this->set('subject', $subject);
        return $this;
    }

    public function setAssignedUserId(?string $userId): Event {
        $this->set('assigned_user_id', $userId);
        return $this;
    }

    public function setModifiedBy(?string $userId): Event {
        $this->set('modifiedby', $userId);
        return $this;
    }

    public function setTaskPriority(?string $priority): Event {
        $this->set('taskpriority', $priority);
        return $this;
    }

    public function getContactId(): ?string {
        return $this->data['contact_id'] ?? null;
    }
}
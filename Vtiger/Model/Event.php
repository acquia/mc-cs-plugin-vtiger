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

class Event extends BaseModel
{
    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return isset($this->data['subject']) ? $this->data['subject'] : null;
    }

    /**
     * @return null|int
     */
    public function getTaskPriority(): ?int
    {
        return isset($this->data['taskpriority']) ? (int) $this->data['taskpriority'] : null;
    }

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getDateTimeStart(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['date_start'].' '.$this->data['time_start']);
    }

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getDateTimeEnd(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['due_date'].' '.$this->data['time_end']);
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return Event
     */
    public function setDateTimeStart(\DateTimeInterface $dateTime): Event
    {
        $day  = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('date_start', $day);
        $this->set('time_start', $time);

        return $this;
    }

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return Event
     */
    public function setDateTimeEnd(\DateTimeInterface $dateTime): Event
    {
        $day  = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('due_date', $day);
        $this->set('time_end', $time);

        return $this;
    }

    /**
     * @param null|string $subject
     *
     * @return Event
     */
    public function setSubject(?string $subject): Event
    {
        $this->set('subject', $subject);

        return $this;
    }

    /**
     * @param null|string $userId
     *
     * @return Event
     */
    public function setAssignedUserId(?string $userId): Event
    {
        $this->set('assigned_user_id', $userId);

        return $this;
    }

    /**
     * @param null|string $userId
     *
     * @return Event
     */
    public function setModifiedBy(?string $userId): Event
    {
        $this->set('modifiedby', $userId);

        return $this;
    }

    /**
     * @param int|null $priority
     *
     * @return Event
     */
    public function setTaskPriority(?int $priority): Event
    {
        $this->set('taskpriority', $priority);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getContactId(): ?string
    {
        return $this->data['contact_id'] ?? null;
    }

    /**
     * @param null|string $userId
     *
     * @return Event
     */
    public function setContactId(?string $userId): Event
    {
        $this->set('contact_id', $userId);

        return $this;
    }
}
